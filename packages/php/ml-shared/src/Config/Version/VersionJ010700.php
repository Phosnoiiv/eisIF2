<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010700Proprietary;

class VersionJ010700 extends VersionJ010100 {
    public const VERSION = '1.7.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010700Proprietary);
    }
}
