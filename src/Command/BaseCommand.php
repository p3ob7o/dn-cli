<?php

declare(strict_types=1);

namespace DnCli\Command;

use Automattic\Domain_Services_Client\Api;
use DnCli\Config\ConfigManager;
use DnCli\Factory\ApiClientFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{
    protected ConfigManager $configManager;
    private ?Api $apiOverride = null;

    public function __construct(?ConfigManager $configManager = null)
    {
        $this->configManager = $configManager ?? new ConfigManager();
        parent::__construct();
    }

    public function setApi(Api $api): void
    {
        $this->apiOverride = $api;
    }

    protected function requiresConfig(): bool
    {
        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->requiresConfig() && !$this->configManager->isConfigured()) {
            $io->warning('No API credentials found. Run `dn configure` first, or set DN_API_KEY and DN_API_USER environment variables.');
            return Command::FAILURE;
        }

        return $this->handle($input, $output, $io);
    }

    abstract protected function handle(InputInterface $input, OutputInterface $output, SymfonyStyle $io): int;

    protected function createApi(): Api
    {
        if ($this->apiOverride !== null) {
            return $this->apiOverride;
        }

        return ApiClientFactory::create($this->configManager);
    }
}
