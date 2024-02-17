<?php
namespace EverISay\SIF\ML\Updater\Command;

use EverISay\SIF\ML\Flient\Flient;
use EverISay\SIF\ML\Updater\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('assetHash')]
final class AssetHashGetCommand extends Command implements LoggerAwareInterface {
    use LoggerAwareTrait;

    function __construct(
        private readonly Flient $flient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setLoggerConsoleOutput($output);
        $output->writeln($this->flient->getAssetHash());
        return Command::SUCCESS;
    }
}
