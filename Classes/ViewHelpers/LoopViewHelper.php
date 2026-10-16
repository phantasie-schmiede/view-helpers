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
 * Class LoopViewHelper
 *
 * @package PSBits\ViewHelpers\ViewHelpers
 */
class LoopViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     */
    protected $escapeOutput = false;

    /**
     * @var array<string, mixed>
     */
    private array $variableBackups;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('cycle', 'string', 'Variable name for cycle (starts with 1)', false, 'cycle');
        $this->registerArgument('index', 'string', 'Variable name for index (starts with 0)', false, 'index');
        $this->registerArgument('iterations', 'int', 'Number of loops');
    }

    public function render(): string
    {
        $content = '';
        $renderChildrenClosure = $this->buildRenderChildrenClosure();
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        $this->backupVariables(
            [
                $this->arguments['cycle'],
                $this->arguments['index'],
            ]
        );

        for ($i = 0; $i < $this->arguments['iterations']; $i++) {
            $templateVariableContainer->add($this->arguments['cycle'], $i + 1);
            $templateVariableContainer->add($this->arguments['index'], $i);
            $content .= $renderChildrenClosure();
            $templateVariableContainer->remove($this->arguments['cycle']);
            $templateVariableContainer->remove($this->arguments['index']);
        }

        $this->restoreVariables(
            [
                $this->arguments['cycle'],
                $this->arguments['index'],
            ]
        );

        return $content;
    }

    /**
     * @param list<string> $variables
     */
    private function backupVariables(array $variables): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        foreach ($variables as $variable) {
            if ($templateVariableContainer->exists($variable)) {
                $this->variableBackups[$variable] = $templateVariableContainer->get($variable);
            }
        }
    }

    /**
     * @param list<string> $variables
     */
    private function restoreVariables(array $variables): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        foreach ($variables as $variable) {
            if (isset($this->variableBackups[$variable])) {
                $templateVariableContainer->add($variable, $this->variableBackups[$variable]);
            }
        }
    }
}
