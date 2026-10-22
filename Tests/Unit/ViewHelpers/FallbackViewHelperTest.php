<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\FallbackViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class FallbackViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers
 */
class FallbackViewHelperTest extends UnitTestCase
{
    #[Test]
    public function valuesIsRegisteredAsARequiredArrayArgument(): void
    {
        $viewHelper  = $this->createInitializedViewHelper();
        $definitions = $viewHelper->prepareArguments();

        self::assertArrayHasKey('values', $definitions);
        self::assertSame('array', $definitions['values']->getType());
        self::assertTrue($definitions['values']->isRequired());
    }

    #[Test]
    public function theFirstNonEmptyValueIsReturned(): void
    {
        self::assertSame(
            'x',
            $this->renderWithValues([null, '', 'x', 'y'])
        );
    }

    #[Test]
    public function zeroAndFalseCountAsEmpty(): void
    {
        self::assertSame(
            'y',
            $this->renderWithValues([0, false, null, 'y'])
        );
    }

    #[Test]
    public function aNumericValueIsReturned(): void
    {
        self::assertSame(
            5,
            $this->renderWithValues([null, 5])
        );
    }

    #[Test]
    public function nullIsReturnedWhenEveryValueIsEmpty(): void
    {
        self::assertNull(
            $this->renderWithValues([null, '', 0, false])
        );
    }

    #[Test]
    public function nullIsReturnedForAnEmptyValueList(): void
    {
        self::assertNull($this->renderWithValues([]));
    }

    /**
     * @param array<int, mixed> $values
     */
    private function renderWithValues(array $values): mixed
    {
        $viewHelper = $this->createInitializedViewHelper();
        $this->setArguments($viewHelper, [
            'values' => $values,
        ]);

        return $viewHelper->render();
    }

    /**
     * prepareArguments() returns ArgumentDefinition objects, not values, so the defaults have
     * to be unwrapped the way ViewHelperNode::evaluate() does before render() is called.
     *
     * @param array<string, mixed> $arguments
     */
    private function setArguments(FallbackViewHelper $viewHelper, array $arguments): void
    {
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);
    }

    private function createInitializedViewHelper(): FallbackViewHelper
    {
        $viewHelper = new FallbackViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => '');
        $viewHelper->initializeArguments();

        return $viewHelper;
    }
}
