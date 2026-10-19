<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Class TagViewHelper
 *
 * Simple view helper to render a tag with the given name. This allows to set dynamic attributes in standard HTML tags
 * that are not represented by a ViewHelper without using ugly inline constructs.
 *
 * @package PSBits\ViewHelpers\ViewHelpers
 */
class TagViewHelper extends AbstractTagBasedViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('tag', 'string', 'Tag type to be rendered', true);
    }

    public function render(): string
    {
        $childContent = $this->renderChildren();
        $this->tag->setContent($childContent);
        $this->tag->setTagName($this->arguments['tag']);

        return $this->tag->render();
    }
}
