<?php

declare(strict_types=1);

namespace DnCli\Tests\Factory;

use Automattic\Domain_Services_Client\Api;
use DnCli\Config\ConfigManager;
use DnCli\Factory\ApiClientFactory;
use PHPUnit\Framework\TestCase;

class ApiClientFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        putenv('DN_API_KEY=factory-test-key');
        putenv('DN_API_USER=factory-test-user');
        putenv('DN_API_URL');
    }

    protected function tearDown(): void
    {
        putenv('DN_API_KEY');
        putenv('DN_API_USER');
        putenv('DN_API_URL');
        parent::tearDown();
    }

    public function test_creates_api_instance(): void
    {
        $config = new ConfigManager();
        $api = ApiClientFactory::create($config);

        $this->assertInstanceOf(Api::class, $api);
    }

    public function test_creates_api_with_custom_url(): void
    {
        putenv('DN_API_URL=https://custom.example.com/api');
        $config = new ConfigManager();

        // Should not throw — custom URL is accepted
        $api = ApiClientFactory::create($config);
        $this->assertInstanceOf(Api::class, $api);
    }

    public function test_rejects_http_url(): void
    {
        putenv('DN_API_URL=http://insecure.example.com/api');
        $config = new ConfigManager();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API URL must use HTTPS');
        ApiClientFactory::create($config);
    }
}
