<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Functional\ViewHelpers\Extension;

use PHPUnit\Framework\Attributes\Test;
use PSBits\Foundation\Exceptions\ImplementationException;
use PSBits\Foundation\Service\ExtensionInformationService;
use PSBits\ViewHelpers\ViewHelpers\Extension\PublicResourceWebPathViewHelper;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class PublicResourceWebPathViewHelperTest
 *
 * Uses the real ExtensionInformationService of the booted test instance, so
 * it runs as a functional test: the service registers the ExtensionInformation
 * classes of the loaded extensions and resolves their paths through the
 * package manager.
 *
 * @package PSBits\ViewHelpers\Tests\Functional\ViewHelpers\Extension
 */
class PublicResourceWebPathViewHelperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/psbits/foundation',
        'typo3conf/ext/psbits/view-helpers',
    ];

    #[Test]
    public function thePublicResourcePathOfTheGivenExtensionIsReturned(): void
    {
        $path = rtrim($this->render([
            'extensionKey' => 'view_helpers',
        ]), '/');

        self::assertStringContainsString('view_helpers/Resources/Public', $path);
        self::assertStringEndsWith('/Resources/Public', $path);
    }

    #[Test]
    public function aSubdirectoryIsAppendedToThePath(): void
    {
        $path = rtrim($this->render([
            'extensionKey' => 'view_helpers',
            'subdirectory' => 'Icons',
        ]), '/');

        self::assertStringEndsWith('/Resources/Public/Icons', $path);
    }

    #[Test]
    public function theLeadingSlashCanBeRemoved(): void
    {
        $withSlash = $this->render([
            'extensionKey' => 'view_helpers',
        ]);
        $withoutSlash = $this->render([
            'extensionKey'        => 'view_helpers',
            'withoutLeadingSlash' => true,
        ]);

        self::assertSame(ltrim($withSlash, '/'), $withoutSlash);
    }

    #[Test]
    public function anUnregisteredExtensionRaisesAnException(): void
    {
        $this->expectException(ImplementationException::class);
        $this->expectExceptionCode(1683560687);

        $this->render([
            'extensionKey' => 'view_helpers_does_not_exist',
        ]);
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @throws ImplementationException
     * @throws InvalidFileException
     */
    private function render(array $arguments): string
    {
        $viewHelper = new PublicResourceWebPathViewHelper(
            GeneralUtility::makeInstance(ExtensionInformationService::class)
        );
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => '');
        $viewHelper->initializeArguments();
        $viewHelper->setArguments($arguments + ['subdirectory' => '', 'withoutLeadingSlash' => false]);

        return $viewHelper->render();
    }
}
