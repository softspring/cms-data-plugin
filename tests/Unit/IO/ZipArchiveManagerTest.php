<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\IO\StructuredDataStorage;
use Softspring\CmsDataPlugin\IO\ZipArchiveManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\ChainDecoder;
use Symfony\Component\Serializer\Encoder\ChainEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use ZipArchive;

class ZipArchiveManagerTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir().'/cms_data_test_'.bin2hex(random_bytes(4));
        (new Filesystem())->mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tmpDir);
    }

    public function testItReadsStructuredDataAndMediaFilesFromZip(): void
    {
        $zipName = 'fixture.zip';
        $zip = new ZipArchive();
        self::assertTrue($zip->open($this->tmpDir.'/'.$zipName, ZipArchive::CREATE));
        $zip->addFromString('contents/home.yaml', "page:\n  name: Home\n");
        $zip->addFromString('routes/home.yaml', "route:\n  id: home\n");
        $zip->addFromString('media/logo.json', '{"id":"logo"}');
        $zip->addFromString('media/logo.png', 'PNG');
        $zip->addFromString('ignored-root.yaml', 'ignored: true');
        $zip->close();

        $contents = $this->createManager()->read($this->tmpDir, $zipName);

        self::assertSame('Home', $contents['contents']['home']['page']['name']);
        self::assertSame('home', $contents['routes']['home']['route']['id']);
        self::assertSame('logo', $contents['media']['logo']['media']['id']);
        self::assertSame('logo.png', $contents['media']['logo']['files']['media/logo.png']['name']);
        self::assertFileExists($contents['media']['logo']['files']['media/logo.png']['tmpPath']);
    }

    public function testItReturnsFalseWhenZipCannotBeOpened(): void
    {
        self::assertFalse($this->createManager()->read($this->tmpDir, 'missing.zip'));
    }

    public function testItDumpsDirectoriesToZipAndCreatesDownloadResponse(): void
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->tmpDir.'/contents');
        $filesystem->dumpFile($this->tmpDir.'/contents/home.yaml', "page:\n  name: Home\n");
        $zipPath = $this->tmpDir.'/export.zip';
        $manager = $this->createManager();

        self::assertSame($zipPath, $manager->dump($this->tmpDir, $zipPath));
        self::assertFileExists($zipPath);

        $response = $manager->dumpResponse($this->tmpDir, $this->tmpDir.'/response.zip', false);

        self::assertSame('attachment; filename=response.zip', $response->headers->get('Content-Disposition'));
    }

    private function createManager(): ZipArchiveManager
    {
        $encoders = [new JsonEncoder(), new YamlEncoder()];

        return new ZipArchiveManager(
            new StructuredDataStorage(new ChainEncoder($encoders), new ChainDecoder($encoders), new Filesystem()),
            new Filesystem()
        );
    }
}
