<?php
namespace EverISay\SIF\ML\Updater\Command;

use EverISay\SIF\ML\Storage\Update\UpdateInfo;
use EverISay\SIF\ML\Updater\Step\CreateNewsStep;
use EverISay\SIF\ML\Updater\Step\DecodeManifestStep;
use EverISay\SIF\ML\Updater\Step\DownloadManifestStep;
use EverISay\SIF\ML\Updater\Step\SaveUpdateInfoStep;
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
        private readonly SaveUpdateInfoStep $saveUpdateInfoStep,
        private readonly CreateNewsStep $createNewsStep,
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this->addArgument('assetHash', InputArgument::REQUIRED);
        $this->addOption('time', 't', InputOption::VALUE_REQUIRED, 'Time string represented in +8 timezone', 'now');
        $this->addOption('manual', 'm', InputOption::VALUE_NONE);
        $this->addOption('initial', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $assetHash = $input->getArgument('assetHash');
        $time = new \DateTimeImmutable($input->getOption('time'), new \DateTimeZone('+0800'));
        $isManual = $input->getOption('manual');
        $isInitial = $input->getOption('initial');
        $description = match (true) {
            $isInitial => '初始更新',
            $isManual => '由 eisɪꜰ 运营人员手动触发的更新',
            default => 'eisɪꜰ 定时自动更新',
        };
        $updateInfo = new UpdateInfo($assetHash, $time, $description, $isInitial);
        $this->downloadManifestStep->execute($assetHash);
        $this->decodeManifestStep->execute($assetHash, $time);
        $this->updateDatabaseStep->setLoggerConsoleOutput($output)->execute($updateInfo);
        $this->saveUpdateInfoStep->execute($updateInfo);
        $this->createNewsStep->execute();
        return Command::SUCCESS;
    }
}
