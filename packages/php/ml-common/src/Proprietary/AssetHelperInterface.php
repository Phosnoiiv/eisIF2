<?php
namespace EverISay\SIf\ML\Common\Proprietary;

interface AssetHelperInterface {
    public function decryptManifest(string $data): string;
}
