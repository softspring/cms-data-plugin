<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\FieldTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Mapping\MappingException;
use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\FieldTransformer\EntityFieldTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use stdClass;

class EntityFieldTransformerTest extends TestCase
{
    public function testItSupportsMappedObjects(): void
    {
        $entity = new stdClass();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getClassMetadata')->with(stdClass::class)->willReturn(new ClassMetadata(stdClass::class));

        $transformer = new EntityFieldTransformer($em);

        self::assertSame(10, EntityFieldTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('entity', $entity));
    }

    public function testItRejectsNonObjectsAndUnmappedObjects(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getClassMetadata')->willThrowException(new MappingException());

        $transformer = new EntityFieldTransformer($em);

        self::assertFalse($transformer->supportsExport('entity', 'value'));
        self::assertFalse($transformer->supportsExport('entity', new stdClass()));
    }

    public function testItExportsAndImportsEntityReferences(): void
    {
        $entity = new stdClass();
        $imported = new stdClass();
        $metadata = new ClassMetadata(stdClass::class);
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $unitOfWork->method('getEntityIdentifier')->with($entity)->willReturn(['id' => 7]);
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->with(['id' => 7])->willReturn($imported);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getClassMetadata')->with(stdClass::class)->willReturn($metadata);
        $em->method('getUnitOfWork')->willReturn($unitOfWork);
        $em->method('getRepository')->with(stdClass::class)->willReturn($repository);

        $transformer = new EntityFieldTransformer($em);
        $export = $transformer->export($entity);

        self::assertSame(['_entity' => ['class' => stdClass::class, 'id' => ['id' => 7]]], $export);
        self::assertTrue($transformer->supportsImport('entity', $export));
        self::assertFalse($transformer->supportsImport('entity', ['_entity' => ['class' => stdClass::class]]));
        self::assertSame($imported, $transformer->import($export, new ReferencesRepository()));
    }
}
