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
use PSBits\ViewHelpers\ViewHelpers\TagViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class TagViewHelperTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers
 */
class TagViewHelperTest extends UnitTestCase
{
    #[Test]
    public function tagIsRegisteredAsARequiredStringArgument(): void
    {
        $definitions = $this->createInitializedViewHelper()->prepareArguments();

        self::assertArrayHasKey('tag', $definitions);
        self::assertSame('string', $definitions['tag']->getType());
        self::assertTrue($definitions['tag']->isRequired());
    }

    #[Test]
    public function theGivenTagIsRendered(): void
    {
        $viewHelper = $this->createInitializedViewHelper();
        $this->setArguments($viewHelper, [
            'tag' => 'span',
        ]);

        self::assertSame('<span />', $viewHelper->render());
    }

    #[Test]
    public function theChildContentIsUsedAsTagContent(): void
    {
        $viewHelper = $this->createInitializedViewHelper(static fn() => 'Hello');
        $this->setArguments($viewHelper, [
            'tag' => 'p',
        ]);

        self::assertSame('<p>Hello</p>', $viewHelper->render());
    }

    #[Test]
    public function arbitraryTagsAreRendered(): void
    {
        $viewHelper = $this->createInitializedViewHelper(static fn() => 'content');
        $this->setArguments($viewHelper, [
            'tag' => 'div',
        ]);

        self::assertSame('<div>content</div>', $viewHelper->render());
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function setArguments(TagViewHelper $viewHelper, array $arguments): void
    {
        $defaults = array_map(
            static fn($definition) => $definition->getDefaultValue(),
            $viewHelper->prepareArguments()
        );
        $viewHelper->setArguments($arguments + $defaults);
    }

    /**
     * @param callable(): string|null $renderChildren
     */
    private function createInitializedViewHelper(?callable $renderChildren = null): TagViewHelper
    {
        $viewHelper = new TagViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->setRenderChildrenClosure($renderChildren ?? static fn() => '');
        $viewHelper->initializeArguments();

        return $viewHelper;
    }
}
