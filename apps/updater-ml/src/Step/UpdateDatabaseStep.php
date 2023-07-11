<?php
namespace EverISay\SIF\ML\Updater\Step;

use EverISay\SIF\ML\Proprietary\AssetHelper as ProprietaryAssetHelper;
use EverISay\SIF\ML\Storage\Database\AbstractEntity;
use EverISay\SIF\ML\Storage\Database\ReleaseLabel;
use EverISay\SIF\ML\Storage\DatabaseStorage;
use EverISay\SIF\ML\Storage\Manifest\BundleManifestCollection;
use EverISay\SIF\ML\Storage\Manifest\ManifestName;
use EverISay\SIF\ML\Storage\ManifestStorage;
use EverISay\SIF\ML\Updater\DateTimeNormalizer;
use EverISay\SIF\ML\Updater\Helper\AssetHelper as UpdaterAssetHelper;
use EverISay\SIF\ML\Updater\Helper\Decoder;
use EverISay\SIF\ML\Updater\Helper\TempFileHelper;
use EverISay\SIF\ML\Updater\LoggerAwareTrait;
use EverISay\SIF\ML\Updater\TablePropertyNameConverter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Serializer\Serializer;

final class UpdateDatabaseStep implements LoggerAwareInterface {
    use LoggerAwareTrait;

    function __construct(
        private readonly DatabaseStorage $databaseStorage,
        private readonly ManifestStorage $manifestStorage,
        private readonly TempFileHelper $tempFileHelper,
        private readonly UpdaterAssetHelper $updaterAssetHelper,
        private readonly ProprietaryAssetHelper $proprietaryAssetHelper,
    ) {
        $this->serializer = $this->manifestStorage->getSerializer(new TablePropertyNameConverter, [new DateTimeNormalizer]);
    }

    private readonly Serializer $serializer;

    public array $newIds = [];
    public array $changedIds = [];

    private readonly \DateTimeInterface $time;
    private readonly int $timestamp;

    public function execute(\DateTimeInterface $time): void {
        $this->time = $time;
        $this->timestamp = $time->getTimestamp();
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
                    switch ($this->getReleaseLabelStatus($entity)) {
                        case 1:
                            $this->acceptNew($id, $entity, self::NEW_UNOPENED);
                            break;
                        case 0:
                            $this->acceptNew($id, $entity, self::NEW_AVAILABLE);
                            break;
                        case -1:
                            $this->acceptNew($id, $entity, self::NEW_CLOSED);
                            break;
                    }
                } else if ($entity != $previous[$id]) {
                    // Changed
                    $previousEntity = $previous[$id];
                    switch ($this->getReleaseLabelStatus($previousEntity) * 10 + $this->getReleaseLabelStatus($entity)) {
                        case 11:
                            $this->acceptChanged($id, $entity, self::CHANGED_UNOPENED_TO_UNOPENED);
                            break;
                        case 10:
                            $this->acceptChanged($id, $entity, self::CHANGED_UNOPENED_TO_AVAILABLE);
                            break;
                        case 9:
                            $this->acceptChanged($id, $entity, self::CHANGED_UNOPENED_TO_CLOSED);
                            break;
                        case 1:
                            $this->acceptChanged($id, $entity, self::CHANGED_AVAILABLE_TO_UNOPENED);
                            break;
                        case 0:
                            $this->acceptChanged($id, $entity, self::CHANGED_AVAILABLE_TO_AVAILABLE);
                            break;
                        case -1:
                            $this->acceptChanged($id, $entity, self::CHANGED_AVAILABLE_TO_CLOSED);
                            break;
                        case -9:
                            $this->acceptChanged($id, $entity, self::CHANGED_CLOSED_TO_UNOPENED);
                            break;
                        case -10:
                            $this->acceptChanged($id, $entity, self::CHANGED_CLOSED_TO_AVAILABLE);
                            break;
                        case -11:
                            $this->reject($id, $entity, self::CHANGED_CLOSED_TO_CLOSED);
                            break;
                    }
                }
            }
            $this->flushLog();
        }
        $this->databaseStorage->save();
        $this->databaseStorage->writeLatestHash($this->manifestStorage->readLatestHash(ManifestName::Bundle));
    }

    /**
     * @return AbstractEntity[]
     */
    private function getEntities(BundleManifestCollection $collection, string $assetName, string $className): array {
        $data = $this->updaterAssetHelper->getBundleAsset($collection, "Mst/$assetName.bytes");
        if (null === $data) return [];
        $data = $this->proprietaryAssetHelper->decryptTable($data, Decoder::getTableKey(...));
        $data = gzdecode($data);
        $data = Decoder::deserializeMemory($data, $this->tempFileHelper);
        $data = $this->serializer->deserialize($data, $className . '[]', 'json');
        return array_combine(array_map(fn(AbstractEntity $x) => $x->getId(), $data), $data);
    }

    private array $releaseLabelStatus = [];
    private array $releaseLabelOpenTimestamps = [];
    private array $releaseLabelCloseTimestamps = [];

    private function getReleaseLabelStatus(AbstractEntity $entity): int {
        $releaseLabelId = $entity->getReleaseLabelId();
        if (!isset($this->releaseLabelStatus[$releaseLabelId])) {
            $releaseLabel = $entity instanceof ReleaseLabel ? $entity
                : $this->databaseStorage->getEntityById(ReleaseLabel::class, $releaseLabelId);
            $this->releaseLabelOpenTimestamps[$releaseLabelId] = $openTimestamp = $releaseLabel->openedAt?->getTimestamp();
            $this->releaseLabelCloseTimestamps[$releaseLabelId] = $closeTimestamp = $releaseLabel->closedAt?->getTimestamp();
            $this->releaseLabelStatus[$releaseLabelId] = match (true) {
                !empty($closeTimestamp) && $closeTimestamp < $this->timestamp => -1,
                !empty($openTimestamp) && $openTimestamp > $this->timestamp => 1,
                default => 0,
            };
        }
        return $this->releaseLabelStatus[$releaseLabelId];
    }

    private const NEW_UNOPENED = 0;
    private const NEW_AVAILABLE = 1;
    private const NEW_CLOSED = 2;
    private const CHANGED_UNOPENED_TO_UNOPENED = 3;
    private const CHANGED_UNOPENED_TO_AVAILABLE = 4;
    private const CHANGED_UNOPENED_TO_CLOSED = 5;
    private const CHANGED_AVAILABLE_TO_UNOPENED = 6;
    private const CHANGED_AVAILABLE_TO_AVAILABLE = 7;
    private const CHANGED_AVAILABLE_TO_CLOSED = 8;
    private const CHANGED_CLOSED_TO_UNOPENED = 9;
    private const CHANGED_CLOSED_TO_AVAILABLE = 10;
    private const CHANGED_CLOSED_TO_CLOSED = 11;

    private const LOG_DESCRIPTIONS = [
        self::NEW_UNOPENED                   => 'New %1$s %2$s (%3$s to %4$s)',
        self::NEW_AVAILABLE                  => 'New %1$s %2$s already available since %3$s',
        self::NEW_CLOSED                     => 'New %1$s %2$s already closed since %4$s',
        self::CHANGED_UNOPENED_TO_UNOPENED   => 'Unopened %1$s %2$s (%3$s to %4$s) changed',
        self::CHANGED_UNOPENED_TO_AVAILABLE  => 'Previously unopened %1$s %2$s changed and became available since %3$s',
        self::CHANGED_UNOPENED_TO_CLOSED     => 'Previously unopened %1$s %2$s changed and became closed since %4$s',
        self::CHANGED_AVAILABLE_TO_UNOPENED  => 'Previously available %1$s %2$s changed and became unopened (%3$s to %4$s)',
        self::CHANGED_AVAILABLE_TO_AVAILABLE => '%1$s %2$s (%3$s to %4$s) changed',
        self::CHANGED_AVAILABLE_TO_CLOSED    => 'Previously available %1$s %2$s changed and became closed since %4$s',
        self::CHANGED_CLOSED_TO_UNOPENED     => 'Previously closed %1$s %2$s changed and became unopened (%3$s to %4$s)',
        self::CHANGED_CLOSED_TO_AVAILABLE    => 'Previously closed %1$s %2$s changed and became available (%3$s to %4$s)',
        self::CHANGED_CLOSED_TO_CLOSED       => 'REJECTED to change %1$s %2$s previously closed since %4$s',
    ];
    private const LOG_LEVELS = [
        self::NEW_UNOPENED                   => LogLevel::INFO,
        self::NEW_AVAILABLE                  => LogLevel::NOTICE,
        self::NEW_CLOSED                     => LogLevel::WARNING,
        self::CHANGED_UNOPENED_TO_UNOPENED   => LogLevel::INFO,
        self::CHANGED_UNOPENED_TO_AVAILABLE  => LogLevel::NOTICE,
        self::CHANGED_UNOPENED_TO_CLOSED     => LogLevel::WARNING,
        self::CHANGED_AVAILABLE_TO_UNOPENED  => LogLevel::WARNING,
        self::CHANGED_AVAILABLE_TO_AVAILABLE => LogLevel::NOTICE,
        self::CHANGED_AVAILABLE_TO_CLOSED    => LogLevel::WARNING,
        self::CHANGED_CLOSED_TO_UNOPENED     => LogLevel::WARNING,
        self::CHANGED_CLOSED_TO_AVAILABLE    => LogLevel::WARNING,
        self::CHANGED_CLOSED_TO_CLOSED       => LogLevel::WARNING,
    ];

    private array $logIds = [];

    private function accept(int|string $id, AbstractEntity $entity, int $logType): void {
        $this->databaseStorage->storeEntity($entity);
        $this->reject($id, $entity, $logType);
    }

    private function acceptNew(int|string $id, AbstractEntity $entity, int $logType): void {
        $this->newIds[$entity::class][] = $id;
        $this->accept($id, $entity, $logType);
    }

    private function acceptChanged(int|string $id, AbstractEntity $entity, int $logType): void {
        $oldEntity = $this->databaseStorage->getEntityById($entity::class, is_int($id) ? $id : explode('_', $id));
        foreach ((new \ReflectionClass($entity))->getProperties() as $reflectionProperty) {
            $reflectionProperty->setValue($oldEntity, $reflectionProperty->getValue($entity));
        }
        $this->changedIds[$entity::class][] = $id;
        $this->accept($id, $oldEntity, $logType);
    }

    private function reject(int|string $id, AbstractEntity $entity, int $logType): void {
        $this->logIds[str_replace(' Cycle ORM Proxy', '', $entity::class)][$logType][$entity->getReleaseLabelId()][] = $id;
    }

    private function flushLog(): void {
        foreach ($this->logIds as $className => $logTypes) {
            $shortName = substr(strrchr($className, '\\'), 1);
            foreach ($logTypes as $logType => $releaseLabelIds) {
                $logLevel = self::LOG_LEVELS[$logType];
                foreach ($releaseLabelIds as $releaseLabelId => $entityIds) {
                    $idDescription = implode(', ', array_slice($entityIds, 0, 5));
                    if (0 < ($remaining = count($entityIds) - 5)) {
                        $idDescription .= " and $remaining more";
                    }
                    $this->logger->log($logLevel, sprintf(self::LOG_DESCRIPTIONS[$logType],
                        $shortName, $idDescription,
                        $this->sprintDate($this->releaseLabelOpenTimestamps[$releaseLabelId]),
                        $this->sprintDate($this->releaseLabelCloseTimestamps[$releaseLabelId]),
                    ));
                }
            }
        }
        $this->logIds = [];
    }

    private function sprintDate(?int $timestamp): string {
        if (null === $timestamp) return 'N/A';
        return date(date('Y', $timestamp) == date('Y') ? 'n/j G:i' : 'Y/m/d H:i', $timestamp);
    }
}
