<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010900Proprietary;

class VersionJ010900 extends VersionJ010100 {
    public const VERSION = '1.9.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010900Proprietary);
    }
}
