<?php
declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\ViewHelpers\Condition;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use function in_array;

/**
 * Class InArrayViewHelper
 *
 * @package PSBits\ViewHelpers\ViewHelpers
 */
class InArrayViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('haystack', 'array', 'the data array', true);
        $this->registerArgument('needle', 'mixed', 'the value to search for', true);
        $this->registerArgument('strict', 'bool', 'apply strict comparison', false, true);
    }

    public function render(): bool|string
    {
        if (in_array($this->arguments['needle'], $this->arguments['haystack'], $this->arguments['strict'])) {
            $renderedChildren = trim($this->renderChildren());

            return $renderedChildren ?: true;
        }

        return false;
    }
}
