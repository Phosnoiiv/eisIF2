<?php
namespace EverISay\SIF\Backend\Application;

class Kernel extends \Spiral\Framework\Kernel {
    protected function defineSystemBootloaders(): array {
        return array_merge(parent::SYSTEM, [
            \Spiral\DotEnv\Bootloader\DotenvBootloader::class,
        ]);
    }

    protected function defineBootloaders(): array {
        return [
            \Spiral\RoadRunnerBridge\Bootloader\HttpBootloader::class,
            \Spiral\Nyholm\Bootloader\NyholmBootloader::class,
            \Spiral\Bootloader\Http\RouterBootloader::class,
            \Spiral\Router\Bootloader\AnnotatedRoutesBootloader::class,
            \Spiral\Cache\Bootloader\CacheBootloader::class, // Currently we don't need the RoadRunner bridge bootloader.
            Bootloader\MLPackageBootloader::class,
        ];
    }
}
