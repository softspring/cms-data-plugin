<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\EntityTransformer;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\EntityTransformer\MediaEntityTransformer;
use Softspring\CmsDataPlugin\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\MediaBundle\EntityManager\MediaManagerInterface;
use Softspring\MediaBundle\EntityManager\MediaVersionManagerInterface;
use Softspring\MediaBundle\Model\MediaInterface;
use Softspring\MediaBundle\Model\MediaVersionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaEntityTransformerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/cms_data_media_'.bin2hex(random_bytes(6));
        (new Filesystem())->mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tempDir);
    }

    public function testItPreloadsAndImportsMediaVersions(): void
    {
        $uploadPath = $this->tempDir.'/original.txt';
        file_put_contents($uploadPath, 'file-content');

        $descriptions = [];
        $media = $this->createMock(MediaInterface::class);
        $media->expects(self::exactly(2))
            ->method('setDescription')
            ->willReturnCallback(function (?string $description) use (&$descriptions): void {
                $descriptions[] = $description;
            });
        $media->expects(self::once())->method('setMediaType')->with(MediaInterface::MEDIA_TYPE_IMAGE);
        $media->expects(self::once())->method('setType')->with('image');
        $media->expects(self::once())->method('setName')->with('Logo');
        $media->expects(self::once())->method('addVersion')->with(self::isInstanceOf(MediaVersionInterface::class));

        $version = $this->createMock(MediaVersionInterface::class);
        $version->expects(self::once())->method('setVersion')->with('original');
        $version->expects(self::once())
            ->method('setUpload')
            ->with(self::isInstanceOf(UploadedFile::class), true);

        $mediaManager = $this->createStub(MediaManagerInterface::class);
        $mediaManager->method('createEntity')->willReturn($media);
        $versionManager = $this->createStub(MediaVersionManagerInterface::class);
        $versionManager->method('createEntity')->willReturn($version);

        $references = new ReferencesRepository();
        $transformer = new MediaEntityTransformer($mediaManager, $versionManager);
        $data = [
            'media' => [
                'id' => 'logo',
                'media_type' => MediaInterface::MEDIA_TYPE_IMAGE,
                'type' => 'image',
                'name' => 'Logo',
                'description' => 'Main logo',
                'versionFiles' => ['original' => 'logo-original'],
            ],
            'files' => [
                'logo-original' => ['tmpPath' => $uploadPath, 'name' => 'logo.txt'],
            ],
        ];

        self::assertSame(0, MediaEntityTransformer::getPriority());
        self::assertTrue($transformer->supports('media'));
        self::assertSame([], $transformer->export($media));

        $transformer->preload($data, $references);

        self::assertSame($media, $transformer->import($data, $references));
        self::assertSame(['logo', 'Main logo'], $descriptions);
    }

    public function testItRequiresPreloadBeforeImport(): void
    {
        $this->expectException(RunPreloadBeforeImportException::class);

        (new MediaEntityTransformer(
            $this->createStub(MediaManagerInterface::class),
            $this->createStub(MediaVersionManagerInterface::class)
        ))->import(['media' => ['id' => 'missing']], new ReferencesRepository());
    }
}
