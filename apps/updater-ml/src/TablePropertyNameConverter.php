<?php
namespace EverISay\SIF\ML\Updater;

class TablePropertyNameConverter extends \Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter {
    public function normalize(string $propertyName): string {
        return '_' . parent::normalize($propertyName);
    }

    public function denormalize(string $propertyName): string {
        return parent::denormalize(preg_replace('/^_/', '', $propertyName));
    }
}
