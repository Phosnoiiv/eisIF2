<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010901Proprietary;

class VersionJ010901 extends VersionJ010900 {
    public const VERSION = '1.9.1';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010901Proprietary);
    }
}
