<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Admin\ActionListener;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Admin\ActionListener\Content\ImportListener as ContentImportListener;
use Softspring\CmsDataPlugin\Admin\ActionListener\ContentVersion\ExportListener as VersionExportListener;
use Softspring\CmsDataPlugin\Admin\ActionListener\ContentVersion\ImportListener as VersionImportListener;
use Softspring\CmsDataPlugin\SfsCmsDataPlugin;

class SubscribedEventsTest extends TestCase
{
    public function testContentImportListenerEventMap(): void
    {
        self::assertSame([
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onCreateEntity', 1],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onViewAddConfig', 0],
                ['onViewSetTemplate', 0],
                ['onViewAddEntities', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENTS_IMPORT_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ], ContentImportListener::getSubscribedEvents());
    }

    public function testVersionImportListenerEventMap(): void
    {
        self::assertSame([
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onCreateEntity', 1],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_PREPARE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFormPrepareResolve', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INIT => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_VALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailure', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_FORM_INVALID => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_VIEW => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onViewAddEntities', 1],
                ['onView', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_IMPORT_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
        ], VersionImportListener::getSubscribedEvents());
    }

    public function testVersionExportListenerEventMap(): void
    {
        self::assertSame([
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_INITIALIZE => [
                ['onInitializeGetConfig', 20],
                ['onEventDispatchContentTypeEvent', 10],
                ['onEventLoadContentEntity', 9],
                ['onInitializeUpdateHelperConfig', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_LOAD_ENTITY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onLoadEntity', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_NOT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onNotFoundAddFlash', 5],
                ['onNotFoundRedirectToList', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_FOUND => [
                ['onEventDispatchContentTypeEvent', 10],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_APPLY => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onApply', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_SUCCESS => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onSuccess', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_FAILURE => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureOrException', 0],
            ],
            SfsCmsDataPlugin::ADMIN_CONTENT_VERSIONS_EXPORT_EXCEPTION => [
                ['onEventDispatchContentTypeEvent', 10],
                ['onFailureOrException', 0],
            ],
        ], VersionExportListener::getSubscribedEvents());
    }
}
