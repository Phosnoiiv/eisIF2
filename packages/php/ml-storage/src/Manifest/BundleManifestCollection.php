<?php
namespace EverISay\SIF\ML\Storage\Manifest;

class BundleManifestCollection extends AbstractManifestCollection {
    public function getName(): ManifestName {
        return ManifestName::Bundle;
    }

    /** @var BundleManifest[] */
    public array $manifestCollection;
}
