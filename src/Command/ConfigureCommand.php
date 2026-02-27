<?php

declare(strict_types=1);

namespace DnCli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
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
            ->addOption('stdin', null, InputOption::VALUE_NONE, 'Read API key and user from stdin (one per line)')
            ->addOption('api-url', null, InputOption::VALUE_OPTIONAL, 'API base URL (optional override)');
    }

    protected function handle(InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        $apiUrl = $input->getOption('api-url');

        if ($input->getOption('stdin')) {
            $stream = $this->getInputStream($input);
            $apiKey = trim((string) fgets($stream));
            $apiUser = trim((string) fgets($stream));
        } else {
            $apiKey = (string) $io->askHidden('API Key (X-DSAPI-KEY)');
            $apiUser = (string) $io->askHidden('API User (X-DSAPI-USER)');

            if ($apiUrl === null) {
                $apiUrl = $io->ask('API URL (leave blank for default)', '');
                if ($apiUrl === '') {
                    $apiUrl = null;
                }
            }
        }

        if ($apiKey === '' || $apiUser === '') {
            $io->error('API key and user are required.');
            return self::FAILURE;
        }

        $this->configManager->save($apiKey, $apiUser, $apiUrl);

        $io->success('Configuration saved.');

        return self::SUCCESS;
    }

    /**
     * @return resource
     */
    private function getInputStream(InputInterface $input)
    {
        if ($input instanceof StreamableInputInterface && $input->getStream()) {
            return $input->getStream();
        }

        return fopen('php://stdin', 'r');
    }
}
