<?php

namespace Softspring\CmsDataPlugin\Data\FieldTransformer;

use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\TranslatableBundle\Model\Translation;

class TranslationFieldTransformer implements FieldTransformerInterface
{
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
     * @param  array       $data
     * @return Translation
     */
    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return Translation::createFromArray($data);
    }
}
