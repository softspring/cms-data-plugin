<?php

namespace Softspring\CmsDataPlugin\Data\FieldTransformer;

use Softspring\CmsDataPlugin\Data\DataTransformer;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\TranslatableBundle\Model\Translation;

class TranslationFieldTransformer implements FieldTransformerInterface
{
    public function __construct(protected ?DataTransformer $dataTransformer = null)
    {
    }

    public static function getPriority(): int
    {
        return 100;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return $data instanceof Translation;
    }

    /**
     * @param  Translation $data
     * @return array
     */
    public function export(mixed $data, &$files = []): mixed
    {
        return $data->__toArray();
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return isset($data['_trans_id']);
    }

    /**
     * Imports the value of each locale, so references inside a translatable field (for example a media
     * in a translatable media field) are resolved like any other field.
     *
     * Translations of plain values are returned as a Translation, as before. When a locale value contains an
     * entity, the array is returned instead: Translation::__toArray() does not convert entities into
     * references, so the content version could not store them or link the referenced medias.
     *
     * @param  array             $data
     * @return Translation|array
     */
    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        if (!$this->dataTransformer instanceof DataTransformer) {
            return Translation::createFromArray($data);
        }

        foreach ($data as $key => $value) {
            if (is_string($key) && str_starts_with($key, '_')) {
                continue;
            }

            $data[$key] = $this->dataTransformer->import($value, $referencesRepository, $options);
        }

        return $this->containsObject($data) ? $data : Translation::createFromArray($data);
    }

    private function containsObject(mixed $value): bool
    {
        if (is_object($value)) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->containsObject($item)) {
                    return true;
                }
            }
        }

        return false;
    }
}
