# dn — Domain Services CLI

A command-line tool for managing domains via the Automattic Domain Services API. Check availability, register domains, manage DNS, configure privacy, and more — all from your terminal.

## Installation

### Composer (global)

```bash
composer global require automattic/dn-cli
```

Make sure `~/.composer/vendor/bin` (or `~/.config/composer/vendor/bin`) is in your `PATH`:

```bash
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

### From source

```bash
git clone https://github.com/Automattic/dn-cli.git
cd dn-cli
composer install
```

Then run with `./bin/dn` or symlink it into your PATH:

```bash
ln -s "$(pwd)/bin/dn" /usr/local/bin/dn
```

## Configuration

The CLI needs two credentials: an API key and an API user.

### Interactive setup

```bash
dn configure
```

This prompts for your credentials and saves them to `~/.config/dn/config.json`.

You can also pass them directly:

```bash
dn configure --api-key=YOUR_KEY --api-user=YOUR_USER
```

### Environment variables

Set `DN_API_KEY` and `DN_API_USER` in your shell. These take priority over the config file:

```bash
export DN_API_KEY="your-api-key"
export DN_API_USER="your-api-user"
```

Optionally, override the API endpoint:

```bash
export DN_API_URL="https://custom-endpoint.example.com/command"
```

### Config file

Stored at `~/.config/dn/config.json` with mode `0600`:

```json
{
    "api_key": "your-api-key",
    "api_user": "your-api-user"
}
```

### Credential resolution order

1. Environment variables (`DN_API_KEY`, `DN_API_USER`)
2. Config file (`~/.config/dn/config.json`)

If no credentials are found, the CLI will prompt you to run `dn configure`.

## Commands

### Check domain availability

```bash
# Single domain
dn check example.com

# Multiple domains at once
dn check example.com example.net example.org
```

Returns a table with availability, pricing, fee class, and TLD status.

### Get domain suggestions

```bash
# Basic suggestions
dn suggest "coffee shop"

# Filter by TLDs and limit count
dn suggest "coffee" --tlds=com,net,io --count=20

# Exact match only
dn suggest "mycoffee" --exact
```

### Domain information

```bash
dn info example.com
```

Displays registration dates, expiration, nameservers, contacts, EPP status codes, privacy settings, and auth code.

### Register a domain

```bash
# Interactive — prompts for contact details
dn register newdomain.com

# Non-interactive with all options
dn register newdomain.com \
  --first-name=Jane \
  --last-name=Doe \
  --email=jane@example.com \
  --phone=+1.5551234567 \
  --address="123 Main St" \
  --city="San Francisco" \
  --state=CA \
  --postal-code=94110 \
  --country=US \
  --period=2 \
  --privacy=on
```

Options:
- `--period` — Registration years (default: 1)
- `--privacy` — `on` (default), `off`, or `redact`
- `--price` — Required for premium domains (in cents)

### Renew a domain

```bash
dn renew example.com --expiration-year=2026 --period=1
```

The `--expiration-year` is the domain's current expiration year (required).

### Delete a domain

```bash
dn delete example.com
```

Prompts for confirmation before proceeding.

### Restore a deleted domain

```bash
dn restore example.com
```

### Transfer a domain

```bash
# Interactive — prompts for auth code and contacts
dn transfer example.com

# With auth code
dn transfer example.com --auth-code=ABC123XYZ
```

### DNS management

```bash
# View all DNS records
dn dns:get example.com

# Set a DNS record
dn dns:set example.com --type=A --name=@ --value=1.2.3.4 --ttl=3600

# Multiple values for the same record
dn dns:set example.com --type=A --name=@ --value=1.2.3.4 --value=5.6.7.8
```

Supported record types: `A`, `AAAA`, `ALIAS`, `CAA`, `CNAME`, `MX`, `NS`, `PTR`, `TXT`, `SRV`.

### Update contacts

```bash
# Interactive
dn contacts:set example.com

# Specify contact type
dn contacts:set example.com --type=admin --first-name=Jane --last-name=Doe --email=admin@example.com

# Opt out of automatic transfer lock after contact change
dn contacts:set example.com --transferlock-opt-out
```

Contact types: `owner`, `admin`, `tech`, `billing`.

### WHOIS privacy

```bash
dn privacy example.com on     # Enable privacy service
dn privacy example.com off    # Disclose contact info
dn privacy example.com redact # Redact contact info
```

### Transfer lock

```bash
dn transferlock example.com on
dn transferlock example.com off
```

## Command reference

| Command | Description |
|---|---|
| `dn configure` | Set up API credentials |
| `dn check <domain>...` | Check availability and pricing |
| `dn suggest <query>` | Get domain name suggestions |
| `dn info <domain>` | Domain details and status |
| `dn register <domain>` | Register a new domain |
| `dn renew <domain>` | Renew registration |
| `dn delete <domain>` | Delete a domain |
| `dn restore <domain>` | Restore a deleted domain |
| `dn transfer <domain>` | Transfer a domain in |
| `dn dns:get <domain>` | View DNS records |
| `dn dns:set <domain>` | Set DNS records |
| `dn contacts:set <domain>` | Update contact information |
| `dn privacy <domain> <on\|off\|redact>` | WHOIS privacy settings |
| `dn transferlock <domain> <on\|off>` | Transfer lock control |

## Shell completion

Generate completions for your shell:

```bash
# Bash
dn completion bash | sudo tee /etc/bash_completion.d/dn

# Zsh
dn completion zsh | sudo tee /usr/local/share/zsh/site-functions/_dn

# Fish
dn completion fish | tee ~/.config/fish/completions/dn.fish
```

## License

GPL-2.0-or-later
