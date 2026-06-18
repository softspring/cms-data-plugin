<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use stdClass;

class ReferencesRepositoryTest extends TestCase
{
    public function testItStoresAndReturnsReferencesByIdentifier(): void
    {
        $entity = new stdClass();
        $repository = new ReferencesRepository();

        $repository->addReference('content.home', $entity);

        self::assertSame($entity, $repository->getReference('content.home'));
    }

    public function testItReturnsNullForMissingReferencesByDefault(): void
    {
        self::assertNull((new ReferencesRepository())->getReference('missing'));
    }

    public function testItCanThrowWhenAReferenceIsMissing(): void
    {
        $this->expectException(ReferenceNotFoundException::class);
        $this->expectExceptionMessage('Reference missing not found');

        (new ReferencesRepository())->getReference('missing', true);
    }
}
