<?php
namespace EverISay\SIF\Backend\Endpoint\Console;

use EverISay\SIF\Backend\Endpoint\Web\Feed\MLFeedController;
use EverISay\SIF\ML\Storage\UpdateStorage;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Symfony\Component\Process\Process;

#[AsCommand('ml:update', 'Update SIFML game data on a single machine')]
final class MLUpdateCommand extends Command {
    #[Argument]
    private ?string $hash = null;
    #[Argument]
    private ?string $time = null;

    public function __invoke(
        EnvironmentInterface $env,
        UpdateStorage $updateStorage,
        MLFeedController $mLFeedController,
    ): int {
        $infoMetadata = $updateStorage->readMetadata();
        $previousHash = reset($infoMetadata);
        if (empty($this->hash)) {
            $currentHash = rtrim($this->runUpdater($env, ['a'])->getOutput());
            if ($currentHash == $previousHash) return self::SUCCESS;
            $this->runUpdater($env, ['du', $currentHash]);
        } else {
            if ($this->hash == $previousHash) return self::SUCCESS;
            $this->runUpdater($env, array_merge(
                ['du', $this->hash, '-m'],
                !empty($this->time) ? ['-t', $this->time] : [],
            ));
        }
        $mLFeedController->update();
        $mLFeedController->updateInsider();
        $mLFeedController->updateMusic();
        return self::SUCCESS;
    }

    private function runUpdater(EnvironmentInterface $env, array $arguments): Process {
        $updater = new Process(array_merge(['php', '-f', $env->get('CMD_UPDATER'), '--'], $arguments));
        $updater->mustRun();
        return $updater;
    }
}
