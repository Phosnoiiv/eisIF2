<?php
namespace EverISay\SIF\ML\Storage;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

trait SerializerTrait {
    /**
     * When overridden in an exhibiting class, defines a custom name converter to construct the serializer.
     */
    private function defineSerializerNameConverter(): ?NameConverterInterface {
        return null;
    }

    /**
     * When overridden in an exhibiting class, defines additional normalizers to construct the serializer.
     */
    private function defineSerializerAdditionalNormalizers(): array {
        return [];
    }

    private readonly Serializer $serializer;
    private function getSerializer(): Serializer {
        if (!isset($this->serializer)) {
            $this->serializer = new Serializer(array_merge([new ArrayDenormalizer, new BackedEnumNormalizer, new PropertyNormalizer(
                nameConverter: $this->defineSerializerNameConverter(),
                propertyTypeExtractor: new PropertyInfoExtractor(typeExtractors: [new PhpDocExtractor, new ReflectionExtractor]),
            )], $this->defineSerializerAdditionalNormalizers()), [new JsonEncoder(defaultContext: [
                JsonEncode::OPTIONS => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
            ])]);
        }
        return $this->serializer;
    }

    private function serialize(mixed $data, array $context = []): string {
        return $this->getSerializer()->serialize($data, 'json', $context);
    }

    private function deserialize(mixed $data, string $type, array $context = []): mixed {
        return $this->getSerializer()->deserialize($data, $type, 'json', $context);
    }
}
