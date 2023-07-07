<?php
namespace EverISay\SIF\ML\Updater\Helper;

use Symfony\Component\Process\Process;

final class Decoder {
    private static function create(array $arguments): Process {
        array_unshift($arguments, env('BIN_DECODER'));
        $decoder = new Process($arguments);
        $decoder->mustRun();
        return $decoder;
    }

    public static function deserialize(string $pathFrom, string $pathTo): void {
        self::create(['deserialize', $pathFrom, $pathTo]);
    }

    public static function deserializeMemory(string $data, TempFileHelper $helper): string {
        $pathFrom = $helper->getPath('decoder-deserialize-' . rand(100000, 999999) . '.bin');
        file_put_contents($pathFrom, $data);
        $pathTo = $helper->getPath('decoder-deserialize-' . rand(100000, 999999) . '.json');
        self::deserialize($pathFrom, $pathTo);
        return file_get_contents($pathTo);
    }

    public static function getTableKey(string $password, string $salt): string {
        $decoder = self::create(['tableKey', $password, base64_encode($salt)]);
        return base64_decode($decoder->getOutput());
    }
}
