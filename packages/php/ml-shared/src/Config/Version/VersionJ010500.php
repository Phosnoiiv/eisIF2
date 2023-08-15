<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010500Proprietary;

class VersionJ010500 extends VersionJ010100 {
    public const VERSION = '1.5.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010500Proprietary);
    }
}
