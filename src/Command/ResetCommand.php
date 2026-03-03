<?php

declare(strict_types=1);

namespace DnCli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ResetCommand extends BaseCommand
{
    protected function requiresConfig(): bool
    {
        return false;
    }

    protected function configure(): void
    {
        $this
            ->setName('reset')
            ->setDescription('Remove stored configuration and credentials')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Skip confirmation prompt');
    }

    protected function handle(InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        if (!$this->configManager->isConfigured()) {
            $io->text('No configuration found. Nothing to reset.');
            return self::SUCCESS;
        }

        if (!$input->getOption('force')) {
            $confirmed = $io->confirm('This will remove all stored credentials. Continue?', false);
            if (!$confirmed) {
                $io->text('Reset cancelled.');
                return self::SUCCESS;
            }
        }

        $this->configManager->delete();

        $io->success('Configuration reset. Run `dn configure` to set up new credentials.');

        return self::SUCCESS;
    }
}
