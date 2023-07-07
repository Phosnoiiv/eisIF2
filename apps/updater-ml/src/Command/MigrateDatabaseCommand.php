<?php
namespace EverISay\SIF\ML\Updater\Command;

use EverISay\SIF\ML\Storage\DatabaseStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('migrate')]
final class MigrateDatabaseCommand extends Command {
    function __construct(
        private readonly DatabaseStorage $storage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->storage->syncSchema();
        return Command::SUCCESS;
    }
}
