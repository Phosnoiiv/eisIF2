<?php
namespace EverISay\SIF\ML\Updater;

use Dotenv\Dotenv;
use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIF\ML\Common\Config\AccountConfig;
use EverISay\SIF\ML\Common\Config\DeviceConfig;
use EverISay\SIf\ML\Common\Proprietary\AssetHelperInterface;
use EverISay\SIF\ML\Common\Proprietary\NetworkHelperInterface;
use EverISay\SIF\ML\Proprietary\AssetHelper;
use EverISay\SIF\ML\Proprietary\NetworkHelper;
use EverISay\SIF\ML\Storage\DatabaseStorage;
use EverISay\SIF\ML\Storage\DownloadStorage;
use EverISay\SIF\ML\Storage\InteractionStorage;
use EverISay\SIF\ML\Storage\ManifestStorage;
use EverISay\SIF\ML\Storage\UpdateStorage;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
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
$container->addShared(DeviceConfig::class, function() {
    $config = new DeviceConfig;
    $config->device = env('DEVICE');
    $config->os = env('DEVICE_OS');
    $config->osVersion = env('DEVICE_OS_VERSION');
    return $config;
});
$container->addShared(AccountConfig::class, function() {
    $config = new AccountConfig;
    $config->userId = env('ACCOUNT_ID');
    $config->privateKey = env('ACCOUNT_PRIVATE_KEY');
    return $config;
});
$container->addShared(NetworkHelperInterface::class, fn() => $container->get(NetworkHelper::class));
$container->add(DownloadStorage::class)->addArgument(env('STORAGE_DOWNLOAD_PATH'));
$container->add(ManifestStorage::class)->addArgument(env('STORAGE_MANIFEST_PATH'));
$container->addShared(DatabaseStorage::class)->addArgument(env('STORAGE_DATABASE_PATH'));
$container->addShared(UpdateStorage::class)->addArgument(env('STORAGE_UPDATE_PATH'));
$container->addShared(InteractionStorage::class)->addArgument(env('STORAGE_INTERACTION_PATH'));
$container->add(AssetHelperInterface::class, fn() => $container->get(AssetHelper::class));
$container->delegate(new ReflectionContainer(true));
$container->addShared('logger_updater', function() {
    $logger = new Logger('updater-ml', [
        new StreamHandler(env('LOG_FILE'), Level::Info),
        new ConsoleHandler,
    ]);
    return $logger;
});
$container->inflector(LoggerAwareInterface::class)->invokeMethod('setLogger', ['logger_updater']);

$cmdLoader = new ContainerCommandLoader($container, [
    'assetHash' => Command\AssetHashGetCommand::class,
    'du'             => Command\DownloadUpdateCommand::class,
    'downloadUpdate' => Command\DownloadUpdateCommand::class,
    'ranking' => Command\FetchRankingCommand::class,
    'migrate' => Command\MigrateDatabaseCommand::class,
]);

$app = new Application;
$app->setCommandLoader($cmdLoader);
$app->run();
