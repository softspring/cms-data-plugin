<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\EntityTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\RoutePathManagerInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsDataPlugin\Data\EntityTransformer\RouteEntityTransformer;
use Softspring\CmsDataPlugin\Data\Exception\InvalidElementException;
use Softspring\CmsDataPlugin\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use stdClass;

class RouteEntityTransformerTest extends TestCase
{
    public function testItExportsRoutes(): void
    {
        $site = $this->createStub(SiteInterface::class);
        $site->method('getId')->willReturn('main');
        $content = $this->createStub(ContentInterface::class);
        $content->method('getName')->willReturn('Home Page');
        $path = $this->createStub(RoutePathInterface::class);
        $path->method('getPath')->willReturn('/home');
        $path->method('getLocale')->willReturn('en');
        $path->method('getCacheTtl')->willReturn(3600);
        $parent = $this->createStub(RouteInterface::class);
        $parent->method('getId')->willReturn('root');

        $route = $this->createStub(RouteInterface::class);
        $route->method('getId')->willReturn('home');
        $route->method('getSites')->willReturn(new ArrayCollection([$site]));
        $route->method('getType')->willReturn(RouteInterface::TYPE_CONTENT);
        $route->method('getParent')->willReturn($parent);
        $route->method('getSymfonyRoute')->willReturn(null);
        $route->method('getContent')->willReturn($content);
        $route->method('getRedirectType')->willReturn(null);
        $route->method('getRedirectUrl')->willReturn(null);
        $route->method('getPaths')->willReturn(new ArrayCollection([$path]));
        $transformer = $this->createTransformer();

        self::assertSame(0, RouteEntityTransformer::getPriority());
        self::assertTrue($transformer->supports('routes'));
        self::assertSame([
            'route' => [
                'id' => 'home',
                'sites' => ['main'],
                'type' => RouteInterface::TYPE_CONTENT,
                'parent' => 'root',
                'symfony_route' => null,
                'content' => 'home-page',
                'redirect_type' => null,
                'redirect_url' => null,
                'paths' => [
                    [
                        'path' => '/home',
                        'locale' => 'en',
                        'cache_ttl' => 3600,
                    ],
                ],
            ],
        ], $transformer->export($route));
    }

    public function testItRejectsNonRoutes(): void
    {
        $this->expectException(InvalidElementException::class);

        $this->createTransformer()->export(new stdClass());
    }

    public function testItPreloadsAndImportsContentRoutes(): void
    {
        $site = $this->createStub(SiteInterface::class);
        $content = $this->createStub(ContentInterface::class);
        $path = $this->createMock(RoutePathInterface::class);
        $path->expects(self::once())->method('setPath')->with('/home');
        $path->expects(self::once())->method('setLocale')->with('en');
        $path->expects(self::once())->method('setCacheTtl')->with(3600);

        $existingPath = $this->createStub(RoutePathInterface::class);
        $route = $this->createMock(RouteInterface::class);
        $route->method('getPaths')->willReturn(new ArrayCollection([$existingPath]));
        $route->expects(self::once())->method('setId')->with('home');
        $route->expects(self::once())->method('addSite')->with($site);
        $route->expects(self::exactly(2))->method('setType')->with(self::callback(fn (?int $type): bool => in_array($type, [RouteInterface::TYPE_UNKNOWN, RouteInterface::TYPE_CONTENT], true)));
        $route->expects(self::once())->method('setContent')->with($content);
        $route->expects(self::once())->method('removePath')->with($existingPath);
        $route->expects(self::once())->method('addPath')->with($path);

        $routeManager = $this->createStub(RouteManagerInterface::class);
        $routeManager->method('createEntity')->willReturn($route);
        $pathManager = $this->createStub(RoutePathManagerInterface::class);
        $pathManager->method('createEntity')->willReturn($path);
        $transformer = new RouteEntityTransformer($routeManager, $pathManager, $this->createStub(SiteManagerInterface::class));
        $references = new ReferencesRepository();
        $references->addReference('site___main', $site);
        $references->addReference('content___home-page', $content);
        $data = [
            'route' => [
                'id' => 'home',
                'sites' => ['main'],
                'type' => RouteInterface::TYPE_CONTENT,
                'parent' => null,
                'content' => 'home-page',
                'redirect_type' => null,
                'redirect_url' => null,
                'symfony_route' => null,
                'paths' => [
                    ['path' => '/home', 'locale' => 'en', 'cache_ttl' => 3600],
                ],
            ],
        ];

        $transformer->preload($data, $references);

        self::assertSame($route, $transformer->import($data, $references));
    }

    public function testItImportsRedirectRoutes(): void
    {
        $site = $this->createStub(SiteInterface::class);
        $route = $this->createMock(RouteInterface::class);
        $route->method('getPaths')->willReturn(new ArrayCollection());
        $route->expects(self::once())->method('setRedirectUrl')->with('https://example.com');
        $route->expects(self::once())->method('setRedirectType')->with(301);

        $routeManager = $this->createStub(RouteManagerInterface::class);
        $routeManager->method('createEntity')->willReturn($route);
        $references = new ReferencesRepository();
        $references->addReference('site___main', $site);
        $data = [
            'route' => [
                'id' => 'redirect',
                'sites' => ['main'],
                'type' => RouteInterface::TYPE_REDIRECT_TO_URL,
                'parent' => null,
                'content' => null,
                'redirect_type' => 301,
                'redirect_url' => 'https://example.com',
                'symfony_route' => null,
                'paths' => [],
            ],
        ];
        $transformer = new RouteEntityTransformer($routeManager, $this->createStub(RoutePathManagerInterface::class), $this->createStub(SiteManagerInterface::class));

        $transformer->preload($data, $references);
        $transformer->import($data, $references);
    }

    public function testItRejectsUnknownRouteTypes(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Route type 999 not yet implemented');

        $route = $this->createStub(RouteInterface::class);
        $route->method('getPaths')->willReturn(new ArrayCollection());
        $routeManager = $this->createStub(RouteManagerInterface::class);
        $routeManager->method('createEntity')->willReturn($route);
        $references = new ReferencesRepository();
        $references->addReference('site___main', $this->createStub(SiteInterface::class));
        $data = [
            'route' => [
                'id' => 'unknown',
                'sites' => ['main'],
                'type' => 999,
                'parent' => null,
                'content' => null,
                'redirect_type' => null,
                'redirect_url' => null,
                'symfony_route' => null,
                'paths' => [],
            ],
        ];
        $transformer = new RouteEntityTransformer($routeManager, $this->createStub(RoutePathManagerInterface::class), $this->createStub(SiteManagerInterface::class));

        $transformer->preload($data, $references);
        $transformer->import($data, $references);
    }

    public function testItRequiresPreloadBeforeImport(): void
    {
        $this->expectException(RunPreloadBeforeImportException::class);

        $this->createTransformer()->import(['route' => ['id' => 'missing']], new ReferencesRepository());
    }

    private function createTransformer(): RouteEntityTransformer
    {
        return new RouteEntityTransformer(
            $this->createStub(RouteManagerInterface::class),
            $this->createStub(RoutePathManagerInterface::class),
            $this->createStub(SiteManagerInterface::class)
        );
    }
}
