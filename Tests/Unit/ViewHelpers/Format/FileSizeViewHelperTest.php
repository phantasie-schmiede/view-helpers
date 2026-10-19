<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Format;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\Format\FileSizeViewHelper;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

use function file_put_contents;
use function str_repeat;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * Class FileSizeViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Format
 */
class FileSizeViewHelperTest extends UnitTestCase
{
    private string $tempFile;

    #[Test]
    public function bytesBelowOneKilobyteStayInBytes(): void
    {
        self::assertSame('500&nbsp;B', $this->render(['size' => 500]));
    }

    #[Test]
    public function theUnitIsChosenAutomatically(): void
    {
        self::assertSame('1.5&nbsp;KB', $this->render(['size' => 1536]));
        self::assertSame('1&nbsp;MB', $this->render(['size' => 1048576]));
        self::assertSame('1&nbsp;GB', $this->render(['size' => 1073741824]));
    }

    #[Test]
    public function aForcedUnitDividesTheSize(): void
    {
        self::assertSame('2&nbsp;KB', $this->render([
            'size' => 2048,
            'unit' => 1,
        ]));
    }

    #[Test]
    public function theNumberOfDecimalsIsConfigurable(): void
    {
        self::assertSame('1.5&nbsp;KB', $this->render([
            'size'     => 1536,
            'decimals' => 1,
        ]));
    }

    #[Test]
    public function aFileNameIsResolvedToItsSize(): void
    {
        self::assertSame('512&nbsp;B', $this->render([
            'size' => $this->tempFile,
        ]));
    }

    #[Test]
    public function theChildContentIsUsedWhenNoSizeIsGiven(): void
    {
        self::assertSame('512&nbsp;B', $this->render(['size' => null], $this->tempFile));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFile = sys_get_temp_dir() . '/' . uniqid('file-size-test-') . '.bin';
        file_put_contents($this->tempFile, str_repeat('x', 512));
    }

    protected function tearDown(): void
    {
        unlink($this->tempFile);
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @throws AspectNotFoundException
     */
    private function render(array $arguments, string $children = ''): string
    {
        $viewHelper = new FileSizeViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => $children);
        $viewHelper->initializeArguments();
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);

        return $viewHelper->render();
    }
}
