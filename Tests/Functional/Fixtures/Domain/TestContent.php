<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Functional\Fixtures\Domain;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class TestContent
 *
 * A minimal AbstractEntity standing in for a tt_content record in the
 * RenderViewHelperTest. Instances are created in memory; only the uid is
 * relevant, the CObject rendering itself resolves the record from the
 * database by that uid.
 *
 * @package PSBits\ViewHelpers\Tests\Functional\Fixtures\Domain
 */
class TestContent extends AbstractEntity
{
    protected string $header = '';

    public function getHeader(): string
    {
        return $this->header;
    }

    public function setHeader(string $header): self
    {
        $this->header = $header;

        return $this;
    }
}
