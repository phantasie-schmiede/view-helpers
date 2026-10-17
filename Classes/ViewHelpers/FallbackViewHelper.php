<?php
declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FallbackViewHelper
 *
 * This ViewHelper allows you to define an ordered list of values from which the first non-empty element will be
 * returned. This will allow the replacement of such constructs:
 * <f:if condition="{settings.option1}">
 *     <f:then>{settings.option1}</f:then>
 *     <f:else if="{settings.option2}">{settings.option2}</f:else>
 *     <f:else>{settings.option3}</f:else>
 * </f:if>
 *
 * with:
 * <psb:fallback values="{0: option1, 1: option2, 2: option3}" />
 *
 * The existing OrViewHelper from the fluid package is restricted to one fallback value and does check for NULL only! An
 * empty string will not trigger the alternative value.
 *
 * @package PSBits\ViewHelpers\ViewHelpers
 */
class FallbackViewHelper extends AbstractViewHelper
{
    public function render(): mixed
    {
        /** @var array<string, mixed> $values */
        $values = $this->arguments['values'];

        foreach ($values as $value) {
            if (!empty($value)) {
                return $value;
            }
        }

        return null;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('values', 'array', 'First non-empty value will be returned.', true);
    }
}
