<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Condition;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\Condition\InArrayViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class InArrayViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Condition
 */
class InArrayViewHelperTest extends UnitTestCase
{
    #[Test]
    public function theComparisonIsStrictByDefault(): void
    {
        self::assertFalse(
            $this->render([
                'haystack' => [1, 2, 3],
                'needle'   => '1',
            ])
        );
    }

    #[Test]
    public function aStrictMatchRendersTheChildren(): void
    {
        self::assertSame(
            'found',
            $this->render([
                'haystack' => [1, 2, 3],
                'needle'   => 2,
            ], ' found ')
        );
    }

    #[Test]
    public function aNonStrictMatchLoosensTheComparison(): void
    {
        self::assertSame(
            'found',
            $this->render(
                [
                    'haystack' => [1, 2, 3],
                    'needle'   => '2',
                    'strict'   => false,
                ],
                'found'
            )
        );
    }

    #[Test]
    public function trueIsReturnedWhenTheNeedleMatchesWithoutChildContent(): void
    {
        self::assertTrue(
            $this->render([
                'haystack' => ['a', 'b'],
                'needle'   => 'a',
            ], null)
        );
    }

    #[Test]
    public function falseIsReturnedWhenTheNeedleIsNotInTheHaystack(): void
    {
        self::assertFalse(
            $this->render([
                'haystack' => ['a', 'b'],
                'needle'   => 'c',
            ], 'found')
        );
    }

    #[Test]
    public function theArgumentsAreRegistered(): void
    {
        $definitions = $this->createViewHelper('x')->prepareArguments();

        self::assertSame('array', $definitions['haystack']->getType());
        self::assertTrue($definitions['haystack']->isRequired());
        self::assertTrue($definitions['needle']->isRequired());
        self::assertSame(true, $definitions['strict']->getDefaultValue());
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function render(array $arguments, ?string $children = ''): bool|string
    {
        $viewHelper = $this->createViewHelper($children);
        $defaults   = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);

        return $viewHelper->render();
    }

    private function createViewHelper(?string $children): InArrayViewHelper
    {
        $viewHelper = new InArrayViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => $children);
        $viewHelper->initializeArguments();

        return $viewHelper;
    }
}
