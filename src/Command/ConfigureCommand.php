<?php

declare(strict_types=1);

namespace DnCli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigureCommand extends BaseCommand
{
    protected function requiresConfig(): bool
    {
        return false;
    }

    protected function configure(): void
    {
        $this
            ->setName('configure')
            ->setDescription('Set up API credentials for the Domain Services API')
            ->addOption('api-key', null, InputOption::VALUE_REQUIRED, 'API key')
            ->addOption('api-user', null, InputOption::VALUE_REQUIRED, 'API user')
            ->addOption('api-url', null, InputOption::VALUE_OPTIONAL, 'API base URL (optional override)');
    }

    protected function handle(InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        $apiKey = $input->getOption('api-key');
        $apiUser = $input->getOption('api-user');
        $apiUrl = $input->getOption('api-url');

        if ($apiKey === null) {
            $apiKey = $io->ask('API Key (X-DSAPI-KEY)');
        }

        if ($apiUser === null) {
            $apiUser = $io->ask('API User (X-DSAPI-USER)');
        }

        if ($apiKey === null || $apiUser === null) {
            $io->error('API key and user are required.');
            return self::FAILURE;
        }

        if ($apiUrl === null) {
            $apiUrl = $io->ask('API URL (leave blank for default)', '');
            if ($apiUrl === '') {
                $apiUrl = null;
            }
        }

        $this->configManager->save($apiKey, $apiUser, $apiUrl);

        $io->success('Configuration saved to ' . $this->configManager->getConfigPath());

        return self::SUCCESS;
    }
}
