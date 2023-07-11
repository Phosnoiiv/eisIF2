<?php
namespace EverISay\SIF\ML\Updater\Command;

use EverISay\SIF\ML\Updater\Step\DecodeManifestStep;
use EverISay\SIF\ML\Updater\Step\DownloadManifestStep;
use EverISay\SIF\ML\Updater\Step\UpdateDatabaseStep;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('downloadUpdate', null, ['du'])]
final class DownloadUpdateCommand extends Command {
    function __construct(
        private readonly DownloadManifestStep $downloadManifestStep,
        private readonly DecodeManifestStep $decodeManifestStep,
        private readonly UpdateDatabaseStep $updateDatabaseStep,
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this->addArgument('assetHash', InputArgument::REQUIRED);
        $this->addOption('time', 't', InputOption::VALUE_REQUIRED, 'Time string represented in +8 timezone', 'now');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $assetHash = $input->getArgument('assetHash');
        $time = new \DateTimeImmutable($input->getOption('time'), new \DateTimeZone('+0800'));
        $this->downloadManifestStep->execute($assetHash);
        $this->decodeManifestStep->execute($assetHash, $time);
        $this->updateDatabaseStep->setLoggerConsoleOutput($output)->execute($time);
        return Command::SUCCESS;
    }
}
