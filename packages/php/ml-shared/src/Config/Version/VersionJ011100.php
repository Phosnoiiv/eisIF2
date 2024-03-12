<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ011100Proprietary;

class VersionJ011100 extends VersionJ010100 {
    public const VERSION = '1.11.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ011100Proprietary);
    }
}
