<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\ViewHelpers\Condition;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ExtensionLoadedViewHelper
 *
 * @package PSBits\ViewHelpers\ViewHelpers\Conditions
 */
class ExtensionLoadedViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('extensionKey', 'string', 'Key or name of the extension which shall be checked.', true);
    }

    public function render(): string
    {
        if (ExtensionManagementUtility::isLoaded(
            GeneralUtility::camelCaseToLowerCaseUnderscored($this->arguments['extensionKey'])
        )) {
            return $this->renderChildren();
        }

        return '';
    }
}
