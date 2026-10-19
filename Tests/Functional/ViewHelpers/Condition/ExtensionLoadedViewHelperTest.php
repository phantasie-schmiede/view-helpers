<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Functional\ViewHelpers\Condition;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\Condition\ExtensionLoadedViewHelper;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class ExtensionLoadedViewHelperTest
 *
 * Checks the extension state of the booted test instance, so it runs as a
 * functional test: the unit level has no PackageManager.
 *
 * @package PSBits\ViewHelpers\Tests\Functional\ViewHelpers\Condition
 */
class ExtensionLoadedViewHelperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psbits/foundation',
        'typo3conf/ext/psbits/view-helpers',
    ];

    #[Test]
    public function aLoadedExtensionRendersTheChildren(): void
    {
        self::assertSame('rendered', $this->render('Core'));
    }

    #[Test]
    public function theOwnExtensionIsDetectedAsLoaded(): void
    {
        self::assertSame('rendered', $this->render('ViewHelpers'));
    }

    #[Test]
    public function anUnloadedExtensionRendersNothing(): void
    {
        self::assertSame('', $this->render('ViewHelpersDoesNotExist'));
    }

    private function render(string $extensionKey): string
    {
        $renderingContext = GeneralUtility::makeInstance(RenderingContextFactory::class)->create();
        $request          = new ServerRequest(
            'http://example.com/',
            'GET',
            null,
            [],
            [
                'HTTP_HOST'   => 'example.com',
                'REQUEST_URI' => '/',
            ]
        );
        $request = $request
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        /*
         * The request is exposed as a rendering context attribute; older Fluid versions
         * set it via setAttribute(), newer ones via set().
         */
        if (method_exists($renderingContext, 'set')) {
            $renderingContext->set(ServerRequestInterface::class, $request);
        } else {
            $renderingContext->setAttribute(ServerRequestInterface::class, $request);
        }

        $viewHelper = new ExtensionLoadedViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setRenderChildrenClosure(static fn() => 'rendered');
        $viewHelper->initializeArguments();
        $viewHelper->setArguments([
            'extensionKey' => $extensionKey,
        ]);

        return $viewHelper->render();
    }
}
