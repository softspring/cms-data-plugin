<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\DataTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\FieldTransformerInterface;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;

class DataTransformerTest extends TestCase
{
    public function testItExportsWithTheFirstSupportingTransformer(): void
    {
        $files = [];
        $transformer = new DataTransformer([
            new TestFieldTransformer(false, false, 'ignored-export', 'ignored-import'),
            new TestFieldTransformer(true, true, 'exported', 'imported'),
        ]);

        self::assertSame('exported', $transformer->export('value', $files));
    }

    public function testItImportsWithTheFirstSupportingTransformer(): void
    {
        $transformer = new DataTransformer([
            new TestFieldTransformer(false, false, 'ignored-export', 'ignored-import'),
            new TestFieldTransformer(true, true, 'exported', 'imported'),
        ]);

        self::assertSame('imported', $transformer->import('value', new ReferencesRepository(), []));
    }

    public function testItReturnsOriginalDataWhenNoTransformerSupportsIt(): void
    {
        $data = ['title' => 'Home'];
        $files = [];
        $transformer = new DataTransformer([
            new TestFieldTransformer(false, false, 'ignored-export', 'ignored-import'),
        ]);

        self::assertSame($data, $transformer->export($data, $files));
        self::assertSame($data, $transformer->import($data, new ReferencesRepository(), []));
    }
}

class TestFieldTransformer implements FieldTransformerInterface
{
    public function __construct(
        private readonly bool $supportsExport,
        private readonly bool $supportsImport,
        private readonly mixed $exportedData,
        private readonly mixed $importedData,
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return $this->supportsExport;
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return $this->exportedData;
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return $this->supportsImport;
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $this->importedData;
    }
}
