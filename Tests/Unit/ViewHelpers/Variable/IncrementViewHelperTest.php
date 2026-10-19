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
use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\Variable\IncrementViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

/**
 * Class IncrementViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Variable
 */
class IncrementViewHelperTest extends UnitTestCase
{
    private StandardVariableProvider $provider;

    #[Test]
    public function theVariableIsIncrementedByOneByDefault(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->provider->add('counter', 5);
        $this->setArguments($viewHelper, [
            'variable' => 'counter',
        ]);

        $viewHelper->render();

        self::assertSame(6, $this->provider->get('counter'));
    }

    #[Test]
    public function theStepArgumentControlsTheIncrement(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->provider->add('counter', 5);
        $this->setArguments($viewHelper, [
            'step'     => 3,
            'variable' => 'counter',
        ]);

        $viewHelper->render();

        self::assertSame(8, $this->provider->get('counter'));
    }

    #[Test]
    public function floatingPointValuesAreIncremented(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->provider->add('counter', 1.5);
        $this->setArguments($viewHelper, [
            'step'     => 0.5,
            'variable' => 'counter',
        ]);

        $viewHelper->render();

        self::assertSame(2.0, $this->provider->get('counter'));
    }

    #[Test]
    public function anUndefinedVariableRaisesAnException(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->setArguments($viewHelper, [
            'variable' => 'missing',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1549454307);

        $viewHelper->render();
    }

    #[Test]
    public function aNonNumericStepRaisesAnException(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->provider->add('counter', 5);
        $this->setArguments($viewHelper, [
            'step'     => 'abc',
            'variable' => 'counter',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1549455466);

        $viewHelper->render();
    }

    #[Test]
    public function aNonNumericVariableRaisesAnException(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->provider->add('counter', 'abc');
        $this->setArguments($viewHelper, [
            'variable' => 'counter',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1549454962);

        $viewHelper->render();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function setArguments(IncrementViewHelper $viewHelper, array $arguments): void
    {
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);
    }

    private function createViewHelper(): IncrementViewHelper
    {
        $renderingContext = new RenderingContext();
        $this->provider   = new StandardVariableProvider();
        $renderingContext->setVariableProvider($this->provider);

        $viewHelper = new IncrementViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setRenderChildrenClosure(static fn() => '');
        $viewHelper->initializeArguments();

        return $viewHelper;
    }
}
