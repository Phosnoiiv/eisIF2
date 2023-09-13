<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010600Proprietary;

class VersionJ010600 extends VersionJ010100 {
    public const VERSION = '1.6.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010600Proprietary);
    }
}
