<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\FieldTransformer;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\DataTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\ArrayFieldTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\DefaultTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;

class ArrayFieldTransformerTest extends TestCase
{
    public function testItSupportsOnlyArrays(): void
    {
        $transformer = new ArrayFieldTransformer(new DataTransformer([]));

        self::assertSame(0, ArrayFieldTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('array', []));
        self::assertFalse($transformer->supportsExport('string', 'value'));
        self::assertTrue($transformer->supportsImport('array', []));
        self::assertFalse($transformer->supportsImport('string', 'value'));
    }

    public function testItTransformsNestedArrayValues(): void
    {
        $transformer = new ArrayFieldTransformer(new DataTransformer([new PrefixingTransformer()]));
        $files = [];

        self::assertSame(['cms:one', 'cms:two'], $transformer->export(['one', 'two'], $files));
        self::assertSame(['imported:one', 'imported:two'], $transformer->import(['one', 'two'], new ReferencesRepository()));
    }
}

class PrefixingTransformer extends DefaultTransformer
{
    public function export(mixed $data, &$files = []): mixed
    {
        return "cms:$data";
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return "imported:$data";
    }
}
