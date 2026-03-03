<?php

declare(strict_types=1);

namespace DnCli\Tests\Command;

use DnCli\Command\ResetCommand;
use DnCli\Config\ConfigManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ResetCommandTest extends CommandTestCase
{
    public function test_reset_deletes_config_with_force(): void
    {
        putenv('DN_API_KEY=test-key');
        putenv('DN_API_USER=test-user');

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('isConfigured')->willReturn(true);
        $configManager->expects($this->once())->method('delete');

        $command = new ResetCommand($configManager);
        $app = new Application();
        $app->add($command);
        $tester = new CommandTester($app->find('reset'));

        $tester->execute(['--force' => true]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Configuration reset', $tester->getDisplay());
    }

    public function test_reset_confirms_before_deleting(): void
    {
        putenv('DN_API_KEY=test-key');
        putenv('DN_API_USER=test-user');

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('isConfigured')->willReturn(true);
        $configManager->expects($this->once())->method('delete');

        $command = new ResetCommand($configManager);
        $app = new Application();
        $app->add($command);
        $tester = new CommandTester($app->find('reset'));

        $tester->setInputs(['yes']);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Configuration reset', $tester->getDisplay());
    }

    public function test_reset_cancelled_when_user_declines(): void
    {
        putenv('DN_API_KEY=test-key');
        putenv('DN_API_USER=test-user');

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('isConfigured')->willReturn(true);
        $configManager->expects($this->never())->method('delete');

        $command = new ResetCommand($configManager);
        $app = new Application();
        $app->add($command);
        $tester = new CommandTester($app->find('reset'));

        $tester->setInputs(['no']);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Reset cancelled', $tester->getDisplay());
    }

    public function test_reset_when_not_configured(): void
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('isConfigured')->willReturn(false);
        $configManager->expects($this->never())->method('delete');

        $command = new ResetCommand($configManager);
        $app = new Application();
        $app->add($command);
        $tester = new CommandTester($app->find('reset'));

        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('No configuration found', $tester->getDisplay());
    }
}
