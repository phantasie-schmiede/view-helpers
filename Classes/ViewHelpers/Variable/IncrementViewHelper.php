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

/**
 * Class IncrementViewHelper
 *
 * @package PSBits\ViewHelpers\ViewHelpers\Variable
 */
class IncrementViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('step', 'integer', 'amount added to the variable\'s value', false, 1);
        $this->registerArgument('variable', 'string', 'variable to be incremented', true);
    }

    public function render(): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        if (!$templateVariableContainer->exists($this->arguments['variable'])) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Variable "' . $this->arguments['variable'] . '" has not been defined in this context!',
                1549454307
            );
        }

        if (!is_numeric($this->arguments['step'])) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Argument "step" is not numeric (value: "' . $this->arguments['step'] . '")!',
                1549455466
            );
        }

        $value = $templateVariableContainer->get($this->arguments['variable']);

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Variable "' . $this->arguments['variable'] . '" is not numeric!',
                1549454962
            );
        }

        $templateVariableContainer->add(
            $this->arguments['variable'],
            $value + $this->arguments['step']
        );
    }
}
