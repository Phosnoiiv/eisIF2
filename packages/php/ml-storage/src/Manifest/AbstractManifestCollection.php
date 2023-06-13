<?php
namespace EverISay\SIF\ML\Storage\Manifest;

abstract class AbstractManifestCollection {
    abstract public function getName(): ManifestName;
}
