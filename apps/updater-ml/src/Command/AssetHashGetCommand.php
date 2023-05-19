<?php
namespace EverISay\SIF\ML\Updater\Command;

use Dotenv\Dotenv;
use EverISay\SIF\ML\Common\Config\AccountConfig;
use EverISay\SIF\ML\Common\Config\DeviceConfig;
use EverISay\SIF\ML\Flient\Flient;
use EverISay\SIF\ML\Proprietary\NetworkHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('assetHash')]
final class AssetHashGetCommand extends Command {
    function __construct(
        private readonly Dotenv $env,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $versionConfigClass = env('VERSION_CONFIG');
        $versionConfig = new $versionConfigClass;
        $deviceConfig = new DeviceConfig;
        $deviceConfig->device = env('DEVICE');
        $deviceConfig->os = env('DEVICE_OS');
        $deviceConfig->osVersion = env('DEVICE_OS_VERSION');
        $accountConfig = new AccountConfig;
        $accountConfig->userId = env('ACCOUNT_ID');
        $accountConfig->privateKey = env('ACCOUNT_PRIVATE_KEY');
        $networkHelper = new NetworkHelper($versionConfig, $accountConfig);
        $flient = new Flient($versionConfig, $deviceConfig, $networkHelper);
        $output->writeln($flient->getAssetHash());
        return Command::SUCCESS;
    }
}
