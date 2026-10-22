<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Variable;

use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\Variable\MathViewHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class MathViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Variable
 */
class MathViewHelperTest extends UnitTestCase
{
    /**
     * @return array<string, array{mixed, mixed, string, int|float}>
     */
    public static function operatorDataProvider(): array
    {
        return [
            'addition'        => [2, 3, '+', 5],
            'subtraction'     => [10, 4, '-', 6],
            'multiplication'  => [3, 4, '*', 12],
            'division'        => [10, 4, '/', 2.5],
            'exponentiation'  => [2, 3, '**', 8],
            'float operands'  => [1.5, 2.5, '+', 4.0],
            'string operands' => ['2.5', '2', '*', 5.0],
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('operatorDataProvider')]
    public function theOperatorIsAppliedToTheOperands(mixed $a, mixed $b, string $operator, int|float $expected): void
    {
        self::assertSame(
            $expected,
            $this->render($a, $b, $operator)
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[Test]
    public function aNonNumericOperandRaisesAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1558773027);

        $this->render('abc', 2, '+');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    #[Test]
    public function aDisallowedOperatorRaisesAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1558773161);

        $this->render(2, 3, '%');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function render(mixed $a, mixed $b, string $operator): float|int
    {
        $viewHelper = new MathViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => '');
        $viewHelper->initializeArguments();
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments([
            'a'        => $a,
            'b'        => $b,
            'operator' => $operator,
        ] + $defaults);

        return $viewHelper->render();
    }
}
