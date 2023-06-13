<?php
namespace EverISay\SIF\ML\Storage\Manifest;

class BundleManifest {
    public string $identifier;
    public string $name;
    public string $hash;
    public int $crc;
    public int $length;
    /** @var string[] */
    public array $dependencies;
    /** @var string[] */
    public array $labels;
    /** @var string[] */
    public array $assets;
}
