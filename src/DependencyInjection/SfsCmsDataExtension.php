<?php

namespace Softspring\CmsDataPlugin\DependencyInjection;

use Softspring\CmsDataPlugin\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsDataPlugin\Data\FieldTransformer\FieldTransformerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SfsCmsDataExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(EntityTransformerInterface::class)->addTag('sfs_cms.data.entity_transformer');
        $container->registerForAutoconfiguration(FieldTransformerInterface::class)->addTag('sfs_cms.data.field_transformer');

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config/services'));
        $loader->load('services.yaml');
        $loader->load('controller/admin_content.yaml');
        $loader->load('controller/admin_content_version.yaml');
    }
}
