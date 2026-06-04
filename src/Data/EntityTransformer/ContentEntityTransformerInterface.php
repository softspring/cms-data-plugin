<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Data\EntityTransformer;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsDataPlugin\Data\Exception\InvalidElementException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;

interface ContentEntityTransformerInterface extends EntityTransformerInterface
{
    /**
     * @throws InvalidElementException
     */
    public function export(object $element, &$files = [], ?object $contentVersion = null, ?string $contentType = null): array;

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): ContentInterface;

    public function importVersion(ContentInterface $content, string $layout, array $data, array $seo, ReferencesRepository $referencesRepository, array $options = []): ContentVersionInterface;
}
