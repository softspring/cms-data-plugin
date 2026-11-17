<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Data\EntityTransformer;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 0)]
class PageEntityTransformer extends ContentEntityTransformer
{
}
