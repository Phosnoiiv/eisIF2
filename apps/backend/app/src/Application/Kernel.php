<?php
namespace EverISay\SIF\Backend\Application;

class Kernel extends \Spiral\Framework\Kernel {
    protected function defineBootloaders(): array {
        return [
            \Spiral\RoadRunnerBridge\Bootloader\HttpBootloader::class,
            \Spiral\Nyholm\Bootloader\NyholmBootloader::class,
            \Spiral\Bootloader\Http\RouterBootloader::class,
            \Spiral\Router\Bootloader\AnnotatedRoutesBootloader::class,
        ];
    }
}
