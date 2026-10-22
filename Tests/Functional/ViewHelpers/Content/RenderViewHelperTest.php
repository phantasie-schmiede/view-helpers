<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Functional\ViewHelpers\Content;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\Tests\Functional\Fixtures\Domain\TestContent;
use PSBits\ViewHelpers\Tests\Functional\Fixtures\Domain\TestDomainObject;
use PSBits\ViewHelpers\ViewHelpers\Content\RenderViewHelper;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionProperty;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\AST\AstBuilder;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptStringFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\CMS\Frontend\Page\PageInformation;
use TYPO3\CMS\Frontend\Page\PageParts;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

use function file_get_contents;
use function file_put_contents;

/**
 * Class RenderViewHelperTest
 *
 * Renders tt_content records through the real CObject stack: the TypoScript
 * setup of the extension itself, the RECORDS CObject, the database and the
 * content rendering all run for real.
 *
 * @package PSBits\ViewHelpers\Tests\Functional\ViewHelpers\Content
 */
class RenderViewHelperTest extends FunctionalTestCase
{
    public const int ROOT_PAGE_ID = 1;

    /**
     * Test-level TypoScript. The config section is required by the
     * ContentObjectRenderer; the extension's own TypoScript defines the RECORDS
     * object the ViewHelper addresses, including the record rendering.
     */
    private const string TEST_LEVEL_TYPOSCRIPT = <<<'TYPOSCRIPT'
config {
    contentObjectExceptionHandler =
}
TYPOSCRIPT;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psbits/foundation',
        'typo3conf/ext/psbits/view-helpers',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->writeSiteConfiguration();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tt_content.csv');
    }

    #[Test]
    public function aSingleIntegerUidRendersTheRecord(): void
    {
        $rendered = $this->render(1);

        self::assertStringContainsString('First body', $rendered);
        self::assertStringNotContainsString('Second body', $rendered);
    }

    #[Test]
    public function aCommaSeparatedUidListRendersTheRecords(): void
    {
        $rendered = $this->render('1,2');

        self::assertStringContainsString('First body', $rendered);
        self::assertStringContainsString('Second body', $rendered);
    }

    #[Test]
    public function anArrayOfUidsRendersTheRecords(): void
    {
        $rendered = $this->render([1, 2]);

        self::assertStringContainsString('First body', $rendered);
        self::assertStringContainsString('Second body', $rendered);
    }

    #[Test]
    public function anAbstractEntityRendersItsRecord(): void
    {
        $rendered = $this->render($this->entityWithUid(1));

        self::assertStringContainsString('First body', $rendered);
        self::assertStringNotContainsString('Second body', $rendered);
    }

    #[Test]
    public function anObjectStorageRendersAllOfItsRecords(): void
    {
        $storage = new ObjectStorage();
        $storage->attach($this->entityWithUid(1));
        $storage->attach($this->entityWithUid(2));

        $rendered = $this->render($storage);

        self::assertStringContainsString('First body', $rendered);
        self::assertStringContainsString('Second body', $rendered);
    }

    #[Test]
    public function aQueryResultRendersAllOfItsRecords(): void
    {
        $query = GeneralUtility::makeInstance(Query::class);
        $query->setType(TestContent::class);
        $queryResult = GeneralUtility::makeInstance(QueryResult::class);
        $queryResult->setQuery($query);

        /*
         * QueryResult::initialize() executes the query as soon as the result list is not set
         * yet, which needs a repository and a model to table mapping. Inject the results
         * directly instead; the ViewHelper only iterates over them.
         */
        /*
         * ReflectionProperty is always accessible since PHP 8.1, so the legacy
         * setAccessible() call (deprecated as of PHP 8.5) is not needed here.
         */
        $reflection = new ReflectionProperty(QueryResult::class, 'queryResult');
        $reflection->setValue($queryResult, [
            $this->entityWithUid(1),
            $this->entityWithUid(2),
        ]);

        $rendered = $this->render($queryResult);

        self::assertStringContainsString('First body', $rendered);
        self::assertStringContainsString('Second body', $rendered);
    }

    #[Test]
    public function anArrayMayMixEntitiesAndUids(): void
    {
        $rendered = $this->render([
            $this->entityWithUid(2),
            1,
        ]);

        self::assertStringContainsString('First body', $rendered);
        self::assertStringContainsString('Second body', $rendered);
    }

    #[Test]
    public function aNonEntityInAnObjectStorageRaisesAnException(): void
    {
        $storage = new ObjectStorage();
        $storage->attach(new TestDomainObject());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1651223157);

        $this->render($storage);
    }

    #[Test]
    public function aForeignArrayEntryRaisesAnException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1651223524);

        $this->render([1, 'not-a-uid']);
    }

    private function render(mixed $contentElements): string
    {
        $renderingContext = GeneralUtility::makeInstance(RenderingContextFactory::class)->create();
        $request          = $this->buildRequest();

        /*
         * The request is exposed as a rendering context attribute; older Fluid versions
         * set it via setAttribute(), newer ones via set().
         */
        if (method_exists($renderingContext, 'set')) {
            $renderingContext->set(ServerRequestInterface::class, $request);
        } else {
            $renderingContext->setAttribute(ServerRequestInterface::class, $request);
        }

        $viewHelper = new RenderViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setRenderChildrenClosure(static fn() => null);
        $viewHelper->initializeArguments();
        $viewHelper->setArguments([
            'contentElements' => $contentElements,
        ]);

        return $viewHelper->render();
    }

    private function buildRequest(): ServerRequest
    {
        $request = new ServerRequest(
            'http://example.com/',
            'GET',
            null,
            [],
            [
                'HTTP_HOST'   => 'example.com',
                'REQUEST_URI' => '/',
            ]
        );
        $pageInformation = new PageInformation();
        $pageInformation->setId(self::ROOT_PAGE_ID);

        $request = $request
            ->withQueryParams(['id' => self::ROOT_PAGE_ID])
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.page.information', $pageInformation)
            ->withAttribute('frontend.typoscript', $this->buildFrontendTypoScript());

        /*
         * v14 tracks the page's last changed timestamp in a PageParts request attribute;
         * ContentObjectRenderer::lastChanged() requires it there.
         */
        if (class_exists(PageParts::class)) {
            $request = $request->withAttribute('frontend.page.parts', new PageParts());
        }

        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByRootPageId(self::ROOT_PAGE_ID);

        return $request
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getDefaultLanguage());
    }

    private function writeSiteConfiguration(): void
    {
        $path = Environment::getConfigPath() . '/sites/view-helpers-test';
        GeneralUtility::rmdir($path, true);
        GeneralUtility::mkdir_deep($path);
        file_put_contents($path . '/config.yaml', "rootPageId: 1\nbase: '/'\n");
    }

    private function buildFrontendTypoScript(): FrontendTypoScript
    {
        $typoScriptStringFactory = GeneralUtility::makeInstance(TypoScriptStringFactory::class);
        $extensionTypoScript     = (string)file_get_contents(
            __DIR__ . '/../../../../Configuration/TypoScript/setup.typoscript'
        );
        $rootNode = $typoScriptStringFactory->parseFromString(
            $extensionTypoScript . "\n\n" . self::TEST_LEVEL_TYPOSCRIPT,
            new AstBuilder(new NoopEventDispatcher())
        );
        $setupArray = $rootNode->toArray();

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray($setupArray);
        $frontendTypoScript->setConfigArray($setupArray['config.'] ?? []);

        return $frontendTypoScript;
    }

    private function entityWithUid(int $uid): TestContent
    {
        $entity = new TestContent();
        $entity->_setProperty('uid', $uid);

        return $entity;
    }
}
