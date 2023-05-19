<?php
namespace EverISay\SIF\ML\Updater;

use Dotenv\Dotenv;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

require __DIR__.'/../vendor/autoload.php';

$env = Dotenv::createImmutable(__DIR__.'/..');
$env->load();

$cmdLoader = new FactoryCommandLoader([
    'assetHash' => fn() => new Command\AssetHashGetCommand($env),
]);

$app = new Application;
$app->setCommandLoader($cmdLoader);
$app->run();
