<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Config\Model;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Config\Model\Content;
use Softspring\CmsDataPlugin\Config\Model\ContentExtension;
use Softspring\CmsDataPlugin\Form\Admin\Content\ContentImportForm;
use Softspring\CmsDataPlugin\Form\Admin\ContentVersion\VersionImportForm;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ContentExtensionTest extends TestCase
{
    public function testItSupportsCmsContentConfiguration(): void
    {
        $extension = new ContentExtension();

        self::assertTrue($extension->supports(Content::class));
        self::assertFalse($extension->supports(self::class));
    }

    public function testItAddsImportExportAdminDefaults(): void
    {
        $treeBuilder = new TreeBuilder('content');
        $rootNode = $treeBuilder->getRootNode();
        self::assertInstanceOf(ArrayNodeDefinition::class, $rootNode);

        $adminNode = new ArrayNodeDefinition('admin');
        $adminNode->addDefaultsIfNotSet();
        $rootNode->append($adminNode);

        (new ContentExtension())->extend($rootNode);

        $config = (new Processor())->process($treeBuilder->buildTree(), [[]]);

        self::assertSame('PERMISSION_SFS_CMS_ADMIN_CONTENT_IMPORT', $config['admin']['import']['is_granted']);
        self::assertSame(ContentImportForm::class, $config['admin']['import']['type']);
        self::assertSame('PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSION_IMPORT', $config['admin']['version_import']['is_granted']);
        self::assertSame(VersionImportForm::class, $config['admin']['version_import']['type']);
        self::assertSame('PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSION_EXPORT', $config['admin']['export_version']['is_granted']);
    }
}
