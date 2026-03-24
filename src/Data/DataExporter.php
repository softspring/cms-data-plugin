<?php

namespace Softspring\CmsDataPlugin\Data;

use Exception;
use Google\Cloud\Storage\StorageClient;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Utils\Slugger;
use Softspring\CmsDataPlugin\Data\EntityTransformer\ContentEntityTransformerInterface;
use Softspring\CmsDataPlugin\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsDataPlugin\IO\StructuredDataStorage;
use Symfony\Component\Filesystem\Filesystem;

class DataExporter extends AbstractDataImportExport
{
    protected ReferencesRepository $referenceRepository;

    /**
     * @param EntityTransformerInterface[] $entityTransformers
     */
    public function __construct(
        iterable $entityTransformers,
        protected StructuredDataStorage $structuredDataStorage,
        protected Filesystem $filesystem,
    ) {
        parent::__construct($entityTransformers);
        $this->referenceRepository = new ReferencesRepository();
    }

    public function export(array $contents, array $options = []): void
    {
    }

    public function exportRoute(RouteInterface $route, string $path, array $options = []): string
    {
        $exportFile = $this->structuredDataStorage->saveYaml($this->getDataTransformer('routes', $route)->export($route), "$path/routes/{$route->getId()}.yaml");

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" route to %s', $route->getId(), $exportFile));

        return $exportFile;
    }

    public function exportMenu(MenuInterface $menu, string $path, array $options = []): string
    {
        $exportFile = $this->structuredDataStorage->saveYaml($this->getDataTransformer('menus', $menu)->export($menu), "$path/menus/".Slugger::lowerSlug($menu->getName()).'.yaml');

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" menu to %s', $menu->getId(), $exportFile));

        return $exportFile;
    }

    public function exportBlock(BlockInterface $block, string $path, array $options = []): string
    {
        $exportFile = $this->structuredDataStorage->saveYaml($this->getDataTransformer('blocks', $block)->export($block), "$path/blocks/".Slugger::lowerSlug($block->getName()).'.yaml');

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" block to %s', $block->getId(), $exportFile));

        return $exportFile;
    }

    public function exportContent(ContentInterface $content, ?ContentVersionInterface $contentVersion, array $contentTypeConfig, string $path, array $options = []): string
    {
        $contentType = $contentTypeConfig['_id'];

        $file = "$path/contents/".Slugger::lowerSlug($content->getName()).'.yaml';
        $files = [];
        /** @var ContentEntityTransformerInterface $transformer */
        $transformer = $this->getDataTransformer($contentType, $content);
        $filePath = $this->structuredDataStorage->saveYaml($transformer->export($content, $files, $contentVersion, $contentType), $file);

        foreach ($content->getRoutes() as $route) {
            $parentRoute = $route->getParent();
            while ($parentRoute) {
                self::exportRoute($parentRoute, $path);
                $parentRoute = $parentRoute->getParent();
            }
            self::exportRoute($route, $path);
        }

        foreach ($files as $fileName => $fileData) {
            switch ($fileData['@type']) {
                case 'json':
                    $this->structuredDataStorage->saveJson($fileData['json'], "$path/$fileName");
                    break;

                case 'file':
                    switch ($fileData['@location']) {
                        case 'gcs':
                            $storageClient = new StorageClient();
                            $this->filesystem->mkdir(\dirname("$path/$fileName"));
                            $storageClient->bucket($fileData['bucket'])->object($fileData['object'])->downloadToFile("$path/$fileName");
                            break;

                        case 'sfs-media-filesystem':
                            $this->filesystem->mkdir(\dirname("$path/$fileName"));
                            $this->filesystem->copy($fileData['path'].'/'.$fileData['object'], "$path/$fileName", true);
                            break;

                        default:
                            throw new Exception('Not yet implemented');
                    }
                    break;

                default:
                    throw new Exception('Not yet implemented');
            }
        }

        ($options['output'] ?? false) && $options['output']->writeln(sprintf('Exported "%s" %s content to %s', $content->getName(), $contentType, $filePath));

        return $filePath;
    }
}
