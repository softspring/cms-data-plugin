<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsDataPlugin\Data\FieldTransformer\FieldTransformerInterface;
use Softspring\CmsDataPlugin\DependencyInjection\SfsCmsDataExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SfsCmsDataExtensionTest extends TestCase
{
    public function testItLoadsServicesAndAutoconfiguresTransformers(): void
    {
        $container = new ContainerBuilder();

        (new SfsCmsDataExtension())->load([], $container);

        self::assertTrue($container->hasDefinition('Softspring\CmsDataPlugin\Data\DataTransformer'));
        self::assertTrue($container->hasDefinition('Softspring\CmsDataPlugin\Data\DataImporter'));
        self::assertTrue($container->hasDefinition('Softspring\CmsDataPlugin\Admin\ActionListener\Content\ImportListener'));
        self::assertTrue($container->getAutoconfiguredInstanceof()[EntityTransformerInterface::class]->hasTag('sfs_cms.data.entity_transformer'));
        self::assertTrue($container->getAutoconfiguredInstanceof()[FieldTransformerInterface::class]->hasTag('sfs_cms.data.field_transformer'));
    }
}
