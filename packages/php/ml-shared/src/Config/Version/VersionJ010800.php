<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010800Proprietary;

class VersionJ010800 extends VersionJ010100 {
    public const VERSION = '1.8.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010800Proprietary);
    }
}
