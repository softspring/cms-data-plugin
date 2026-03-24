<?php

namespace Softspring\CmsDataPlugin;

use Softspring\CmsBundle\Plugin\SfsCmsPlugin;

class SfsCmsDataPlugin extends SfsCmsPlugin
{
    public static function getAlias(): string
    {
        return 'sfs_cms_data';
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
