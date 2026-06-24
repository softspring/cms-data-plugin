<?php

declare(strict_types=1);

namespace Softspring\CmsDataPlugin\Tests\Unit\Data\EntityTransformer;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsDataPlugin\Data\DataTransformer;
use Softspring\CmsDataPlugin\Data\EntityTransformer\PageEntityTransformer;
use Softspring\CmsDataPlugin\Data\Exception\InvalidElementException;
use Softspring\CmsDataPlugin\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsDataPlugin\Data\ReferencesRepository;
use Softspring\MediaBundle\EntityManager\MediaManagerInterface;
use stdClass;

class ContentEntityTransformerTest extends TestCase
{
    public function testItExportsContentWithVersionData(): void
    {
        $site = $this->createStub(SiteInterface::class);
        $site->method('getId')->willReturn('main');

        $content = $this->createStub(ContentInterface::class);
        $content->method('getName')->willReturn('Home page');
        $content->method('getDefaultLocale')->willReturn('en');
        $content->method('getLocales')->willReturn(['en', 'es']);
        $content->method('getSites')->willReturn(new ArrayCollection([$site]));
        $content->method('getExtraData')->willReturn(['template' => 'home']);
        $content->method('getIndexing')->willReturn(['robots' => 'index']);

        $version = $this->createStub(ContentVersionInterface::class);
        $version->method('getSeo')->willReturn(['title' => 'Home']);
        $version->method('getLayout')->willReturn('default');
        $version->method('getData')->willReturn(['body' => 'Welcome']);
        $version->method('getVersionNumber')->willReturn(3);
        $version->method('getOrigin')->willReturn(ContentVersionInterface::ORIGIN_IMPORT);
        $version->method('getOriginDescription')->willReturn('fixture');
        $version->method('getNote')->willReturn('Initial version');
        $version->method('getCreatedAt')->willReturn(new DateTime('2026-01-02 03:04:05'));
        $version->method('getMeta')->willReturn(['author' => 'cms']);

        $dataTransformer = $this->createMock(DataTransformer::class);
        $dataTransformer->expects(self::exactly(2))
            ->method('export')
            ->willReturnCallback(fn (mixed $data, array &$files = []): mixed => ['exported' => $data]);

        $transformer = $this->createTransformer(dataTransformer: $dataTransformer);

        self::assertSame(0, PageEntityTransformer::getPriority());
        self::assertTrue($transformer->supports('contents'));
        self::assertTrue($transformer->supports('page'));
        self::assertSame([
            'page' => [
                'name' => 'Home page',
                'default_locale' => 'en',
                'locales' => ['en', 'es'],
                'sites' => ['main'],
                'extra' => ['template' => 'home'],
                'indexing' => ['robots' => 'index'],
                'versions' => [
                    [
                        'seo' => ['exported' => ['title' => 'Home']],
                        'layout' => 'default',
                        'data' => ['exported' => ['body' => 'Welcome']],
                        'version_number' => 3,
                        'origin' => ContentVersionInterface::ORIGIN_IMPORT,
                        'origin_description' => 'fixture',
                        'note' => 'Initial version',
                        'created_at' => '2026-01-02 03:04:05',
                        'meta' => ['author' => 'cms'],
                    ],
                ],
            ],
        ], $transformer->export($content, $files, $version, 'page'));
    }

    public function testItRejectsInvalidExportArguments(): void
    {
        $this->expectException(InvalidElementException::class);

        $this->createTransformer()->export(new stdClass(), $files, null, 'page');
    }

    public function testItPreloadsAndImportsContent(): void
    {
        $site = $this->createStub(SiteInterface::class);
        $firstVersion = $this->createStub(ContentVersionInterface::class);
        $createdVersion = $this->createMock(ContentVersionInterface::class);
        $createdVersion->expects(self::once())->method('setLayout')->with('default');
        $createdVersion->expects(self::once())->method('setData')->with(['imported' => ['body' => 'Welcome']]);
        $createdVersion->expects(self::once())->method('setSeo')->with(['imported' => ['title' => 'Home']]);

        $content = $this->createMock(ContentInterface::class);
        $content->method('getVersions')->willReturn(new ArrayCollection([$firstVersion]));
        $content->expects(self::once())->method('removeVersion')->with($firstVersion);
        $content->expects(self::once())->method('setName')->with('Home page');
        $content->expects(self::once())->method('addSite')->with($site);
        $content->expects(self::once())->method('setDefaultLocale')->with('en');
        $content->expects(self::once())->method('setLocales')->with(['en', 'es']);
        $content->expects(self::once())->method('setExtraData')->with(['template' => 'home']);
        $content->expects(self::once())->method('setIndexing')->with(['robots' => 'index']);
        $content->expects(self::once())->method('setPublishedVersion')->with($createdVersion);

        $contentManager = $this->createStub(ContentManagerInterface::class);
        $contentManager->method('createEntity')->with('page')->willReturn($content);
        $contentManager->method('createVersion')->with($content, null, ContentVersionInterface::ORIGIN_IMPORT)->willReturn($createdVersion);

        $dataTransformer = $this->createMock(DataTransformer::class);
        $dataTransformer->expects(self::exactly(2))
            ->method('import')
            ->willReturnCallback(fn (?array $data, ReferencesRepository $references, array $options): array => ['imported' => $data]);

        $references = new ReferencesRepository();
        $references->addReference('site___main', $site);
        $data = [
            'page' => [
                'name' => 'Home page',
                'default_locale' => 'en',
                'locales' => ['en', 'es'],
                'sites' => ['main'],
                'extra' => ['template' => 'home'],
                'indexing' => ['robots' => 'index'],
                'versions' => [
                    [
                        'layout' => 'default',
                        'data' => ['body' => 'Welcome'],
                        'seo' => ['title' => 'Home'],
                    ],
                ],
            ],
        ];
        $transformer = $this->createTransformer($contentManager, $dataTransformer);

        $transformer->preload($data, $references);

        self::assertSame($content, $transformer->import($data, $references, [
            'auto_publish_version' => true,
            'version_origin' => ContentVersionInterface::ORIGIN_IMPORT,
        ]));
    }

    public function testItRequiresPreloadBeforeImport(): void
    {
        $this->expectException(RunPreloadBeforeImportException::class);

        $this->createTransformer()->import(['page' => ['name' => 'Missing']], new ReferencesRepository());
    }

    private function createTransformer(?ContentManagerInterface $contentManager = null, ?DataTransformer $dataTransformer = null): PageEntityTransformer
    {
        return new PageEntityTransformer(
            $contentManager ?? $this->createStub(ContentManagerInterface::class),
            $this->createStub(RouteManagerInterface::class),
            $this->createStub(MediaManagerInterface::class),
            $this->createStub(SiteManagerInterface::class),
            $dataTransformer ?? $this->createStub(DataTransformer::class)
        );
    }
}
