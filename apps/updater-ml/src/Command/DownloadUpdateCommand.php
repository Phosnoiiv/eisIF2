<?php
namespace EverISay\SIF\ML\Updater\Command;

use EverISay\SIF\ML\Common\Config\AbstractVersionConfig;
use EverISay\SIF\ML\Storage\DownloadStorage;
use EverISay\SIF\ML\Updater\Helper\NetworkHelper;
use EverISay\SIF\ML\Updater\Step\DownloadManifestStep;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('downloadUpdate', null, ['du'])]
final class DownloadUpdateCommand extends Command {
    function __construct(
        private readonly DownloadStorage $storage,
        private readonly AbstractVersionConfig $versionConfig,
        private readonly NetworkHelper $networkHelper,
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this->addArgument('assetHash', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $assetHash = $input->getArgument('assetHash');
        (new DownloadManifestStep($assetHash, $this->storage, $this->versionConfig, $this->networkHelper))->execute();
        return Command::SUCCESS;
    }
}
