<?php
namespace EverISay\SIF\Backend\Endpoint\Web\Feed;

use EverISay\SIF\Backend\Domain\Feed\UpdateItem;
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

    private function generateFeed(string $name, string $title, callable $itemCreator): string {
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
                $item = $itemCreator($info, $news);
                $cache->set($key, $item);
            }
            $entry = $feed->createEntry();
            /** @var UpdateItem $item */
            $entry->setTitle($item->title);
            $entry->setDescription($item->content);
            $entry->setId(sha1($key));
            $entry->setDateCreated($item->createTime);
            $feed->addEntry($entry);
        }
        return $feed->export('rss');
    }

    #[Route('/feed/ml/update', methods: 'GET')]
    public function update(): string {
        return $this->generateFeed('update', 'SIF2 新情报', $this->createUpdateItem(...));
    }

    private function createUpdateItem(UpdateInfo $info, UpdateNews $news): UpdateItem {
        $title = $info->updateTime->format('Y/m/d H:i') . ' 更新';
        $item = new UpdateItem($title);
        $item->content .= sprintf('<p><small>Hash: %s</small></p>', $info->assetHash);
        return $item;
    }
}
