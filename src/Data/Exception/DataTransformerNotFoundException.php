<?php

namespace Softspring\CmsDataPlugin\Data\Exception;

use Exception;
use Throwable;

class DataTransformerNotFoundException extends Exception
{
    public function __construct(string $type, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Data transformer not found for '$type' elements", $code, $previous);
    }
}
