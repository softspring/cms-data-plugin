<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Admin\Routing;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Admin\Routing\ContentTypeRoutingProvider;

class ContentTypeRoutingProviderTest extends TestCase
{
    public function testItSupportsCmsPluginAdminContentTypeRoutes(): void
    {
        $provider = new ContentTypeRoutingProvider();

        self::assertSame(['sfs_cms_plugin_admin_content_type'], $provider->supportedTypes());
        self::assertTrue($provider->supports('sfs_cms_plugin_admin_content_type'));
        self::assertFalse($provider->supports('other'));
    }

    public function testItBuildsImportAndExportRoutes(): void
    {
        $routes = (new ContentTypeRoutingProvider())->getAdminRoutes('sfs_cms_plugin_admin_content_type');

        self::assertSame('/import', $routes->get('import')->getPath());
        self::assertSame('import', $routes->get('import')->getDefault('configKey'));
        self::assertSame('/{content}/{version}/export', $routes->get('export_version')->getPath());
        self::assertSame('version_export', $routes->get('export_version')->getDefault('configKey'));
        self::assertSame('/{content}/import', $routes->get('import_version')->getPath());
        self::assertSame('version_import', $routes->get('import_version')->getDefault('configKey'));
    }
}
