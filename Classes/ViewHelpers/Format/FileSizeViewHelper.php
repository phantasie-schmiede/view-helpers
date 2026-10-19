<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\ViewHelpers\Format;

use PSBits\Foundation\Utility\FileUtility;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FileSizeViewHelper
 *
 * @package PSBits\ViewHelpers\ViewHelpers\Format
 */
class FileSizeViewHelper extends AbstractViewHelper
{
    /**
     * If argument size is not provided, the tag content will be used instead.
     *
     * @throws AspectNotFoundException
     */
    public function render(): string
    {
        $size = $this->arguments['size'] ?? $this->renderChildren();

        return FileUtility::formatFileSize($size, $this->arguments['unit'], $this->arguments['decimals']);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'decimals',
            'mixed',
            'number of decimals that will be shown in the formatted string',
            false,
            2
        );
        $this->registerArgument('size', 'mixed', 'file size in bytes (or file name)');
        $this->registerArgument('unit', 'mixed', 'enforces a specific unit');
    }
}
