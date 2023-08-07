<?php
namespace EverISay\SIF\ML\Storage;

use Cycle\Annotated\Entities;
use Cycle\ORM\EntityManager;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\SyncTables;
use Cycle\Schema\Registry;
use EverISay\SIF\ML\Storage\Database\AbstractEntity;
use EverISay\SIF\ML\Storage\Database\DatabaseProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

final class DatabaseStorage {
    use SimpleStorageTrait;

    function __construct(
        private readonly string $localPath,
    ) {
        $localAdapter = new LocalFilesystemAdapter($localPath);
        $this->localFiles = new Filesystem($localAdapter);
        $this->databaseProvider = new DatabaseProvider($this->localPath);
    }

    private readonly Filesystem $localFiles;
    private readonly DatabaseProvider $databaseProvider;

    private function getSimpleFilesystem(): Filesystem {
        return $this->localFiles;
    }

    public const ENTITIES = [
        'release_label' => Database\ReleaseLabel::class,
        'music'       => Database\Music\Music::class,
        'music_level' => Database\Music\MusicLevel::class,
        'live'        => Database\Music\Live::class,
    ];

    private const PATH_LATEST_HASH = 'latest.txt';
    private const PATH_SCHEMA = 'schema.txt';

    public function readLatestHash(): ?string {
        return $this->readSimpleStorage(self::PATH_LATEST_HASH);
    }

    public function writeLatestHash(string $assetHash): void {
        $this->writeSimpleStorage(self::PATH_LATEST_HASH, $assetHash);
    }

    private readonly ORM $orm;
    private readonly EntityManager $manager;

    private function getORM(): ORM {
        if (!isset($this->orm)) $this->createORM();
        return $this->orm;
    }

    private function getManager(): EntityManager {
        if (!isset($this->orm)) $this->createORM();
        return $this->manager;
    }

    private function createORM(): void {
        $schema = $this->readSimpleStorage(self::PATH_SCHEMA);
        if (null === $schema) {
            $schema = $this->compileSchema();
            $this->writeSimpleStorage(self::PATH_SCHEMA, $schema);
        }
        $this->orm = new ORM(new Factory($this->databaseProvider), new Schema($schema));
        $this->manager = new EntityManager($this->orm);
    }

    private function compileSchema(bool $sync = false): array {
        $compiler = new Compiler;
        return $compiler->compile(new Registry($this->databaseProvider), array_merge([
            new Entities(new ClassLocator((new Finder)->files()->in(__DIR__ . '/Database')), null, Entities::TABLE_NAMING_NONE),
            new RenderTables,
        ], $sync ? [new SyncTables] : [], [
            new GenerateTypecast,
        ]));
    }

    public function syncSchema(): void {
        $this->compileSchema(true);
    }

    /**
     * @template T of AbstractEntity
     * @param class-string<T> $className
     * @return T|null
     */
    public function getEntityById(string $className, mixed $id): ?AbstractEntity {
        return $this->getORM()->getRepository($className)->findByPK($id);
    }

    /**
     * @template T of AbstractEntity
     * @param class-string<T> $className
     * @return T|null
     */
    public function searchEntity(string $className, array $scope): ?AbstractEntity {
        return $this->getORM()->getRepository($className)->findOne($scope);
    }

    /**
     * @template T of AbstractEntity
     * @param class-string<T> $className
     * @return T[]
     */
    public function searchEntities(string $className, array $scope): mixed {
        return $this->getORM()->getRepository($className)->findAll($scope);
    }

    public function storeEntity(AbstractEntity $entity): void {
        $this->getManager()->persist($entity);
    }

    public function removeEntityById(string $className, mixed $id): void {
        $this->getManager()->delete($this->getEntityById($className, $id));
    }

    public function save(): void {
        $this->getManager()->run();
    }
}
