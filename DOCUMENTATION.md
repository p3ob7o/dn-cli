# dn CLI — Project Documentation

## Overview

`dn` is a command-line tool for managing domains via the Automattic Domain Services API. It wraps the `automattic/domain-services-client` PHP library, providing a Symfony Console interface for domain availability checks, registration, DNS management, contact updates, privacy settings, and more.

## Architecture

```
bin/dn  →  Application  →  Command  →  ApiClientFactory  →  Api  →  DSAPI
                              ↑
                        ConfigManager (env vars / config file)
```

- **Entry point** (`bin/dn`): Finds Composer autoloader (local or global install), creates and runs the Application.
- **Application**: Registers all 14 commands.
- **BaseCommand**: Abstract base providing auth guard, API creation (with test injection support), and error message sanitization.
- **ConfigManager**: Resolves credentials from environment variables (`DN_API_KEY`, `DN_API_USER`) or `~/.config/dn/config.json`. Env vars take priority.
- **ApiClientFactory**: Static factory that wires `Configuration`, Guzzle HTTP client, and request/response factories into the `Api` client. Enforces HTTPS on custom URLs.

## Commands (14)

| Command | Class | Description |
|---|---|---|
| `configure` | ConfigureCommand | Set up API credentials (hidden input or `--stdin`) |
| `check` | CheckCommand | Check domain availability and pricing |
| `suggest` | SuggestCommand | Get domain name suggestions |
| `info` | InfoCommand | Domain details: dates, contacts, nameservers, EPP status |
| `register` | RegisterCommand | Register a domain with contact info and privacy |
| `renew` | RenewCommand | Renew a domain registration |
| `delete` | DeleteCommand | Delete a domain (with confirmation) |
| `restore` | RestoreCommand | Restore a deleted domain |
| `transfer` | TransferCommand | Transfer a domain in (hidden auth code input) |
| `dns:get` | DnsGetCommand | View DNS records |
| `dns:set` | DnsSetCommand | Set DNS records (supports multiple values) |
| `contacts:set` | ContactsSetCommand | Update contact information |
| `privacy` | PrivacySetCommand | Set WHOIS privacy (on/off/redact) |
| `transferlock` | TransferlockCommand | Set transfer lock (on/off) |

## Test Suite

- **120 tests, 206 assertions** — all passing, zero deprecations
- **Fully mocked** — no API credentials needed to run tests
- **Coverage**: every command (success, API error, exception, unconfigured state), ConfigManager (env vars, file I/O, permissions, caching), ApiClientFactory (creation, HTTPS enforcement), Application (command registration)
- **Security tests**: credential redaction in error output, TOCTOU permission fix, HTTP URL rejection, config path non-disclosure

## Security Measures

1. **Credential input**: `askHidden()` for interactive, `--stdin` for scripted — no CLI flags that appear in `ps` or shell history
2. **Config file permissions**: `chmod 0600` applied before writing content (TOCTOU-safe)
3. **Error sanitization**: `BaseCommand::sanitizeErrorMessage()` redacts API key/user from exception messages before display
4. **HTTPS enforcement**: `ApiClientFactory` rejects custom API URLs that don't use HTTPS
5. **Immutable API injection**: Constructor parameter, not a mutable setter
6. **gitignore**: Covers `.env*`, `vendor/`, `composer.lock`, IDE files

## Distribution

- **Composer global**: `composer global require p3ob7o/dn-cli`
- **From source**: `git clone` + `composer install` + `./bin/dn`
- Entry point `bin/dn` handles both autoloader paths

## Current Status

- All 14 commands implemented and tested
- Security review completed with all findings resolved
- README with full usage documentation
- No remote repository configured yet
- No LICENSE file created yet (declared as GPL-2.0-or-later in composer.json)
