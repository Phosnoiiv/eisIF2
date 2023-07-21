<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010401Proprietary;

class VersionJ010401 extends VersionJ010400 {
    public const VERSION = '1.4.1';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010401Proprietary);
    }
}
