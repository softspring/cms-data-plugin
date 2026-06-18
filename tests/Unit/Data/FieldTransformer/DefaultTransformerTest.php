<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\FieldTransformer;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\FieldTransformer\DefaultTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;

class DefaultTransformerTest extends TestCase
{
    public function testItSupportsAndReturnsAnyValue(): void
    {
        $transformer = new DefaultTransformer();
        $files = [];
        $data = ['title' => 'Home'];

        self::assertSame(-255, DefaultTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('any', $data));
        self::assertSame($data, $transformer->export($data, $files));
        self::assertTrue($transformer->supportsImport('any', $data));
        self::assertSame($data, $transformer->import($data, new ReferencesRepository()));
    }
}
