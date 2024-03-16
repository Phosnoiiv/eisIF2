<?php
namespace EverISay\SIF\ML\Flient;

use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIF\ML\Common\Config\AccountConfig;
use EverISay\SIF\ML\Common\Config\DeviceConfig;
use EverISay\SIF\ML\Common\Proprietary\NetworkHelperInterface;
use EverISay\SIF\ML\Shared\Enum\RankingGroupType;
use EverISay\SIF\ML\Shared\Enum\RankingType;
use EverISay\SIF\ML\Shared\Response\AbstractResponse;
use EverISay\SIF\ML\Shared\Response\AssetHashGetResponse;
use EverISay\SIF\ML\Shared\Response\Data\Element\AllUserClearRate;
use EverISay\SIF\ML\Shared\Response\Data\Element\RankingDetail;
use EverISay\SIF\ML\Shared\Response\EventRankingGetResponse;
use EverISay\SIF\ML\Shared\Response\LiveClearRateResponse;
use EverISay\SIF\ML\Shared\Response\StartResponse;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Flient implements LoggerAwareInterface {
    use LoggerAwareTrait;

    function __construct(
        private readonly AbstractVersionConfig $versionConfig,
        private readonly DeviceConfig $deviceConfig,
        private readonly AccountConfig $accountConfig,
        private readonly NetworkHelperInterface $networkHelper,
    ) {
        $this->client = HttpClient::create([
            'base_uri' => $versionConfig::API_SERVER,
            'headers' => [
                'User-Agent' => $versionConfig->proprietaryConfig::USER_AGENT,
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
    private const API_START = '/api/start';
    private const API_EVENT_RANKING = '/api/event/ranking';
    private const API_LIVE_CLEAR_RATE = '/api/live/clearRate';

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
        try {
            $responseData = $this->networkHelper->decryptResponseBody($httpResponse->getContent());
        } catch (HttpExceptionInterface $ex) {
            $this->logger?->error($ex->getMessage());
            $this->logger?->error($uri . ' ' . $httpResponse->getContent(false));
            throw $ex;
        }
        $this->logger?->debug($uri . ' ' . $responseData);
        $this->lastSignature = $signature;
        $serializer = new Serializer([new ArrayDenormalizer, new PropertyNormalizer(
            nameConverter: new CamelCaseToSnakeCaseNameConverter,
            propertyTypeExtractor: new PropertyInfoExtractor(typeExtractors: [new PhpDocExtractor, new ReflectionExtractor])
        )], [new JsonEncoder]);
        return $serializer->deserialize($responseData, $responseClass, 'json');
    }

    private function prepareStartRequestData(): array {
        return [
            'asset_version' => $this->versionConfig->proprietaryConfig::ASSET_VERSION,
            'environment' => 'release',
        ];
    }

    public function getAssetHash(): string {
        $response = $this->request(self::API_START_ASSET_HASH, AssetHashGetResponse::class, $this->prepareStartRequestData());
        return $this->assetHash = $response->data->assetHash;
    }

    private function login(): void {
        if (empty($this->assetHash)) {
            $this->getAssetHash();
        }
        $this->userId = $this->accountConfig->userId;
        $this->request(self::API_START, StartResponse::class, $this->prepareStartRequestData());
    }

    private function ensureLogin(): void {
        if (empty($this->userId)) {
            $this->login();
        }
    }

    public const SIZE_EVENT_RANKING = 98;

    /**
     * @return RankingDetail[]
     */
    public function getEventRanking(int $eventId, RankingType $rankingType, int $startRank = 1, int $groupId = 0): array {
        $this->ensureLogin();
        $response = $this->request(self::API_EVENT_RANKING, EventRankingGetResponse::class, [
            'master_event_id' => $eventId,
            'ranking_type' => $rankingType->value,
            'ranking_group_type' => empty($groupId) ? RankingGroupType::All->value : RankingGroupType::Group->value,
            'user_id' => 0,
            'start_rank' => $startRank,
            'count' => self::SIZE_EVENT_RANKING,
            'group_id' => $groupId,
        ]);
        return $response->data->rankingDetailList;
    }

    /**
     * @return AllUserClearRate[]
     */
    public function getLiveClearRate(): array {
        $this->ensureLogin();
        $response = $this->request(self::API_LIVE_CLEAR_RATE, LiveClearRateResponse::class);
        return $response->data->allUserClearRate;
    }
}
