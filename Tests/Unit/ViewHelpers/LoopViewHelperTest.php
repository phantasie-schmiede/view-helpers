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
use PSBits\ViewHelpers\ViewHelpers\LoopViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

/**
 * Class LoopViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers
 */
class LoopViewHelperTest extends UnitTestCase
{
    private StandardVariableProvider $provider;

    #[Test]
    public function theChildrenAreRenderedOncePerIteration(): void
    {
        [$viewHelper] = $this->createViewHelper();
        $this->setArguments($viewHelper, [
            'iterations' => 3,
        ]);

        self::assertSame('1-0;2-1;3-2;', $viewHelper->render());
    }

    #[Test]
    public function theLoopVariablesAreRemovedAgainAfterRendering(): void
    {
        [$viewHelper] = $this->createViewHelper();
        $this->setArguments($viewHelper, [
            'iterations' => 2,
        ]);

        $viewHelper->render();

        self::assertFalse($this->provider->exists('cycle'));
        self::assertFalse($this->provider->exists('index'));
    }

    #[Test]
    public function customVariableNamesAreUsed(): void
    {
        [$viewHelper] = $this->createViewHelper('loop', 'i');
        $this->setArguments($viewHelper, [
            'cycle'      => 'loop',
            'index'      => 'i',
            'iterations' => 2,
        ]);

        self::assertSame('1-0;2-1;', $viewHelper->render());
    }

    #[Test]
    public function existingVariablesAreBackedUpAndRestored(): void
    {
        [$viewHelper] = $this->createViewHelper();
        $this->provider->add('cycle', 'preset-cycle');
        $this->provider->add('index', 'preset-index');
        $this->setArguments($viewHelper, [
            'iterations' => 1,
        ]);

        self::assertSame('1-0;', $viewHelper->render());

        self::assertSame('preset-cycle', $this->provider->get('cycle'));
        self::assertSame('preset-index', $this->provider->get('index'));
    }

    #[Test]
    public function theDefaultVariableNamesAreCycleAndIndex(): void
    {
        $viewHelper  = $this->createViewHelper()[0];
        $definitions = $viewHelper->prepareArguments();

        self::assertSame('cycle', $definitions['cycle']->getDefaultValue());
        self::assertSame('index', $definitions['index']->getDefaultValue());
        self::assertFalse($definitions['iterations']->isRequired());
    }

    /**
     * prepareArguments() returns ArgumentDefinition objects, not values, so the defaults have
     * to be unwrapped the way ViewHelperNode::evaluate() does before render() is called.
     *
     * @param array<string, mixed> $arguments
     */
    private function setArguments(LoopViewHelper $viewHelper, array $arguments): void
    {
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);
    }

    /**
     * @return array{0: LoopViewHelper, 1: StandardVariableProvider}
     */
    private function createViewHelper(string $cycleVariable = 'cycle', string $indexVariable = 'index'): array
    {
        $renderingContext = new RenderingContext();
        $this->provider   = new StandardVariableProvider();
        $renderingContext->setVariableProvider($this->provider);

        $viewHelper = new LoopViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setRenderChildrenClosure(function() use ($cycleVariable, $indexVariable): string {
            return $this->provider->get($cycleVariable) . '-' . $this->provider->get($indexVariable) . ';';
        });
        $viewHelper->initializeArguments();

        return [$viewHelper, $this->provider];
    }
}
