<?php
declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\ViewHelpers\Content;

use Closure;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Fluid\ViewHelpers\CObjectViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use function is_array;
use function is_int;
use function is_string;

/**
 * Class RenderViewHelper
 *
 * This ViewHelper renders records from tt_content. There are multiple ways of providing the UIDs.
 * Example 1:
 * <psb:content.render contentElements="{variable}" />
 *
 * Example 2:
 * <psb:content.render>{variable}</psb:content.render>
 *
 * Example 3:
 * {variable -> psb:content.render()}
 *
 * {variable} may be one of the following types:
 * - integer
 * - csv
 * - array
 * - instance of an AbstractEntity (domain model object)
 * - ObjectStorage (containing AbstractEntity instances)
 * - QueryResult (containing AbstractEntity instances)
 *
 * @package PSBits\ViewHelpers\ViewHelpers\Content
 */
class RenderViewHelper extends AbstractViewHelper
{
    protected $escapeChildren = false;

    protected $escapeOutput = false;

    /**
     * Returns the rendered HTML (unescaped, no format-ViewHelper needed).
     */
    public static function renderStatic(
        array                     $arguments,
        Closure                   $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ): string {
        $contentElements = $arguments['contentElements'] ?? $renderChildrenClosure();
        $uidList = [];

        switch (true) {
            case $contentElements instanceof AbstractEntity:
                $uidList[] = $contentElements->getUid();
                break;
            case $contentElements instanceof ObjectStorage:
            case $contentElements instanceof QueryResultInterface:
                foreach ($contentElements as $contentElement) {
                    if (!$contentElement instanceof AbstractEntity) {
                        throw new RuntimeException(
                            __CLASS__ . ': ObjectStorage has to contain only domain models that extend AbstractEntity!',
                            1651223157
                        );
                    }

                    $uidList[] = $contentElement->getUid();
                }

                break;
            case is_array($contentElements):
                foreach ($contentElements as $contentElement) {
                    if ($contentElement instanceof AbstractEntity) {
                        $uidList[] = $contentElement->getUid();
                    } elseif (true === is_int($contentElement)) {
                        $uidList[] = $contentElement;
                    } else {
                        throw new RuntimeException(
                            __CLASS__ . ': Array has to contain only domain models that extend AbstractEntity or integer values!',
                            1651223524
                        );
                    }
                }

                break;
            case is_int($contentElements):
                $uidList[] = $contentElements;
                break;
            case is_string($contentElements):
                $uidList = GeneralUtility::intExplode(',', $contentElements, true);
        }

        // Set keys to avoid PHP warnings in CObjectViewHelper.
        $arguments['currentValueKey'] = null;
        $arguments['table'] = null;

        $arguments['typoscriptObjectPath'] = 'lib.tx_viewhelpers.content';
        $renderChildrenClosure = static function() use ($uidList) {
            return implode(',', $uidList);
        };

        if (method_exists(CObjectViewHelper::class, 'renderStatic')) {
            // TYPO3 v12.4
            return CObjectViewHelper::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
        }

        // TYPO3 v13.4
        $cObjectViewHelper = GeneralUtility::makeInstance(CObjectViewHelper::class);
        $cObjectViewHelper->setArguments($arguments);
        $cObjectViewHelper->setRenderChildrenClosure($renderChildrenClosure);
        $cObjectViewHelper->setRenderingContext($renderingContext);

        return $cObjectViewHelper->render();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'contentElements',
            'mixed',
            'A list of content elements. May be AbstractEntity, ObjectStorage, QueryResult, array, integer or comma separated list.'
        );
    }
}
