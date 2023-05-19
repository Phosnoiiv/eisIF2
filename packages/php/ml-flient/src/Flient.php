<?php
namespace EverISay\SIF\ML\Flient;

use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIF\ML\Common\Config\DeviceConfig;
use EverISay\SIF\ML\Common\Proprietary\NetworkHelperInterface;
use EverISay\SIF\ML\Shared\Response\AbstractResponse;
use EverISay\SIF\ML\Shared\Response\AssetHashGetResponse;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Flient {
    function __construct(
        private readonly AbstractVersionConfig $versionConfig,
        private readonly DeviceConfig $deviceConfig,
        private readonly NetworkHelperInterface $networkHelper,
    ) {
        $this->client = HttpClient::create([
            'base_uri' => $versionConfig::API_SERVER,
            'headers' => [
                'Content-Type' => 'application/json',
                'AOHARU-DEVICE' => $deviceConfig->device,
                'AOHARU-PLATFORM' => $deviceConfig->platform,
                'AOHARU-OS' => $deviceConfig->os,
                'AOHARU-OS-VERSION' => $deviceConfig->osVersion,
                'AOHARU-CLIENT-VERSION' => $versionConfig::VERSION,
                'AOHARU-IGNORE-GAMELIB' => '0',
                $versionConfig->proprietaryConfig::HEADER_BINARY_HASH => $versionConfig->proprietaryConfig::BINARY_HASH,
                $versionConfig->proprietaryConfig::HEADER_DEBUGGER_ATTACHED => '0',
                'AOHARU-ASSET-VERSION' => $versionConfig->proprietaryConfig::ASSET_VERSION,
                'AOHARU-ENVIRONMENT' => 'release',
                'RETRY-FLAG' => 'NONE',
                'RETRY-COUNT' => 0,
                'X-Unity-Version' => $versionConfig::UNITY_VERSION,
            ],
        ]);
    }

    private HttpClientInterface $client;
    private int $userId = 0;
    private ?string $assetHash = null;
    private ?string $lastSignature = null;

    private function generateSid(int $timestamp): string {
        return strval($this->userId) . strval($timestamp);
    }

    private const API_START_ASSET_HASH = '/api/start/assetHash';

    /**
     * @template T of AbstractResponse
     * @param class-string<T> $responseClass
     * @return T
     */
    private function request(string $uri, string $responseClass, ?array $data = null): AbstractResponse {
        if ($isPost = isset($data)) {
            $jsonData = json_encode($data);
            $body = $this->networkHelper->encryptRequestBody($jsonData);
        }
        $time = time();
        $signature = $this->networkHelper->signRequest($jsonData ?? '', $isPost, $this->userId, $time);
        $httpResponse = $this->client->request($isPost ? 'POST' : 'GET', $uri, [
            'body' => $body ?? null,
            'headers' => [
                'AOHARU-USER-ID' => $this->userId,
                'AOHARU-SID' => $this->generateSid($time),
                'AOHARU-TIMESTAMP' => $time,
                'AOHARU-IS-AUTHORIZED' => empty($this->userId) ? 'FALSE' : 'TRUE',
                $this->versionConfig->proprietaryConfig::HEADER_LAST_SIGNATURE => $this->lastSignature,
                $this->versionConfig->proprietaryConfig::HEADER_THIS_SIGNATURE => $signature,
                'AOHARU-ASSET-HASH' => $this->assetHash,
            ],
        ]);
        $responseData = $this->networkHelper->decryptResponseBody($httpResponse->getContent());
        $this->lastSignature = $signature;
        $serializer = new Serializer([
            new PropertyNormalizer(null, new CamelCaseToSnakeCaseNameConverter, new ReflectionExtractor),
        ], [new JsonEncoder]);
        return $serializer->deserialize($responseData, $responseClass, 'json');
    }

    public function getAssetHash(): string {
        $response = $this->request(self::API_START_ASSET_HASH, AssetHashGetResponse::class, [
            'asset_version' => $this->versionConfig->proprietaryConfig::ASSET_VERSION,
            'environment' => 'release',
        ]);
        return $this->assetHash = $response->data->assetHash;
    }
}
