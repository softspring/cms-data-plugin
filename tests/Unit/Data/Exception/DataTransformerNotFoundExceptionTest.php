<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\Exception;

use PHPUnit\Framework\TestCase;
use Softspring\CmsDataPlugin\Data\Exception\DataTransformerNotFoundException;

class DataTransformerNotFoundExceptionTest extends TestCase
{
    public function testItIncludesTheElementTypeInTheMessage(): void
    {
        $exception = new DataTransformerNotFoundException('contents');

        self::assertSame("Data transformer not found for 'contents' elements", $exception->getMessage());
    }
}
