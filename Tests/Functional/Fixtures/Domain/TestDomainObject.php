<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Functional\Fixtures\Domain;

use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

/**
 * Class TestDomainObject
 *
 * A domain model that is deliberately NOT an AbstractEntity, for the
 * RenderViewHelperTest error path.
 *
 * @package PSBits\ViewHelpers\Tests\Functional\Fixtures\Domain
 */
class TestDomainObject extends AbstractDomainObject
{
}
