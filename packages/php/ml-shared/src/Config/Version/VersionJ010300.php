<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010300Proprietary;

class VersionJ010300 extends VersionJ010100 {
    public const VERSION = '1.3.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010300Proprietary);
    }
}
