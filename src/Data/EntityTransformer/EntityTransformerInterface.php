<?php

namespace Softspring\CmsDataPlugin\Data\EntityTransformer;

use Softspring\CmsDataPlugin\Data\Exception\InvalidElementException;
use Softspring\CmsDataPlugin\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;

interface EntityTransformerInterface
{
    public static function getPriority(): int;

    public function supports(string $fieldName, $fieldValue = null): bool;

    /**
     * @throws InvalidElementException
     */
    public function export(object $element, &$files = []): array;

    public function preload(array $data, ReferencesRepository $referencesRepository): void;

    /**
     * @throws RunPreloadBeforeImportException
     */
    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): object;
}
