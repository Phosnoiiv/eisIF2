<?php
namespace EverISay\SIF\ML\Updater;

use Dotenv\Dotenv;
use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIf\ML\Common\Proprietary\AssetHelperInterface;
use EverISay\SIF\ML\Proprietary\AssetHelper;
use EverISay\SIF\ML\Storage\DatabaseStorage;
use EverISay\SIF\ML\Storage\DownloadStorage;
use EverISay\SIF\ML\Storage\ManifestStorage;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

require __DIR__.'/../vendor/autoload.php';

$env = Dotenv::createImmutable(__DIR__.'/..');
$env->load();

$container = new Container;
$container->add(Dotenv::class, $env);
$container->addShared(\DateTimeInterface::class, new \DateTimeImmutable);
$container->add(AbstractVersionConfig::class, function() {
    $versionConfigClass = env('VERSION_CONFIG');
    return new $versionConfigClass;
});
$container->add(DownloadStorage::class)->addArgument(env('STORAGE_DOWNLOAD_PATH'));
$container->add(ManifestStorage::class)->addArgument(env('STORAGE_MANIFEST_PATH'));
$container->addShared(DatabaseStorage::class)->addArgument(env('STORAGE_DATABASE_PATH'));
$container->add(AssetHelperInterface::class, fn() => $container->get(AssetHelper::class));
$container->delegate(new ReflectionContainer(true));
$container->add('logger_updater', function() {
    $logger = new Logger('updater-ml', [
        new StreamHandler(env('LOG_FILE')),
        new ConsoleHandler,
    ]);
    return $logger;
});
$container->inflector(LoggerAwareInterface::class)->invokeMethod('setLogger', ['logger_updater']);

$cmdLoader = new ContainerCommandLoader($container, [
    'assetHash' => Command\AssetHashGetCommand::class,
    'du'             => Command\DownloadUpdateCommand::class,
    'downloadUpdate' => Command\DownloadUpdateCommand::class,
    'migrate' => Command\MigrateDatabaseCommand::class,
]);

$app = new Application;
$app->setCommandLoader($cmdLoader);
$app->run();
