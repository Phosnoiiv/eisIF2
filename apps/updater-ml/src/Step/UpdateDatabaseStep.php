<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Proprietary\AssetHelper as ProprietaryAssetHelper;
use EverISay\SIF\ML\Storage\Database\AbstractEntity;
use EverISay\SIF\ML\Storage\DatabaseStorage;
use EverISay\SIF\ML\Storage\Manifest\BundleManifestCollection;
use EverISay\SIF\ML\Storage\Manifest\ManifestName;
use EverISay\SIF\ML\Storage\ManifestStorage;
use EverISay\SIF\ML\Updater\Helper\AssetHelper as UpdaterAssetHelper;
use EverISay\SIF\ML\Updater\Helper\Decoder;
use EverISay\SIF\ML\Updater\Helper\TempFileHelper;
use EverISay\SIF\ML\Updater\TablePropertyNameConverter;
use Symfony\Component\Serializer\Serializer;

final class UpdateDatabaseStep {
    function __construct(
        private readonly DatabaseStorage $databaseStorage,
        private readonly ManifestStorage $manifestStorage,
        private readonly TempFileHelper $tempFileHelper,
        private readonly UpdaterAssetHelper $updaterAssetHelper,
        private readonly ProprietaryAssetHelper $proprietaryAssetHelper,
    ) {
        $this->serializer = $this->manifestStorage->getSerializer(new TablePropertyNameConverter);
    }

    private readonly Serializer $serializer;

    public array $newIds = [];
    public array $changedIds = [];

    public function execute(): void {
        $currentCollection = $this->manifestStorage->loadBundleManifest();
        $previousHash = $this->databaseStorage->readLatestHash();
        if (null !== $previousHash) {
            $previousCollection = $this->manifestStorage->loadBundleManifest($previousHash);
        }
        foreach ($this->databaseStorage::ENTITIES as $assetName => $className) {
            $current = $this->getEntities($currentCollection, $assetName, $className);
            $previous = !empty($previousCollection) ? $this->getEntities($previousCollection, $assetName, $className) : [];
            foreach ($current as $id => $entity) {
                if (!isset($previous[$id])) {
                    // New
                    $this->newIds[$className][] = $id;
                    $this->databaseStorage->storeEntity($entity);
                }
            }
        }
        $this->databaseStorage->save();
        $this->databaseStorage->writeLatestHash($this->manifestStorage->readLatestHash(ManifestName::Bundle));
    }

    private function getEntities(BundleManifestCollection $collection, string $assetName, string $className): array {
        $data = $this->updaterAssetHelper->getBundleAsset($collection, "Mst/$assetName.bytes");
        if (null === $data) return [];
        $data = $this->proprietaryAssetHelper->decryptTable($data, Decoder::getTableKey(...));
        $data = gzdecode($data);
        $data = Decoder::deserializeMemory($data, $this->tempFileHelper);
        $data = $this->serializer->deserialize($data, $className . '[]', 'json');
        return array_combine(array_map(fn(AbstractEntity $x) => $x->getId(), $data), $data);
    }
}
