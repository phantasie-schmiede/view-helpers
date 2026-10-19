<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Variable;

use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\Variable\ConvertStringViewHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class ConvertStringViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Variable
 */
class ConvertStringViewHelperTest extends UnitTestCase
{
    #[Test]
    public function anIntegerStringIsConvertedToAnInteger(): void
    {
        self::assertSame(42, $this->render('42'));
    }

    #[Test]
    public function aFloatStringIsConvertedToAFloat(): void
    {
        self::assertSame(4.5, $this->render('4.5'));
    }

    #[Test]
    public function aNonNumericStringIsReturnedAsIs(): void
    {
        self::assertSame('abc', $this->render('abc'));
    }

    #[Test]
    public function anEmptyStringIsReturnedAsIs(): void
    {
        self::assertSame('', $this->render(''));
    }

    #[Test]
    public function aMissingStringYieldsNull(): void
    {
        self::assertNull($this->render(null));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function render(?string $string): mixed
    {
        $viewHelper = new ConvertStringViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => '');
        $viewHelper->initializeArguments();
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments([
            'string' => $string,
        ] + $defaults);

        return $viewHelper->render();
    }
}
