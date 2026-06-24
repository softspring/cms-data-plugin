<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data;

use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsDataPlugin\Data\DataExporter;
use Softspring\CmsDataPlugin\Data\EntityTransformer\ContentEntityTransformerInterface;
use Softspring\CmsDataPlugin\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\CmsDataPlugin\IO\StructuredDataStorage;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

class DataExporterTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/cms_data_exporter_'.bin2hex(random_bytes(6));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tempDir);
    }

    public function testItExportsSimpleEntitiesToExpectedYamlPaths(): void
    {
        $storage = $this->createMock(StructuredDataStorage::class);
        $storage->expects(self::exactly(3))
            ->method('saveYaml')
            ->willReturnCallback(function (array $data, string $path): string {
                self::assertContains($data, [
                    ['route' => ['id' => 'home']],
                    ['menu' => ['name' => 'Main menu']],
                    ['block' => ['name' => 'Hero block']],
                ]);

                return $path;
            });

        $exporter = new DataExporter([
            new ExportingEntityTransformer('routes', ['route' => ['id' => 'home']]),
            new ExportingEntityTransformer('menus', ['menu' => ['name' => 'Main menu']]),
            new ExportingEntityTransformer('blocks', ['block' => ['name' => 'Hero block']]),
        ], $storage, new Filesystem());

        $route = $this->createStub(RouteInterface::class);
        $route->method('getId')->willReturn('home');

        $menu = $this->createStub(MenuInterface::class);
        $menu->method('getId')->willReturn('main');
        $menu->method('getName')->willReturn('Main menu');

        $block = $this->createStub(BlockInterface::class);
        $block->method('getId')->willReturn('hero');
        $block->method('getName')->willReturn('Hero block');

        self::assertSame($this->tempDir.'/routes/home.yaml', $exporter->exportRoute($route, $this->tempDir));
        self::assertSame($this->tempDir.'/menus/main-menu.yaml', $exporter->exportMenu($menu, $this->tempDir));
        self::assertSame($this->tempDir.'/blocks/hero-block.yaml', $exporter->exportBlock($block, $this->tempDir));
    }

    public function testItExportsContentAndReferencedFiles(): void
    {
        $sourceDir = $this->tempDir.'/source';
        $targetDir = $this->tempDir.'/target';
        (new Filesystem())->mkdir($sourceDir);
        file_put_contents($sourceDir.'/logo.png', 'image-bytes');

        $storage = $this->createMock(StructuredDataStorage::class);
        $storage->expects(self::once())
            ->method('saveYaml')
            ->with(['page' => ['name' => 'Home page']], $targetDir.'/contents/home-page.yaml')
            ->willReturn($targetDir.'/contents/home-page.yaml');
        $storage->expects(self::once())
            ->method('saveJson')
            ->with(['title' => 'Home'], $targetDir.'/data/home.json')
            ->willReturn($targetDir.'/data/home.json');

        $transformer = new ExportingContentEntityTransformer(
            ['page' => ['name' => 'Home page']],
            [
                'data/home.json' => ['@type' => 'json', 'json' => ['title' => 'Home']],
                'files/logo.png' => ['@type' => 'file', '@location' => 'sfs-media-filesystem', 'path' => $sourceDir, 'object' => 'logo.png'],
            ]
        );
        $exporter = new DataExporter([$transformer], $storage, new Filesystem());

        $content = $this->createStub(ContentInterface::class);
        $content->method('getName')->willReturn('Home page');
        $content->method('getRoutes')->willReturn(new ArrayCollection());

        self::assertSame(
            $targetDir.'/contents/home-page.yaml',
            $exporter->exportContent($content, $this->createStub(ContentVersionInterface::class), ['_id' => 'page'], $targetDir)
        );
        self::assertSame('image-bytes', file_get_contents($targetDir.'/files/logo.png'));
    }
}

class ExportingEntityTransformer implements EntityTransformerInterface
{
    public function __construct(
        private readonly string $supportedType,
        private readonly array $exportedData,
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supports(string $fieldName, $fieldValue = null): bool
    {
        return $fieldName === $this->supportedType;
    }

    public function export(object $element, &$files = []): array
    {
        return $this->exportedData;
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): object
    {
        return new stdClass();
    }
}

class ExportingContentEntityTransformer implements ContentEntityTransformerInterface
{
    public function __construct(
        private readonly array $exportedData,
        private readonly array $exportedFiles,
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supports(string $fieldName, $fieldValue = null): bool
    {
        return 'page' === $fieldName;
    }

    public function export(object $element, &$files = [], ?object $contentVersion = null, ?string $contentType = null): array
    {
        $files = $this->exportedFiles;

        return $this->exportedData;
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): ContentInterface
    {
        throw new LogicException('Not used by this test.');
    }

    public function importVersion(ContentInterface $content, string $layout, array $data, array $seo, ReferencesRepository $referencesRepository, array $options = []): ContentVersionInterface
    {
        throw new LogicException('Not used by this test.');
    }
}
