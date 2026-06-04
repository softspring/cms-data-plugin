<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Data\FieldTransformer;

use Softspring\CmsDataPlugin\Data\ReferencesRepository;

class DefaultTransformer implements FieldTransformerInterface
{
    public static function getPriority(): int
    {
        return -255;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return true;
    }

    public function export(mixed $data, &$files = []): mixed
    {
        return $data;
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return true;
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $data;
    }
}
