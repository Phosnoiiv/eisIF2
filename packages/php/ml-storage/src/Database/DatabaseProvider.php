<?php
namespace EverISay\SIF\ML\Storage\Database;

use Cycle\Database\Config\SQLite\FileConnectionConfig;
use Cycle\Database\Config\SQLiteDriverConfig;
use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\SQLite\SQLiteDriver;

final class DatabaseProvider implements \Cycle\Database\DatabaseProviderInterface {
    function __construct(
        private readonly string $localPath,
    ) {}

    public function database(?string $database = null): DatabaseInterface {
        return $this->databases[$database] ??= $this->getDatabase($database);
    }

    private array $databases = [];

    private function getDatabase(?string $database): DatabaseInterface {
        switch ($database) {
            default:
                return $this->getSQLiteDatabase($database, $this->localPath . "/$database.s3db", '+0000');
        }
    }

    private function getSQLiteDatabase(string $name, string $filename, string $timezone = '+0800', bool $readonly = false): Database {
        return new Database($name, '', SQLiteDriver::create(new SQLiteDriverConfig(
            new FileConnectionConfig($filename),
            timezone: $timezone, readonly: $readonly,
        )));
    }
}
