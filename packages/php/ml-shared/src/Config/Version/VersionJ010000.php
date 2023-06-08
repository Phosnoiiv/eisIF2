<?php
namespace EverISay\SIF\ML\Shared\Config\Version;

use EverISay\SIF\ML\Proprietary\Config\VersionJ010000Proprietary;

class VersionJ010000 extends \EverISay\SIF\ML\Common\Config\AbstractVersionConfig {
    public const VERSION = '1.0.0';

    function __construct() {
        $this->setProprietaryConfig(new VersionJ010000Proprietary);
    }

    public const API_SERVER = 'https://api.app.lovelive-sif2.bushimo.jp/';
    public const CDN_SERVER = 'https://lovelive-schoolidolfestival2-assets.akamaized.net/';

    public const UNITY_VERSION = '2021.3.14f1';
}
