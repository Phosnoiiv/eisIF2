<?php
namespace EverISay\SIF\ML\Common\Config;

abstract class AbstractVersionProprietaryConfig {
    public const NETWORK_PACKER_KEY = '';
    public const NETWORK_SIGNATURE_KEY = '';
    public const MANIFEST_KEY = '';
    public const MANIFEST_IV = '';
    public const TABLE_PASSWORD = '';

    public const HEADER_LAST_SIGNATURE = '';
    public const HEADER_THIS_SIGNATURE = '';
    public const HEADER_BINARY_HASH = '';
    public const HEADER_DEBUGGER_ATTACHED = '';

    public const ASSET_VERSION = '';
    public const ASSET_MANIFEST_NAME = '';

    public const BINARY_HASH = '';
    public const USER_AGENT = '';
}
