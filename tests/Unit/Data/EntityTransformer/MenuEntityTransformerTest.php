<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\EntityTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Manager\MenuItemManagerInterface;
use Softspring\CmsBundle\Manager\MenuManagerInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\MenuItemInterface;
use Softspring\CmsDataPlugin\Data\EntityTransformer\MenuEntityTransformer;
use Softspring\CmsDataPlugin\Data\Exception\InvalidElementException;
use Softspring\CmsDataPlugin\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use stdClass;

class MenuEntityTransformerTest extends TestCase
{
    public function testItExportsMenus(): void
    {
        $item = $this->createStub(MenuItemInterface::class);
        $item->method('getText')->willReturn(['en' => 'Home']);
        $item->method('getSymfonyRoute')->willReturn(['route' => 'homepage']);

        $menu = $this->createStub(MenuInterface::class);
        $menu->method('getType')->willReturn('main');
        $menu->method('getName')->willReturn('Main menu');
        $menu->method('getItems')->willReturn(new ArrayCollection([$item]));

        $transformer = new MenuEntityTransformer($this->createStub(MenuManagerInterface::class), $this->createStub(MenuItemManagerInterface::class));

        self::assertSame(0, MenuEntityTransformer::getPriority());
        self::assertTrue($transformer->supports('menus'));
        self::assertSame([
            'menu' => [
                'type' => 'main',
                'name' => 'Main menu',
                'items' => [
                    [
                        'text' => ['en' => 'Home'],
                        'symfony_route' => ['route' => 'homepage'],
                    ],
                ],
            ],
        ], $transformer->export($menu));
    }

    public function testItRejectsNonMenus(): void
    {
        $this->expectException(InvalidElementException::class);

        (new MenuEntityTransformer($this->createStub(MenuManagerInterface::class), $this->createStub(MenuItemManagerInterface::class)))->export(new stdClass());
    }

    public function testItPreloadsAndImportsMenus(): void
    {
        $item = $this->createMock(MenuItemInterface::class);
        $item->expects(self::once())->method('setText')->with(['en' => 'Home']);
        $item->expects(self::once())->method('setSymfonyRoute')->with(['route' => 'homepage']);

        $menu = $this->createMock(MenuInterface::class);
        $menu->expects(self::once())->method('setName')->with('Main menu');
        $menu->expects(self::once())->method('addItem')->with($item);

        $menuManager = $this->createStub(MenuManagerInterface::class);
        $menuManager->method('createEntity')->with('main')->willReturn($menu);
        $itemManager = $this->createStub(MenuItemManagerInterface::class);
        $itemManager->method('createEntity')->willReturn($item);
        $transformer = new MenuEntityTransformer($menuManager, $itemManager);
        $references = new ReferencesRepository();
        $data = [
            'menu' => [
                'type' => 'main',
                'name' => 'Main menu',
                'items' => [
                    [
                        'text' => ['en' => 'Home'],
                        'symfony_route' => ['route' => 'homepage'],
                    ],
                ],
            ],
        ];

        $transformer->preload($data, $references);

        self::assertSame($menu, $transformer->import($data, $references));
    }

    public function testItRequiresPreloadBeforeImport(): void
    {
        $this->expectException(RunPreloadBeforeImportException::class);

        (new MenuEntityTransformer($this->createStub(MenuManagerInterface::class), $this->createStub(MenuItemManagerInterface::class)))->import(
            ['menu' => ['name' => 'Missing']],
            new ReferencesRepository()
        );
    }
}
