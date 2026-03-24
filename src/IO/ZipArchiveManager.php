<?php

namespace Softspring\CmsDataPlugin\IO;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ZipArchive;

class ZipArchiveManager
{
    public function __construct(
        protected StructuredDataStorage $structuredDataStorage,
        protected Filesystem $filesystem,
    ) {
    }

    public function read(string $path, string $zipName): array|false
    {
        $zip = new ZipArchive();
        if (true !== $zip->open("$path/$zipName")) {
            return false;
        }

        $contents = [
            'blocks' => [],
            'contents' => [],
            'media' => [],
            'menus' => [],
            'routes' => [],
        ];

        $extractPath = sys_get_temp_dir().'/cms_data_'.bin2hex(random_bytes(8));
        $this->filesystem->mkdir($extractPath);

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $stat = $zip->statIndex($i);
            if (!isset($stat['name']) || str_ends_with($stat['name'], '/')) {
                continue;
            }

            [$root, $relativePath] = array_pad(explode('/', $stat['name'], 2), 2, null);
            if (!$relativePath) {
                continue;
            }

            $fileName = pathinfo($relativePath, PATHINFO_FILENAME);
            $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
            $content = $zip->getFromIndex($i);

            switch ($root) {
                case 'contents':
                case 'routes':
                case 'blocks':
                case 'menus':
                    $contents[$root][$fileName] = $this->structuredDataStorage->loadYamlString($content);
                    break;

                case 'media':
                    if ('json' === $extension) {
                        $contents['media'][$fileName]['media'] = $this->structuredDataStorage->loadJsonString($content);
                        break;
                    }

                    $targetPath = $extractPath.'/'.$stat['name'];
                    $this->filesystem->mkdir(\dirname($targetPath));
                    $this->filesystem->dumpFile($targetPath, $content);

                    $contents['media'][$fileName]['files'][$stat['name']] = [
                        'name' => basename($relativePath),
                        'path' => $stat['name'],
                        'size' => $stat['size'],
                        'tmpPath' => $targetPath,
                    ];
                    break;
            }
        }

        $zip->close();

        return $contents;
    }

    public function dump(string $path, string $zipName): string
    {
        $zip = new ZipArchive();
        if (true !== $zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new RuntimeException('Zip file could not be created/opened.');
        }

        $finder = new Finder();
        $finder->files()->in($path);

        foreach ($finder as $file) {
            $zip->addFile($file->getRealPath(), str_replace("$path/", '', $file->getRealPath()));
        }

        if (!$zip->close()) {
            throw new RuntimeException('Zip file could not be closed.');
        }

        return $zipName;
    }

    public function dumpResponse(string $path, string $zipName, bool $deleteAfterResponse = true): BinaryFileResponse
    {
        $archivePath = $this->dump($path, $zipName);

        return (new BinaryFileResponse($archivePath))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($archivePath))
            ->deleteFileAfterSend($deleteAfterResponse);
    }
}
