<?php

declare(strict_types=1);

namespace DnCli\Tests\Command;

use Automattic\Domain_Services_Client\Response\Domain\Check as CheckResponse;
use DnCli\Command\CheckCommand;

class CheckCommandTest extends CommandTestCase
{
    public function test_single_domain_available(): void
    {
        $response = new CheckResponse($this->successData([
            'data' => [
                'domains' => [
                    'example.com' => [
                        'available' => true,
                        'fee_class' => 'standard',
                        'fee_amount' => 12.00,
                        'zone_is_active' => true,
                        'tld_in_maintenance' => false,
                    ],
                ],
            ],
        ]));

        $this->api->method('post')->willReturn($response);

        $tester = $this->createTester(new CheckCommand());
        $tester->execute(['domains' => ['example.com']]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('example.com', $output);
        $this->assertStringContainsString('Yes', $output);
        $this->assertStringContainsString('standard', $output);
        $this->assertStringContainsString('$12.00', $output);
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function test_domain_unavailable(): void
    {
        $response = new CheckResponse($this->successData([
            'data' => [
                'domains' => [
                    'taken.com' => [
                        'available' => false,
                        'fee_class' => 'standard',
                        'fee_amount' => 12.00,
                        'zone_is_active' => true,
                        'tld_in_maintenance' => false,
                    ],
                ],
            ],
        ]));

        $this->api->method('post')->willReturn($response);

        $tester = $this->createTester(new CheckCommand());
        $tester->execute(['domains' => ['taken.com']]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No', $output);
    }

    public function test_multiple_domains(): void
    {
        $response = new CheckResponse($this->successData([
            'data' => [
                'domains' => [
                    'a.com' => [
                        'available' => true,
                        'fee_class' => 'standard',
                        'fee_amount' => 10.00,
                        'zone_is_active' => true,
                        'tld_in_maintenance' => false,
                    ],
                    'b.com' => [
                        'available' => false,
                        'fee_class' => 'premium',
                        'fee_amount' => 500.00,
                        'zone_is_active' => true,
                        'tld_in_maintenance' => false,
                    ],
                ],
            ],
        ]));

        $this->api->method('post')->willReturn($response);

        $tester = $this->createTester(new CheckCommand());
        $tester->execute(['domains' => ['a.com', 'b.com']]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('a.com', $output);
        $this->assertStringContainsString('b.com', $output);
        $this->assertStringContainsString('premium', $output);
    }

    public function test_api_error(): void
    {
        $response = new CheckResponse($this->errorData('Server error'));

        $this->api->method('post')->willReturn($response);

        $tester = $this->createTester(new CheckCommand());
        $tester->execute(['domains' => ['example.com']]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('Server error', $tester->getDisplay());
    }

    public function test_exception_handling(): void
    {
        $this->api->method('post')->willThrowException(new \RuntimeException('Connection failed'));

        $tester = $this->createTester(new CheckCommand());
        $tester->execute(['domains' => ['example.com']]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('Connection failed', $tester->getDisplay());
    }

    public function test_not_configured(): void
    {
        $tester = $this->createUnconfiguredTester(new CheckCommand());
        $tester->execute(['domains' => ['example.com']]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('No API credentials found', $tester->getDisplay());
    }

    public function test_tld_in_maintenance(): void
    {
        $response = new CheckResponse($this->successData([
            'data' => [
                'domains' => [
                    'example.xyz' => [
                        'available' => true,
                        'fee_class' => 'standard',
                        'fee_amount' => 5.00,
                        'zone_is_active' => true,
                        'tld_in_maintenance' => true,
                    ],
                ],
            ],
        ]));

        $this->api->method('post')->willReturn($response);

        $tester = $this->createTester(new CheckCommand());
        $tester->execute(['domains' => ['example.xyz']]);

        $this->assertSame(0, $tester->getStatusCode());
    }
}
