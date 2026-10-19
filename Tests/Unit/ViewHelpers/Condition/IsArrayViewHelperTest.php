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
use PSBits\ViewHelpers\ViewHelpers\Condition\IsArrayViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class IsArrayViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Condition
 */
class IsArrayViewHelperTest extends UnitTestCase
{
    #[Test]
    public function anArrayYieldsTrue(): void
    {
        self::assertTrue($this->render(['value' => ['a', 'b']]));
    }

    #[Test]
    public function anEmptyArrayYieldsTrue(): void
    {
        self::assertTrue($this->render(['value' => []]));
    }

    #[Test]
    public function aStringYieldsFalse(): void
    {
        self::assertFalse($this->render(['value' => 'array']));
    }

    #[Test]
    public function nullYieldsFalse(): void
    {
        self::assertFalse($this->render(['value' => null]));
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function render(array $arguments): bool
    {
        $viewHelper = new IsArrayViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure(static fn() => '');
        $viewHelper->initializeArguments();
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);

        return $viewHelper->render();
    }
}
