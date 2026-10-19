<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Unit\Data;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\Data\ExtensionInformation;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ExtensionInformationTest
 *
 * @package PSBits\ViewHelpers\Tests\Unit\Data
 */
class ExtensionInformationTest extends UnitTestCase
{
    #[Test]
    public function theInformationIsExtractedFromTheExtensionNamespace(): void
    {
        $information = new ExtensionInformation();

        self::assertSame('PSBits', $information->getVendorName());
        self::assertSame('ViewHelpers', $information->getExtensionName());
        self::assertSame('view_helpers', $information->getExtensionKey());
    }
}
