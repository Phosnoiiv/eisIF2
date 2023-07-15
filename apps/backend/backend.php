<?php
use EverISay\SIF\Backend\Application\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = Kernel::create([
    'root' => __DIR__,
]);
$app->run();
if (null === $app) exit(255);
exit($app->serve());
