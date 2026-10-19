<?php

declare(strict_types=1);

/*
 * This file is part of PSBits ViewHelpers.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Content;

use PHPUnit\Framework\Attributes\Test;
use PSBits\ViewHelpers\ViewHelpers\Content\RenderViewHelper;
use ReflectionMethod;
use RuntimeException;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class RenderViewHelperTest
 *
 * The ViewHelper's uid collection logic; the actual record rendering is
 * covered by the functional RenderViewHelperTest.
 *
 * @package PSBits\ViewHelpers\Tests\Unit\ViewHelpers\Content
 */
class RenderViewHelperTest extends UnitTestCase
{
    #[Test]
    public function anIntegerIsCollected(): void
    {
        self::assertSame([5], $this->collectUids(5));
    }

    #[Test]
    public function aCommaSeparatedListIsExploded(): void
    {
        self::assertSame([1, 2, 3], $this->collectUids('1,2,3'));
    }

    #[Test]
    public function anEmptyListYieldsNoUids(): void
    {
        self::assertSame([], $this->collectUids(''));
    }

    #[Test]
    public function anArrayOfUidsIsCollected(): void
    {
        self::assertSame([1, 2], $this->collectUids([1, 2]));
    }

    #[Test]
    public function anEntityIsCollectedByItsUid(): void
    {
        self::assertSame([7], $this->collectUids($this->entityWithUid(7)));
    }

    #[Test]
    public function anObjectStorageIsCollectedByItsUids(): void
    {
        $storage = new ObjectStorage();
        $storage->attach($this->entityWithUid(1));
        $storage->attach($this->entityWithUid(2));

        self::assertSame([1, 2], $this->collectUids($storage));
    }

    #[Test]
    public function anArrayMayMixEntitiesAndUids(): void
    {
        self::assertSame(
            [2, 1],
            $this->collectUids([
                $this->entityWithUid(2),
                1,
            ])
        );
    }

    #[Test]
    public function aNonEntityInAnObjectStorageRaisesAnException(): void
    {
        $storage = new ObjectStorage();
        $storage->attach(new class () extends AbstractDomainObject {});

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1651223157);

        $this->collectUids($storage);
    }

    #[Test]
    public function aForeignArrayEntryRaisesAnException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1651223524);

        $this->collectUids([1, 'not-a-uid']);
    }

    /**
     * @param mixed $contentElements
     * @return list<int>
     */
    private function collectUids(mixed $contentElements): array
    {
        $viewHelper = new RenderViewHelper();
        $viewHelper->setRenderingContext($this->createStub(RenderingContextInterface::class));
        $viewHelper->initializeArguments();

        $method = new ReflectionMethod(RenderViewHelper::class, 'collectUids');

        return $method->invoke($viewHelper, $contentElements);
    }

    private function entityWithUid(int $uid): AbstractEntity
    {
        $entity = new class () extends AbstractEntity {};
        $entity->_setProperty('uid', $uid);

        return $entity;
    }
}
