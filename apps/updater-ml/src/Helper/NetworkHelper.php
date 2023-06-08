<?php
namespace EverISay\SIF\ML\Updater\Helper;

use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIF\ML\Common\Config\DeviceConfig;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NetworkHelper {
    function __construct(AbstractVersionConfig $versionConfig, DeviceConfig|string $platform = 'Android') {
        $this->client = HttpClient::create([
            'base_uri' => $versionConfig::CDN_SERVER,
            'headers' => [
                'User-Agent' => $versionConfig->proprietaryConfig::USER_AGENT,
                'X-Unity-Version' => $versionConfig::UNITY_VERSION,
            ],
        ]);
        $this->platform = is_string($platform) ? $platform : $platform->platform;
    }

    private HttpClientInterface $client;
    private readonly string $platform;

    private function get(string $uri): string {
        $response = $this->client->request('GET', $uri);
        return $response->getContent();
    }

    private function getAsset(string $name, string $hash, string $extension): string {
        return $this->get("{$this->platform}/$hash/$name.$extension");
    }

    public function getBundle(string $name, string $hash): string {
        return $this->getAsset($name, $hash, 'unity3d');
    }
}
