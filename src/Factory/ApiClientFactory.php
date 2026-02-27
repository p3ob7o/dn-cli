<?php

declare(strict_types=1);

namespace DnCli\Factory;

use Automattic\Domain_Services_Client\Api;
use Automattic\Domain_Services_Client\Configuration;
use Automattic\Domain_Services_Client\Request;
use Automattic\Domain_Services_Client\Response;
use DnCli\Config\ConfigManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

class ApiClientFactory
{
    public static function create(ConfigManager $config): Api
    {
        $configuration = new Configuration();
        $configuration->set_api_key('X-DSAPI-KEY', $config->getApiKey());
        $configuration->set_api_key('X-DSAPI-USER', $config->getApiUser());

        $apiUrl = $config->getApiUrl();
        if ($apiUrl !== null) {
            $configuration->set_host($apiUrl);
        }

        $httpFactory = new HttpFactory();
        $requestFactory = new Request\Factory($httpFactory, $httpFactory);
        $responseFactory = new Response\Factory();
        $httpClient = new Client();

        return new Api($configuration, $requestFactory, $responseFactory, $httpClient);
    }
}
