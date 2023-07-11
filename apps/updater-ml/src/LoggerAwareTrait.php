<?php
namespace EverISay\SIF\ML\Updater;

use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Output\OutputInterface;

trait LoggerAwareTrait {
    use \Psr\Log\LoggerAwareTrait;

    /**
     * @return $this
     */
    public function setLoggerConsoleOutput(OutputInterface $output): static {
        if (! $this->logger instanceof Logger) return $this;
        foreach ($this->logger->getHandlers() as $handler) {
            if (! $handler instanceof ConsoleHandler) continue;
            $handler->setOutput($output);
        }
        return $this;
    }
}
