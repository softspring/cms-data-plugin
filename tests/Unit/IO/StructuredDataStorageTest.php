<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\IO;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\IO\StructuredDataStorage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Serializer;

class StructuredDataStorageTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/cms_data_storage_'.bin2hex(random_bytes(6));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tempDir);
    }

    public function testItSavesAndLoadsYamlFilesAndStrings(): void
    {
        $storage = $this->createStorage();
        $path = $this->tempDir.'/nested/content.yaml';
        $data = ['title' => 'Home', 'published' => true];

        self::assertSame($path, $storage->saveYaml($data, $path));
        self::assertSame($data, $storage->loadYamlFile($path));
        self::assertSame($data, $storage->loadYamlString(file_get_contents($path)));
    }

    public function testItSavesAndLoadsJsonFilesAndStrings(): void
    {
        $storage = $this->createStorage();
        $path = $this->tempDir.'/nested/content.json';
        $data = ['title' => 'Home', 'items' => ['one', 'two']];

        self::assertSame($path, $storage->saveJson($data, $path));
        self::assertSame($data, $storage->loadJsonFile($path));
        self::assertSame($data, $storage->loadJsonString(file_get_contents($path)));
    }

    private function createStorage(): StructuredDataStorage
    {
        $serializer = new Serializer([], [new JsonEncoder(), new YamlEncoder()]);

        return new StructuredDataStorage($serializer, $serializer, new Filesystem());
    }
}
