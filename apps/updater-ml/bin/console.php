<?php
namespace EverISay\SIF\ML\Updater;

use Dotenv\Dotenv;
use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIF\ML\Storage\DownloadStorage;
use League\Container\Argument\ResolvableArgument;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

require __DIR__.'/../vendor/autoload.php';

$env = Dotenv::createImmutable(__DIR__.'/..');
$env->load();

$container = new Container;
$container->add(Dotenv::class, $env);
$container->add(AbstractVersionConfig::class, function() {
    $versionConfigClass = env('VERSION_CONFIG');
    return new $versionConfigClass;
});
$container->add('DownloadStorageFilesystem', fn() => new LocalFilesystemAdapter(env('STORAGE_DOWNLOAD_PATH')));
$container->add(DownloadStorage::class)->addArgument(new ResolvableArgument('DownloadStorageFilesystem'));
$container->delegate(new ReflectionContainer);

$cmdLoader = new ContainerCommandLoader($container, [
    'assetHash' => Command\AssetHashGetCommand::class,
    'du'             => Command\DownloadUpdateCommand::class,
    'downloadUpdate' => Command\DownloadUpdateCommand::class,
]);

$app = new Application;
$app->setCommandLoader($cmdLoader);
$app->run();
