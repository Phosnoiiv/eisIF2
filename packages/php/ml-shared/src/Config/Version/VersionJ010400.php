<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010400Proprietary;

class VersionJ010400 extends VersionJ010100 {
    public const VERSION = '1.4.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010400Proprietary);
    }
}
