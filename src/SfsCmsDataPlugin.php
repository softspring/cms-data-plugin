<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin;

use Softspring\CmsBundle\Plugin\SfsCmsPlugin;
use Softspring\CmsDataPlugin\Config\Model\ContentExtension;

class SfsCmsDataPlugin extends SfsCmsPlugin
{
    public const ADMIN_CONTENTS_IMPORT_INITIALIZE = 'sfs_cms.admin.contents.import_initialize';
    public const ADMIN_CONTENTS_IMPORT_ENTITY = 'sfs_cms.admin.contents.import_import_entity';
    public const ADMIN_CONTENTS_IMPORT_FORM_PREPARE = 'sfs_cms.admin.contents.import_form_prepare';
    public const ADMIN_CONTENTS_IMPORT_FORM_INIT = 'sfs_cms.admin.contents.import_form_init';
    public const ADMIN_CONTENTS_IMPORT_FORM_VALID = 'sfs_cms.admin.contents.import_form_valid';
    public const ADMIN_CONTENTS_IMPORT_APPLY = 'sfs_cms.admin.contents.import_apply';
    public const ADMIN_CONTENTS_IMPORT_SUCCESS = 'sfs_cms.admin.contents.import_success';
    public const ADMIN_CONTENTS_IMPORT_FAILURE = 'sfs_cms.admin.contents.import_failure';
    public const ADMIN_CONTENTS_IMPORT_FORM_INVALID = 'sfs_cms.admin.contents.import_form_invalid';
    public const ADMIN_CONTENTS_IMPORT_VIEW = 'sfs_cms.admin.contents.import_view';
    public const ADMIN_CONTENTS_IMPORT_EXCEPTION = 'sfs_cms.admin.contents.import_exception';

    public const ADMIN_CONTENT_VERSIONS_IMPORT_INITIALIZE = 'sfs_cms.admin.content_versions.import_initialize';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_ENTITY = 'sfs_cms.admin.content_versions.import_import_entity';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_PREPARE = 'sfs_cms.admin.content_versions.import_form_prepare';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INIT = 'sfs_cms.admin.content_versions.import_form_init';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_VALID = 'sfs_cms.admin.content_versions.import_form_valid';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_APPLY = 'sfs_cms.admin.content_versions.import_apply';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_SUCCESS = 'sfs_cms.admin.content_versions.import_success';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FAILURE = 'sfs_cms.admin.content_versions.import_failure';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INVALID = 'sfs_cms.admin.content_versions.import_form_invalid';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_VIEW = 'sfs_cms.admin.content_versions.import_view';
    public const ADMIN_CONTENT_VERSIONS_IMPORT_EXCEPTION = 'sfs_cms.admin.content_versions.import_exception';

    public const ADMIN_CONTENT_VERSIONS_EXPORT_INITIALIZE = 'sfs_cms.admin.content_versions.export_initialize';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_LOAD_ENTITY = 'sfs_cms.admin.content_versions.export_load_entity';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_NOT_FOUND = 'sfs_cms.admin.content_versions.export_not_found';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_FOUND = 'sfs_cms.admin.content_versions.export_found';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_APPLY = 'sfs_cms.admin.content_versions.export_apply';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_SUCCESS = 'sfs_cms.admin.content_versions.export_success';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_FAILURE = 'sfs_cms.admin.content_versions.export_failure';
    public const ADMIN_CONTENT_VERSIONS_EXPORT_EXCEPTION = 'sfs_cms.admin.content_versions.export_exception';

    public static function getAlias(): string
    {
        return 'sfs_cms_data';
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    protected function getConfigExtensionClasses(): array
    {
        return [
            ContentExtension::class,
        ];
    }
}
