<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsDataPlugin\Data\DataImporter;
use Softspring\CmsDataPlugin\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\MediaBundle\EntityManager\MediaManagerInterface;
use stdClass;

class DataImporterTest extends TestCase
{
    public function testItPreloadsAndImportsDataInTheExpectedOrder(): void
    {
        $site = $this->createStub(SiteInterface::class);
        $site->method('__toString')->willReturn('main');

        $cmsConfig = $this->createMock(CmsConfig::class);
        $cmsConfig->expects(self::once())->method('clearSites');
        $cmsConfig->method('getSites')->willReturn(['main' => $site]);

        $persisted = [];
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('clear');
        $entityManager->expects(self::exactly(6))
            ->method('persist')
            ->willReturnCallback(function (object $entity) use (&$persisted): void {
                $persisted[] = $entity->name;
            });
        $entityManager->expects(self::exactly(3))->method('flush');

        $mediaTransformer = new RecordingEntityTransformer('media');
        $routeTransformer = new RecordingEntityTransformer('routes');
        $blockTransformer = new RecordingEntityTransformer('blocks');
        $menuTransformer = new RecordingEntityTransformer('menus');
        $contentTransformer = new RecordingEntityTransformer('contents');
        $importer = new DataImporter(
            $entityManager,
            [$mediaTransformer, $routeTransformer, $blockTransformer, $menuTransformer, $contentTransformer],
            $cmsConfig,
            $this->createStub(SiteManagerInterface::class),
            $this->createStub(MediaManagerInterface::class),
            null
        );

        $importer->import([
            'media' => [
                ['media' => ['name' => 'Logo']],
            ],
            'routes' => [
                ['route' => ['id' => 'root', 'type' => RouteInterface::TYPE_PARENT_ROUTE]],
                ['route' => ['id' => 'home', 'type' => RouteInterface::TYPE_CONTENT]],
            ],
            'blocks' => [
                ['block' => ['name' => 'Hero']],
            ],
            'menus' => [
                ['menu' => ['name' => 'Main']],
            ],
            'contents' => [
                ['page' => ['name' => 'Home']],
            ],
        ], ['auto_publish_version' => true]);

        self::assertSame(['media', 'routes', 'routes', 'blocks', 'menus', 'contents'], array_merge(
            $mediaTransformer->preloadedTypes,
            $routeTransformer->preloadedTypes,
            $blockTransformer->preloadedTypes,
            $menuTransformer->preloadedTypes,
            $contentTransformer->preloadedTypes,
        ));
        self::assertSame(['media:Logo', 'routes:root', 'routes:home', 'blocks:Hero', 'menus:Main', 'contents:Home'], $persisted);
        self::assertSame([['auto_publish_version' => true]], $mediaTransformer->importOptions);
        self::assertSame([['auto_publish_version' => true], ['auto_publish_version' => true]], $routeTransformer->importOptions);
    }
}

class RecordingEntityTransformer implements EntityTransformerInterface
{
    /**
     * @var string[]
     */
    public array $preloadedTypes = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $importOptions = [];

    public function __construct(private readonly string $type)
    {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supports(string $fieldName, $fieldValue = null): bool
    {
        return $fieldName === $this->type;
    }

    public function export(object $element, &$files = []): array
    {
        return [];
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
        $this->preloadedTypes[] = $this->type;
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): object
    {
        $this->importOptions[] = $options;
        $name = $data['media']['name']
            ?? $data['route']['id']
            ?? $data['block']['name']
            ?? $data['menu']['name']
            ?? $data['page']['name'];

        $entity = new stdClass();
        $entity->name = "{$this->type}:{$name}";

        return $entity;
    }
}
