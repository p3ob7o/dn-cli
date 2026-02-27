# dn CLI — Development Guide

## Quick Reference

```bash
composer install          # Install dependencies
composer test             # Run test suite (alias for phpunit)
vendor/bin/phpunit        # Run tests directly
vendor/bin/phpunit --filter=CheckCommand  # Run specific test class
```

## Project Structure

```
src/
  Application.php              # Symfony Console app, registers all 14 commands
  Command/
    BaseCommand.php            # Abstract base: auth guard, API creation, error sanitization
    ConfigureCommand.php       # dn configure (--stdin or interactive hidden input)
    CheckCommand.php           # dn check <domain>...
    ... (14 commands total)
  Config/ConfigManager.php     # Credential resolution: env vars → config file
  Factory/ApiClientFactory.php # Wires Api client from ConfigManager
tests/
  Command/CommandTestCase.php  # Shared test base: mock API, env var management
  Command/*Test.php            # One test file per command
  Config/ConfigManagerTest.php # Config file I/O, env var priority
  Factory/ApiClientFactoryTest.php
```

## Namespace

`DnCli\` → `src/`, `DnCli\Tests\` → `tests/` (PSR-4)

## Architecture Conventions

### Command Pattern
Every command extends `BaseCommand` and implements `handle()`. The base class:
- Guards against missing credentials via `requiresConfig()` (override to return `false` for `ConfigureCommand`)
- Provides `createApi()` which uses constructor-injected `?Api` for tests, or `ApiClientFactory` in production
- Provides `sanitizeErrorMessage()` to redact API key/user from exception output

### API Mocking in Tests
- Commands accept `?Api $api` as second constructor parameter (immutable injection)
- `CommandTestCase::createTester()` re-creates the command via `get_class()` to inject the mock API
- Response objects are constructed directly with fixture arrays (the library's `Data_Trait` supports this)
- Date format in fixtures must be `'Y-m-d H:i:s'` (not ISO 8601)

### Config & Credentials
- Resolution order: `DN_API_KEY`/`DN_API_USER` env vars → `~/.config/dn/config.json`
- Config file created with `0600` permissions (chmod before write to avoid TOCTOU race)
- `ConfigureCommand` uses `askHidden()` for interactive input, `--stdin` for piped input
- No CLI flags for credentials (prevents `ps aux` / shell history exposure)

### Security Patterns
- All catch blocks use `sanitizeErrorMessage()` to redact credentials
- `ApiClientFactory` rejects non-HTTPS custom API URLs
- `.gitignore` includes `.env*` to prevent accidental credential commits
- Success messages never reveal config file paths

## Testing Conventions

- Test classes mirror source structure: `src/Command/FooCommand.php` → `tests/Command/FooCommandTest.php`
- Each command test covers: success path, API error, exception handling, unconfigured state, plus command-specific edge cases
- Use PHPUnit 11 attributes (`#[DataProvider(...)]`), not doc-comment annotations
- Env vars are saved/restored in setUp/tearDown to avoid test pollution
- `ConfigManagerTest` uses real temp directories (no mocking for file I/O)

## Dependencies

- `automattic/domain-services-client` ^1.6 — Domain Services API client
- `symfony/console` ^6.0 || ^7.0 — CLI framework
- `guzzlehttp/guzzle` ^7.0 — HTTP client (PSR-18)
- `phpunit/phpunit` ^10.0 || ^11.0 — Testing (dev)
