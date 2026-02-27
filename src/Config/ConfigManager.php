<?php

declare(strict_types=1);

namespace DnCli\Config;

class ConfigManager
{
    private const CONFIG_DIR = '.config/dn';
    private const CONFIG_FILE = 'config.json';

    private ?array $config = null;

    public function getApiKey(): ?string
    {
        return $this->getEnv('DN_API_KEY') ?? $this->get('api_key');
    }

    public function getApiUser(): ?string
    {
        return $this->getEnv('DN_API_USER') ?? $this->get('api_user');
    }

    public function getApiUrl(): ?string
    {
        return $this->getEnv('DN_API_URL') ?? $this->get('api_url');
    }

    public function isConfigured(): bool
    {
        return $this->getApiKey() !== null && $this->getApiUser() !== null;
    }

    public function save(string $apiKey, string $apiUser, ?string $apiUrl = null): void
    {
        $dir = $this->getConfigDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $data = [
            'api_key' => $apiKey,
            'api_user' => $apiUser,
        ];

        if ($apiUrl !== null) {
            $data['api_url'] = $apiUrl;
        }

        file_put_contents(
            $this->getConfigPath(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
        );

        chmod($this->getConfigPath(), 0600);

        // Reset cached config
        $this->config = null;
    }

    public function getConfigPath(): string
    {
        return $this->getConfigDir() . '/' . self::CONFIG_FILE;
    }

    private function getConfigDir(): string
    {
        $home = $this->getEnv('HOME') ?? $this->getEnv('USERPROFILE') ?? '~';
        return $home . '/' . self::CONFIG_DIR;
    }

    private function get(string $key): ?string
    {
        if ($this->config === null) {
            $this->config = $this->loadConfig();
        }

        return $this->config[$key] ?? null;
    }

    private function loadConfig(): array
    {
        $path = $this->getConfigPath();

        if (!file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return [];
        }

        $data = json_decode($contents, true);
        return is_array($data) ? $data : [];
    }

    private function getEnv(string $name): ?string
    {
        $value = $_ENV[$name] ?? getenv($name);
        return ($value !== false && $value !== '') ? (string) $value : null;
    }
}
