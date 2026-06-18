<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\SfsCmsDataPlugin;

class SfsCmsDataPluginTest extends TestCase
{
    public function testItExposesPluginAliasAndPath(): void
    {
        $plugin = new SfsCmsDataPlugin();

        self::assertSame('sfs_cms_data', SfsCmsDataPlugin::getAlias());
        self::assertSame(\dirname(__DIR__, 2), $plugin->getPath());
    }
}
