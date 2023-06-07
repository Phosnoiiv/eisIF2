<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010100Proprietary;

class VersionJ010100 extends VersionJ010000 {
    public const VERSION = '1.1.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010100Proprietary);
    }
}
