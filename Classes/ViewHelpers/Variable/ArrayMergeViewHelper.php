<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\ViewHelpers\Variable;

use InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

use function is_array;

/**
 * Class ArrayMergeViewHelper
 *
 * @package PSBits\ViewHelpers\ViewHelpers\Variable
 */
class ArrayMergeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('arrays', 'array', 'arrays to be merged', true);
        $this->registerArgument('as', 'string', 'variable name of the merged result', true);
        $this->registerArgument('overwrite', 'boolean', 'overwrites the variable if already existing', false, false);
    }

    public function render(): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        if (!$this->arguments['overwrite'] && $templateVariableContainer->exists($this->arguments['as'])) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Variable "' . $this->arguments['as'] . '" already exists!',
                1549520834
            );
        }

        array_walk($this->arguments['arrays'], static function(&$value) {
            if (!is_array($value)) {
                $value = [];
            }
        });

        $templateVariableContainer->add($this->arguments['as'], array_merge(...$this->arguments['arrays']));
    }
}
