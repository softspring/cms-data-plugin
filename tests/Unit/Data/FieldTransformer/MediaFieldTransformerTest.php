<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\FieldTransformer;

use Exception;
use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\FieldTransformer\MediaFieldTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\Model\MediaVersionInterface;
use stdClass;

class MediaFieldTransformerTest extends TestCase
{
    public function testItExportsGoogleCloudStorageMedia(): void
    {
        $files = [];
        $media = $this->createMedia('image/png', 'gs://bucket/path/image.png');
        $transformer = new MediaFieldTransformer('/media');

        self::assertSame(100, MediaFieldTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('media', $media));
        self::assertFalse($transformer->supportsExport('media', new stdClass()));
        self::assertSame(['_reference' => 'media___media-id'], $transformer->export($media, $files));
        self::assertSame([
            '@type' => 'file',
            '@location' => 'gcs',
            'bucket' => 'bucket',
            'object' => 'path/image.png',
        ], $files['media/media-id.png']);
        self::assertSame('image', $files['media/media-id.json']['json']['type']);
    }

    public function testItExportsFilesystemMedia(): void
    {
        $files = [];
        $media = $this->createMedia('image/svg+xml', 'sfs-media-filesystem://folder/icon.svg');
        $transformer = new MediaFieldTransformer('/storage');

        $transformer->export($media, $files);

        self::assertSame([
            '@type' => 'file',
            '@location' => 'sfs-media-filesystem',
            'path' => '/storage/folder',
            'object' => 'icon.svg',
        ], $files['media/media-id.svg']);
    }

    public function testItRejectsUnsupportedMediaProtocols(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('protocol not supported');

        (new MediaFieldTransformer())->export($this->createMedia('image/jpeg', 'https://example.com/image.jpg'));
    }

    public function testItImportsMediaReferences(): void
    {
        $media = new stdClass();
        $repository = new ReferencesRepository();
        $repository->addReference('media___media-id', $media);
        $transformer = new MediaFieldTransformer();

        self::assertTrue($transformer->supportsImport('media', ['_reference' => 'media___media-id']));
        self::assertFalse($transformer->supportsImport('media', ['_reference' => 'block___hero']));
        self::assertSame($media, $transformer->import(['_reference' => 'media___media-id'], $repository));
    }

    private function createMedia(string $mimeType, string $url): MediaInterface
    {
        $version = $this->createStub(MediaVersionInterface::class);
        $version->method('getFileMimeType')->willReturn($mimeType);
        $version->method('getUrl')->willReturn($url);

        $media = $this->createStub(MediaInterface::class);
        $media->method('getVersion')->with('_original')->willReturn($version);
        $media->method('getId')->willReturn('media-id');
        $media->method('getType')->willReturn('image');
        $media->method('getMediaType')->willReturn(MediaInterface::MEDIA_TYPE_IMAGE);
        $media->method('getName')->willReturn('Media');
        $media->method('getDescription')->willReturn('Description');

        return $media;
    }
}
