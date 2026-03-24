<?php

namespace Softspring\CmsDataPlugin\IO;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Yaml\Yaml;

class StructuredDataStorage
{
    public function __construct(
        protected EncoderInterface $encoder,
        protected DecoderInterface $decoder,
        protected Filesystem $filesystem,
    ) {
    }

    public function saveYaml(array $data, string $filePath): string
    {
        return $this->save($data, $filePath, 'yaml', [
            YamlEncoder::YAML_INLINE => 100,
            YamlEncoder::YAML_INDENTATION => 4,
            YamlEncoder::YAML_FLAGS => Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_NULL_AS_TILDE | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_OBJECT | Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK,
        ]);
    }

    public function loadYamlFile(string $filePath): array
    {
        return $this->loadFile($filePath, 'yaml');
    }

    public function loadYamlString(string $content): array
    {
        return $this->decode($content, 'yaml');
    }

    public function saveJson(array $data, string $filePath): string
    {
        return $this->save($data, $filePath, 'json', [
            JsonEncode::OPTIONS => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
        ]);
    }

    public function loadJsonFile(string $filePath): array
    {
        return $this->loadFile($filePath, 'json');
    }

    public function loadJsonString(string $content): array
    {
        return $this->decode($content, 'json');
    }

    protected function save(array $data, string $filePath, string $format, array $context = []): string
    {
        $this->filesystem->mkdir(\dirname($filePath));
        $this->filesystem->dumpFile($filePath, $this->encoder->encode($data, $format, $context));

        return $filePath;
    }

    protected function loadFile(string $filePath, string $format): array
    {
        return $this->decode(file_get_contents($filePath), $format);
    }

    protected function decode(string $content, string $format): array
    {
        return $this->decoder->decode($content, $format);
    }
}
