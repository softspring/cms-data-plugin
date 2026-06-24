<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\EntityTransformer;

use DateTime;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsDataPlugin\Data\DataTransformer;
use Softspring\CmsDataPlugin\Data\EntityTransformer\BlockEntityTransformer;
use Softspring\CmsDataPlugin\Data\Exception\InvalidElementException;
use Softspring\CmsDataPlugin\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use stdClass;

class BlockEntityTransformerTest extends TestCase
{
    public function testItExportsBlocks(): void
    {
        $files = [];
        $block = $this->createStub(BlockInterface::class);
        $block->method('getType')->willReturn('hero');
        $block->method('getName')->willReturn('Hero Block');
        $block->method('getPublishStartDate')->willReturn(new DateTime('2026-06-24 10:00:00 UTC'));
        $block->method('getPublishEndDate')->willReturn(null);
        $block->method('getData')->willReturn(['title' => 'Home']);
        $dataTransformer = $this->createStub(DataTransformer::class);
        $dataTransformer->method('export')->with(['title' => 'Home'], $files)->willReturn(['title' => 'Exported']);
        $transformer = new BlockEntityTransformer($this->createStub(BlockManagerInterface::class), $dataTransformer);

        self::assertSame(0, BlockEntityTransformer::getPriority());
        self::assertTrue($transformer->supports('blocks'));
        self::assertSame([
            'block' => [
                'type' => 'hero',
                'name' => 'Hero Block',
                'publish_start_date' => '2026-06-24 10:00:00',
                'publish_end_date' => null,
                'data' => ['title' => 'Exported'],
            ],
        ], $transformer->export($block, $files));
    }

    public function testItRejectsNonBlocks(): void
    {
        $this->expectException(InvalidElementException::class);

        (new BlockEntityTransformer($this->createStub(BlockManagerInterface::class), $this->createStub(DataTransformer::class)))->export(new stdClass());
    }

    public function testItPreloadsAndImportsBlocks(): void
    {
        $block = $this->createMock(BlockInterface::class);
        $block->expects(self::once())->method('setName')->with('Hero Block');
        $block->expects(self::once())->method('setData')->with(['title' => 'Imported']);
        $block->expects(self::once())->method('setPublishStartDate')->with(self::isInstanceOf(DateTime::class));
        $block->expects(self::once())->method('setPublishEndDate')->with(null);

        $blockManager = $this->createStub(BlockManagerInterface::class);
        $blockManager->method('createEntity')->with('hero')->willReturn($block);
        $transformer = new BlockEntityTransformer($blockManager, $this->createStub(DataTransformer::class));
        $references = new ReferencesRepository();
        $data = [
            'block' => [
                'type' => 'hero',
                'name' => 'Hero Block',
                'publish_start_date' => '2026-06-24 10:00:00',
                'publish_end_date' => null,
                'data' => ['title' => 'Imported'],
            ],
        ];

        $transformer->preload($data, $references);

        self::assertSame($block, $transformer->import($data, $references));
    }

    public function testItRequiresPreloadBeforeImport(): void
    {
        $this->expectException(RunPreloadBeforeImportException::class);

        (new BlockEntityTransformer($this->createStub(BlockManagerInterface::class), $this->createStub(DataTransformer::class)))->import(
            ['block' => ['name' => 'Missing']],
            new ReferencesRepository()
        );
    }
}
