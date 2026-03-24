<?php

namespace Softspring\CmsDataPlugin\Config\Model;

use Softspring\CmsBundle\Config\Model\ConfigExtensionInterface;
use Softspring\CmsBundle\Config\Model\Content;
use Softspring\CmsDataPlugin\Form\Admin\Content\ContentImportForm;
use Softspring\CmsDataPlugin\Form\Admin\ContentVersion\VersionImportForm;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class ContentExtension implements ConfigExtensionInterface
{
    public function extend(NodeDefinition $rootNode): void
    {
        foreach ($rootNode->getChildNodeDefinitions() as $name => $node) {
            if ('admin' !== $name) {
                continue;
            }

            $node->append(
                (new ArrayNodeDefinition('import'))
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_IMPORT')->end()
                    ->scalarNode('view')->defaultValue('@SfsCmsDataPlugin/admin/content/import.html.twig')->end()
                    ->scalarNode('type')->defaultValue(ContentImportForm::class)->end()
                    ->scalarNode('success_redirect_to')->defaultValue('')->end()
                ->end()
            );

            $node->append(
                (new ArrayNodeDefinition('version_import'))
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSION_IMPORT')->end()
                    ->scalarNode('view')->defaultValue('@SfsCmsDataPlugin/admin/content/version_import.html.twig')->end()
                    ->scalarNode('type')->defaultValue(VersionImportForm::class)->end()
                    ->scalarNode('success_redirect_to')->defaultValue('')->end()
                ->end()
            );

            $node->append(
                (new ArrayNodeDefinition('export_version'))
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('is_granted')->defaultValue('PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSION_EXPORT')->end()
                ->end()
            );
        }
    }

    public function supports(string $modelClassName): bool
    {
        return Content::class === $modelClassName;
    }
}
