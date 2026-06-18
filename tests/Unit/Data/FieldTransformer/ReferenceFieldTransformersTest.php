<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\FieldTransformer;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsDataPlugin\Data\FieldTransformer\BlockFieldTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\RouteFieldTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\SiteFieldTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use stdClass;

class ReferenceFieldTransformersTest extends TestCase
{
    public function testSiteTransformerExportsAndImportsReferences(): void
    {
        $site = $this->createStub(SiteInterface::class);
        $site->method('getId')->willReturn('Main Site');
        $importedSite = new stdClass();
        $referencesRepository = new ReferencesRepository();
        $referencesRepository->addReference('site___main-site', $importedSite);
        $transformer = new SiteFieldTransformer();

        self::assertSame(100, SiteFieldTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('site', $site));
        self::assertFalse($transformer->supportsExport('site', new stdClass()));
        self::assertSame(['_reference' => 'site___main-site'], $transformer->export($site));
        self::assertTrue($transformer->supportsImport('site', ['_reference' => 'site___main-site']));
        self::assertFalse($transformer->supportsImport('site', ['_reference' => 'route___home']));
        self::assertSame($importedSite, $transformer->import(['_reference' => 'site___main-site'], $referencesRepository));
    }

    public function testRouteTransformerExportsAndImportsReferences(): void
    {
        $route = $this->createStub(RouteInterface::class);
        $route->method('getId')->willReturn('route-home');
        $importedRoute = new stdClass();
        $referencesRepository = new ReferencesRepository();
        $referencesRepository->addReference('route___route-home', $importedRoute);
        $transformer = new RouteFieldTransformer();

        self::assertSame(100, RouteFieldTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('route', $route));
        self::assertFalse($transformer->supportsExport('route', new stdClass()));
        self::assertSame(['_reference' => 'route___route-home'], $transformer->export($route));
        self::assertTrue($transformer->supportsImport('route', ['_reference' => 'route___route-home']));
        self::assertFalse($transformer->supportsImport('route', ['_reference' => 'site___main']));
        self::assertSame($importedRoute, $transformer->import(['_reference' => 'route___route-home'], $referencesRepository));
    }

    public function testBlockTransformerExportsAndImportsReferences(): void
    {
        $block = $this->createStub(BlockInterface::class);
        $block->method('getName')->willReturn('Hero Block');
        $importedBlock = new stdClass();
        $referencesRepository = new ReferencesRepository();
        $referencesRepository->addReference('block___hero-block', $importedBlock);
        $transformer = new BlockFieldTransformer();

        self::assertSame(100, BlockFieldTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('block', $block));
        self::assertFalse($transformer->supportsExport('block', new stdClass()));
        self::assertSame(['_reference' => 'block___hero-block'], $transformer->export($block));
        self::assertTrue($transformer->supportsImport('block', ['_reference' => 'block___hero-block']));
        self::assertFalse($transformer->supportsImport('block', ['_reference' => 'site___main']));
        self::assertSame($importedBlock, $transformer->import(['_reference' => 'block___hero-block'], $referencesRepository));
    }
}
