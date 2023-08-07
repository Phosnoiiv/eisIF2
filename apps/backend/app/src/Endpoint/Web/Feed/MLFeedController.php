<?php
namespace EverISay\SIF\Backend\Endpoint\Web\Feed;

use EverISay\SIF\Backend\Domain\Feed\UpdateItem;
use EverISay\SIF\ML\Storage\Database\Music\LiveLevel;
use EverISay\SIF\ML\Storage\Update\UpdateInfo;
use EverISay\SIF\ML\Storage\Update\UpdateNews;
use EverISay\SIF\ML\Storage\UpdateStorage;
use Laminas\Feed\Writer\Feed;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Router\Annotation\Route;

class MLFeedController {
    function __construct(
        private readonly UpdateStorage $updateStorage,
        private readonly ConfiguratorInterface $configurator,
        private readonly CacheStorageProviderInterface $cacheProvider,
    ) {}

    private function generateFeed(string $name, string $title, callable $itemTitleFactory, array $contentFactories, bool $isInsider = false): string {
        $config = $this->configurator->getConfig('backend');
        $cache = $this->cacheProvider->storage('file');
        $feed = new Feed;
        $feed->setTitle($title);
        $feed->setDescription('eisɪꜰ2 RSS: ' . $title);
        $feed->setLink('https://' . $config['host_frontend'] . '/');
        $hashes = $this->updateStorage->readMetadata();
        foreach ($hashes as $timestamp => $hash) {
            $key = "feed/$name/$hash";
            $item = $cache->get($key);
            if (null === $item) {
                $info = $this->updateStorage->readUpdateInfo($hash);
                $news = $this->updateStorage->readUpdateNews($hash);
                $item = $this->generateUpdateItem($info, $news, $itemTitleFactory, $contentFactories, $isInsider);
                $cache->set($key, $item);
            }
            /** @var UpdateItem $item */
            if (empty($item->content)) continue;
            $entry = $feed->createEntry();
            $entry->setTitle($item->title);
            $entry->setDescription($item->content);
            $entry->setId(sha1($key));
            $entry->setDateCreated($item->createTime);
            $feed->addEntry($entry);
        }
        return $feed->export('rss');
    }

    private function generateUpdateItem(UpdateInfo $info, UpdateNews $news, callable $titleFactory, array $contentFactories, bool $isInsider): UpdateItem {
        $item = new UpdateItem($titleFactory($info, $news));
        foreach ($contentFactories as $subtitle => $contentFactory) {
            $content = $contentFactory($info, $news, $isInsider);
            if (!empty($content)) {
                if (!is_numeric($subtitle)) {
                    $item->content .= '<h3>' . $subtitle . '</h3>';
                }
                $item->content .= $content;
            }
        }
        return $item;
    }

    private function makeUpdateItemTitle(UpdateInfo $info, UpdateNews $news): string {
        return $info->updateTime->format('Y/m/d H:i') . ' 更新';
    }

    private function generateUpdateFeed(bool $isInsider): string {
        return $this->generateFeed('update' . ($isInsider ? '_insider' : ''), 'SIF2 新情报', $this->makeUpdateItemTitle(...), [
            0 => $this->outputHead(...),
            '新歌曲' => $this->outputNewMusic(...),
        ], $isInsider);
    }

    private function outputHead(UpdateInfo $info, UpdateNews $news, bool $isInsider): string {
        $text = '<p><small>' . $info->description . '</small></p>';
        if ($isInsider) {
            $text .= '<p><small>' . $info->assetHash . '</small></p>';
        }
        return $text;
    }

    #[Route('/feed/ml/update', methods: 'GET')]
    public function update(): string {
        return $this->generateUpdateFeed(false);
    }

    #[Route('/feed/ml/update/insider', methods: 'GET')]
    public function updateInsider(): string {
        return $this->generateUpdateFeed(true);
    }

    private function makeUpdateMusicItemTitle(UpdateInfo $info, UpdateNews $news): string {
        return $info->updateTime->format('Y/m/d') . ' 新歌曲';
    }

    #[Route('/feed/ml/update/music', methods: 'GET')]
    public function updateMusic(): string {
        return $this->generateFeed('update_music', 'SIF2 新歌曲', $this->makeUpdateMusicItemTitle(...), [$this->outputNewMusic(...)]);
    }

    private function outputNewMusic(UpdateInfo $info, UpdateNews $news, bool $isInsider): string {
        if ($info->isInitial && !$isInsider) return '';
        $text = '';
        foreach ($news->newMusic as $newMusic) {
            $text .= '<div>';
            if (!empty($newMusic->dictionaryReference)) {
                $text .= '<p><small>' . $newMusic->dictionaryReference . '</small></p>';
            }
            $text .= '<h4>' . $newMusic->name . '</h4>';
            $text .= '<p>' . $newMusic->artist . '</p>';
            $text .= '<p><small>' . $newMusic->detailInfo . '</small></p>';
            if (!empty($newMusic->type)) {
                $text .= '<p>属性：' . $newMusic->type->name . '</p>';
                foreach ($newMusic->levelNumbers as $level => $levelNumber) {
                    $text .= '<p>' . sprintf('%s %d★%d', strtoupper(LiveLevel::from($level)->name), $levelNumber, $newMusic->fullCombos[$level]) . '</p>';
                }
            }
            if ($isInsider) {
                $text .= '<p><small>ID: ' . $newMusic->id . '</small></p>';
            }
        }
        return $text;
    }
}
