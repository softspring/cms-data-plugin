<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\FieldTransformer;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\DataTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\ArrayFieldTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\DefaultTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\MediaFieldTransformer;
use Softspring\CmsDataPlugin\Data\FieldTransformer\TranslationFieldTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\MediaBundle\Model\MediaInterface;
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

    public function testItKeepsPlainTranslationsAsTranslationsWhenItImportsTheLocaleValues(): void
    {
        $data = [
            '_trans_id' => 'title',
            '_default' => 'en',
            'en' => 'Home',
            'es' => 'Inicio',
        ];

        $translation = $this->createTransformer()->import($data, new ReferencesRepository());

        self::assertInstanceOf(Translation::class, $translation);
        self::assertEquals($data, $translation->__toArray());
    }

    public function testItResolvesReferencesInsideTranslatableFields(): void
    {
        $media = $this->createStub(MediaInterface::class);
        $repository = new ReferencesRepository();
        $repository->addReference('media___media-id', $media);

        $imported = $this->createTransformer()->import([
            '_trans_id' => 'image',
            '_default' => 'en',
            'en' => ['media' => ['_reference' => 'media___media-id'], 'version' => 'image#_original'],
            'es' => null,
        ], $repository);

        self::assertSame([
            '_trans_id' => 'image',
            '_default' => 'en',
            'en' => ['media' => $media, 'version' => 'image#_original'],
            'es' => null,
        ], $imported);
    }

    private function createTransformer(): TranslationFieldTransformer
    {
        // Field transformers are ordered by priority, like the tagged iterator of the container.
        $transformers = new ArrayObject();
        $dataTransformer = new DataTransformer($transformers);
        $translationTransformer = new TranslationFieldTransformer($dataTransformer);
        $transformers->append(new MediaFieldTransformer());
        $transformers->append($translationTransformer);
        $transformers->append(new ArrayFieldTransformer($dataTransformer));
        $transformers->append(new DefaultTransformer());

        return $translationTransformer;
    }
}
