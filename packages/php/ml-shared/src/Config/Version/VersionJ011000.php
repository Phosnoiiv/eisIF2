<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ011000Proprietary;

class VersionJ011000 extends VersionJ010100 {
    public const VERSION = '1.10.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ011000Proprietary);
    }
}
