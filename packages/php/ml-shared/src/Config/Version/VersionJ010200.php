<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010200Proprietary;

class VersionJ010200 extends VersionJ010100 {
    public const VERSION = '1.2.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010200Proprietary);
    }
}
