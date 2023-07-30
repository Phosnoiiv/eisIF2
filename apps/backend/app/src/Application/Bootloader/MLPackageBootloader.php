<?php
namespace EverISay\SIF\Backend\Application\Bootloader;

use EverISay\SIF\ML\Storage\UpdateStorage;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;

final class MLPackageBootloader extends \Spiral\Boot\Bootloader\Bootloader {
    public function boot(Container $container, EnvironmentInterface $env): void {
        $container->bindSingleton(UpdateStorage::class, fn() => new UpdateStorage($env->get('STORAGE_UPDATE_PATH')));
    }
}
