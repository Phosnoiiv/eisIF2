<?php
namespace EverISay\SIF\ML\Updater\Command;

use EverISay\SIF\ML\Flient\Flient;
use EverISay\SIF\ML\Storage\InteractionStorage;
use EverISay\SIF\ML\Updater\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('clear-rate')]
final class FetchLiveClearRateCommand extends Command implements LoggerAwareInterface {
    use LoggerAwareTrait;

    function __construct(
        private readonly Flient $flient,
        private readonly InteractionStorage $interactionStorage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setLoggerConsoleOutput($output);
        $rates = $this->flient->getLiveClearRate();
        $this->interactionStorage->writeClearRates($rates);
        return Command::SUCCESS;
    }
}
