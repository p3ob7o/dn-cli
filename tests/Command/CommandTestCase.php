<?php

declare(strict_types=1);

namespace DnCli\Tests\Command;

use Automattic\Domain_Services_Client\Api;
use DnCli\Command\BaseCommand;
use DnCli\Config\ConfigManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends TestCase
{
    protected Api&MockObject $api;
    private string $savedApiKey = '';
    private string $savedApiUser = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = $this->createMock(Api::class);

        // Save and clear env vars
        $this->savedApiKey = getenv('DN_API_KEY') ?: '';
        $this->savedApiUser = getenv('DN_API_USER') ?: '';
        putenv('DN_API_KEY');
        putenv('DN_API_USER');
        putenv('DN_API_URL');
    }

    /**
     * Create a CommandTester for a command with a mocked API injected.
     * Sets env vars so config check passes.
     */
    protected function createTester(BaseCommand $command): CommandTester
    {
        putenv('DN_API_KEY=test-key');
        putenv('DN_API_USER=test-user');

        // Re-create the command with the mock API injected via constructor
        // (BaseCommand accepts ?Api as second constructor parameter).
        $commandClass = get_class($command);
        $command = new $commandClass(null, $this->api);

        $app = new Application();
        $app->add($command);

        return new CommandTester($app->find($command->getName()));
    }

    /**
     * Create a CommandTester for a command WITHOUT credentials (for testing unconfigured state).
     */
    protected function createUnconfiguredTester(BaseCommand $command): CommandTester
    {
        putenv('DN_API_KEY');
        putenv('DN_API_USER');

        $app = new Application();
        $app->add($command);

        return new CommandTester($app->find($command->getName()));
    }

    protected function successData(array $data = []): array
    {
        return array_merge([
            'success' => true,
            'status' => 200,
            'status_description' => 'Command completed successfully.',
            'client_txn_id' => 'test-txn',
            'server_txn_id' => 'srv-txn',
            'timestamp' => time(),
            'runtime' => 0.1,
        ], $data);
    }

    protected function errorData(string $message = 'Command failed.'): array
    {
        return [
            'success' => false,
            'status' => 400,
            'status_description' => $message,
            'client_txn_id' => 'test-txn',
            'server_txn_id' => 'srv-txn',
            'timestamp' => time(),
            'runtime' => 0.1,
        ];
    }

    protected function tearDown(): void
    {
        // Restore env vars
        if ($this->savedApiKey !== '') {
            putenv('DN_API_KEY=' . $this->savedApiKey);
        } else {
            putenv('DN_API_KEY');
        }
        if ($this->savedApiUser !== '') {
            putenv('DN_API_USER=' . $this->savedApiUser);
        } else {
            putenv('DN_API_USER');
        }
        putenv('DN_API_URL');

        parent::tearDown();
    }
}
