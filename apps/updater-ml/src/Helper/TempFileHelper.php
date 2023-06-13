<?php
namespace EverISay\SIF\ML\Updater\Helper;

use Spatie\TemporaryDirectory\TemporaryDirectory;

final class TempFileHelper {
    private ?TemporaryDirectory $dir = null;

    function __destruct() {
        $this->dir?->delete();
    }

    private function getBacking(): TemporaryDirectory {
        return $this->dir ??= (new TemporaryDirectory)->name('eisIF2-updater-ml-' . time() . '-' . rand(100000, 999999))->create();
    }

    public function getPath(string $filename = ''): string {
        return $this->getBacking()->path($filename);
    }
}
