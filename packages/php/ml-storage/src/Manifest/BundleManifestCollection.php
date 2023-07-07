<?php
namespace EverISay\SIF\ML\Storage\Manifest;

class BundleManifestCollection extends AbstractManifestCollection {
    public function getName(): ManifestName {
        return ManifestName::Bundle;
    }

    /** @var BundleManifest[] */
    public array $manifestCollection;

    /** @var BundleManifest[] */
    private array $recentSearchedManifests = [];

    public function findManifestByAssetName(string $assetName): ?BundleManifest {
        foreach ($this->recentSearchedManifests as $manifest) {
            if (in_array($assetName, $manifest->assets)) return $manifest;
        }
        foreach ($this->manifestCollection as $manifest) {
            if (!in_array($assetName, $manifest->assets)) continue;
            array_unshift($this->recentSearchedManifests, $manifest);
            if (10 < count($this->recentSearchedManifests)) {
                array_pop($this->recentSearchedManifests);
            }
            return $manifest;
        }
        return null;
    }
}
