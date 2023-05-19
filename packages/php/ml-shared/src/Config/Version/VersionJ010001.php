<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010001Proprietary;

class VersionJ010001 extends VersionJ010000 {
    public const VERSION = '1.0.1';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010001Proprietary);
    }
}
