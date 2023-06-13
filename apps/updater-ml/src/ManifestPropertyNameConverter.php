<?php
namespace EverISay\SIF\ML\Updater;

class ManifestPropertyNameConverter extends \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter {
    public function normalize(string $propertyName): string {
        return 'm_' . parent::normalize($propertyName);
    }

    public function denormalize(string $propertyName): string {
        return parent::denormalize(preg_replace('/^m_/', '', $propertyName));
    }
}
