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
use function is_array;

/**
 * Class IsArrayViewHelper
 *
 * @package PSBits\ViewHelpers\ViewHelpers\Condition
 */
class IsArrayViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'mixed', 'the value to check', true);
    }

    public function render(): bool|string
    {
        return is_array($this->arguments['value']);
    }
}
