<?php

namespace Softspring\CmsDataPlugin\Admin\Routing;

use Softspring\CmsBundle\Routing\Provider\RoutingProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ContentTypeRoutingProvider implements RoutingProviderInterface
{
    public function supportedTypes(): array
    {
        return [
            'sfs_cms_plugin_admin_content_type',
        ];
    }

    public function supports(string $type): bool
    {
        return in_array($type, $this->supportedTypes());
    }

    public function getAdminRoutes(string $type): RouteCollection
    {
        $collection = new RouteCollection();

        $collection->add('import', new Route('/import', [
            '_controller' => 'sfs_cms.data_plugin.admin.content.controller::create',
            'configKey' => 'import',
        ]));

        $collection->add('export_version', new Route('/{content}/{version}/export', [
            '_controller' => 'sfs_cms.data_plugin.admin.content_version.controller::apply',
            'configKey' => 'version_export',
        ]));

        $collection->add('import_version', new Route('/{content}/import', [
            '_controller' => 'sfs_cms.data_plugin.admin.content_version.controller::create',
            'configKey' => 'version_import',
        ]));

        return $collection;
    }
}
