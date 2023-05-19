<?php
namespace EverISay\SIF\ML\Common\Config;

abstract class AbstractVersionConfig {
    public const VERSION = '';

    public readonly AbstractVersionProprietaryConfig $proprietaryConfig;
    protected function setProprietaryConfig(AbstractVersionProprietaryConfig $proprietaryConfig): void {
        $this->proprietaryConfig = $proprietaryConfig;
    }

    public const API_SERVER = '';

    public const UNITY_VERSION = '';
}
