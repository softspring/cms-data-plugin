<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\FieldTransformer;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\FieldTransformer\TranslationFieldTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\TranslatableBundle\Model\Translation;

class TranslationFieldTransformerTest extends TestCase
{
    public function testItExportsTranslationsAsArrays(): void
    {
        $translation = Translation::createFromArray([
            '_trans_id' => 'title',
            '_default' => 'en',
            'en' => 'Home',
            'es' => 'Inicio',
        ]);
        $transformer = new TranslationFieldTransformer();
        $files = [];

        self::assertSame(100, TranslationFieldTransformer::getPriority());
        self::assertTrue($transformer->supportsExport('translation', $translation));
        self::assertSame($translation->__toArray(), $transformer->export($translation, $files));
    }

    public function testItImportsTranslationArrays(): void
    {
        $data = [
            '_trans_id' => 'title',
            '_default' => 'en',
            'en' => 'Home',
        ];
        $transformer = new TranslationFieldTransformer();

        self::assertTrue($transformer->supportsImport('translation', $data));

        $translation = $transformer->import($data, new ReferencesRepository());

        self::assertInstanceOf(Translation::class, $translation);
        self::assertEquals($data, $translation->__toArray());
    }
}
