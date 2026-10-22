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
use PSBits\ViewHelpers\ViewHelpers\Variable\ArrayMergeViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

/**
 * Class ArrayMergeViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Variable
 */
class ArrayMergeViewHelperTest extends UnitTestCase
{
    private StandardVariableProvider $provider;

    #[Test]
    public function theArraysAreMergedIntoTheGivenVariable(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->setArguments($viewHelper, [
            'arrays' => [
                ['a' => 1],
                ['b' => 2],
            ],
            'as'     => 'merged',
        ]);

        $viewHelper->render();

        self::assertSame(['a' => 1, 'b' => 2], $this->provider->get('merged'));
    }

    #[Test]
    public function laterArraysOverwriteMatchingKeys(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->setArguments($viewHelper, [
            'arrays' => [
                ['a' => 1],
                ['a' => 2],
            ],
            'as'     => 'merged',
        ]);

        $viewHelper->render();

        self::assertSame(['a' => 2], $this->provider->get('merged'));
    }

    #[Test]
    public function nonArrayEntriesAreCoercedToEmptyArrays(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->setArguments($viewHelper, [
            'arrays' => [
                'not-an-array',
                ['a' => 1],
                null,
            ],
            'as'     => 'merged',
        ]);

        $viewHelper->render();

        self::assertSame(['a' => 1], $this->provider->get('merged'));
    }

    #[Test]
    public function anExistingVariableIsReplacedWhenOverwritingIsAllowed(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->provider->add('merged', ['old' => true]);
        $this->setArguments($viewHelper, [
            'arrays'    => [
                ['a' => 1],
            ],
            'as'        => 'merged',
            'overwrite' => true,
        ]);

        $viewHelper->render();

        self::assertSame(['a' => 1], $this->provider->get('merged'));
    }

    #[Test]
    public function anExistingVariableRaisesAnExceptionWithoutOverwrite(): void
    {
        $viewHelper = $this->createViewHelper();
        $this->provider->add('merged', ['old' => true]);
        $this->setArguments($viewHelper, [
            'arrays' => [
                ['a' => 1],
            ],
            'as'     => 'merged',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1549520834);

        $viewHelper->render();
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function setArguments(ArrayMergeViewHelper $viewHelper, array $arguments): void
    {
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);
    }

    private function createViewHelper(): ArrayMergeViewHelper
    {
        $renderingContext = new RenderingContext();
        $this->provider   = new StandardVariableProvider();
        $renderingContext->setVariableProvider($this->provider);

        $viewHelper = new ArrayMergeViewHelper();
        $viewHelper->setRenderingContext($renderingContext);
        $viewHelper->setRenderChildrenClosure(static fn() => '');
        $viewHelper->initializeArguments();

        return $viewHelper;
    }
}
