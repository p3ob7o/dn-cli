<?php

declare(strict_types=1);

namespace DnCli\Tests\Command;

use DnCli\Command\ConfigureCommand;
use DnCli\Config\ConfigManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigureCommandTest extends TestCase
{
    private string $tempDir;
    private string $savedHome = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/dn-cli-configure-test-' . uniqid();
        mkdir($this->tempDir, 0700, true);

        $this->savedHome = getenv('HOME') ?: '';
        putenv('HOME=' . $this->tempDir);
        putenv('DN_API_KEY');
        putenv('DN_API_USER');
        putenv('DN_API_URL');
    }

    protected function tearDown(): void
    {
        if ($this->savedHome !== '') {
            putenv('HOME=' . $this->savedHome);
        } else {
            putenv('HOME');
        }
        putenv('DN_API_KEY');
        putenv('DN_API_USER');
        putenv('DN_API_URL');

        // Cleanup
        $configDir = $this->tempDir . '/.config/dn';
        if (is_file($configDir . '/config.json')) {
            unlink($configDir . '/config.json');
        }
        if (is_dir($configDir)) {
            rmdir($configDir);
        }
        if (is_dir($this->tempDir . '/.config')) {
            rmdir($this->tempDir . '/.config');
        }
        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }

        parent::tearDown();
    }

    private function createTester(): CommandTester
    {
        $command = new ConfigureCommand();
        $app = new Application();
        $app->add($command);

        return new CommandTester($app->find('configure'));
    }

    public function test_configure_with_options(): void
    {
        $tester = $this->createTester();
        $tester->execute([
            '--api-key' => 'my-key',
            '--api-user' => 'my-user',
        ], ['interactive' => false]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Configuration saved', $tester->getDisplay());

        $configFile = $this->tempDir . '/.config/dn/config.json';
        $this->assertFileExists($configFile);

        $data = json_decode(file_get_contents($configFile), true);
        $this->assertSame('my-key', $data['api_key']);
        $this->assertSame('my-user', $data['api_user']);
    }

    public function test_configure_with_api_url(): void
    {
        $tester = $this->createTester();
        $tester->execute([
            '--api-key' => 'key',
            '--api-user' => 'user',
            '--api-url' => 'https://custom.api.com',
        ], ['interactive' => false]);

        $this->assertSame(0, $tester->getStatusCode());

        $data = json_decode(file_get_contents($this->tempDir . '/.config/dn/config.json'), true);
        $this->assertSame('https://custom.api.com', $data['api_url']);
    }

    public function test_configure_interactive(): void
    {
        $tester = $this->createTester();
        $tester->setInputs(['interactive-key', 'interactive-user', '']);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());

        $data = json_decode(file_get_contents($this->tempDir . '/.config/dn/config.json'), true);
        $this->assertSame('interactive-key', $data['api_key']);
        $this->assertSame('interactive-user', $data['api_user']);
    }

    public function test_does_not_require_prior_config(): void
    {
        // ConfigureCommand should work even when not configured
        $tester = $this->createTester();
        $tester->execute([
            '--api-key' => 'key',
            '--api-user' => 'user',
        ], ['interactive' => false]);

        $this->assertSame(0, $tester->getStatusCode());
    }
}
