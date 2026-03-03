<div align="center">
    <h1 align="center">
        <br>
	    <img src="assets/icons/logo.svg" width=128 alt="logo">
        <br>
        Tinybox
    </h1>


[![php-version-shield]][php-version-link]
[![phpstan-shield]][phpstan-link]
[![][github-release-shield]][github-release-link]

[![status-shield]][status-link]
[![last-commit-shield]][last-commit-link]
[![][github-release-date-shield]][github-release-date-link]
[![github-license-shield]][github-license-link]

</div>

# Tinybox

## Overview

Tinybox is a command-line utility designed for managing sing-box subscriptions and configurations.  
It allows you to store subscription links, update their content, and generate sing-box configurations based on your own
templates.

## Features

- **Subscription Management**
    - Store subscriptions
    - Update subscriptions
    - Apply subscriptions

- **Configuration Management**
    - Generate configurations
    - Store configurations
    - Apply configurations

## Commands

### tinybox subscription:list

**Description**

Displays the list of added subscriptions.

**Usage example**

```bash
tinybox subscription:list
```

### tinybox subscription:update

**Description**

Updates existing subscriptions.

If the `subscriptionName` argument is not provided — all subscriptions are updated.  
If the argument is provided — only the specified subscription is updated.

**Arguments**

`subscriptionName` — subscription name (optional).

**Flags**

`-a`, `--apply` — apply the subscription after updating.  
If a subscription is specified, only that one is applied.  
If no subscription is specified, you must explicitly provide the name of the subscription to apply, for example:  
`tinybox subscription:update -a subName` — all subscriptions will be updated, but only `subName` will be applied.

`-s`, `--systemd` — when applying, use the sing-box systemd service instead of launching via the binary.

**Examples**

```bash
# Update subscription "sub"
tinybox subscription:update sub

# Update all subscriptions and apply subscription "mySub" using sing-box systemd service
tinybox subscription:update -a mySub -s
```

### tinybox subscription:apply

**Description**

Applies a specific configuration.

**Arguments**

`subscriptionName` — name of the subscription to apply

**Flags**

`-u`, `--update` — update the subscription before applying.

`-s`, `--systemd` — use the sing-box systemd service instead of launching via the binary.  
sing-box can be started directly as a binary or as a systemd service; this flag forces systemd mode.

**Examples**

```bash
# Apply configuration "mySub"
tinybox subscription:apply mySub
```

### tinybox config:list

**Description**

Shows the list of generated configuration files.

**Example**

```bash
tinybox config:list
```

## Global Flags

`-d`, `--debug` — enables debug log output.

## Configuration

The configuration file is stored at: `~/.config/tinybox/config.json`

If the configuration file is missing or some parameters are not specified, default values are used.

```json
{
  "subscriptions_list": "/home/user/.local/share/tinybox/subscriptions.json",
  "generated_configs_dir": "/home/user/.local/share/tinybox/configs/",
  "sing_box": {
    "binary": "sing-box",
    "default_config_path": "/etc/sing-box/config.json",
    "systemd_service_name": "sing-box",
    "templates": {
      "outbound": "/home/user/.config/tinybox/templates/outbound.json",
      "outbound_urltest": "/home/user/.config/tinybox/templates/outbound_urltest.json",
      "config": "/home/user/.config/tinybox/templates/config.json"
    }
  }
}
```

## Templates

Templates are used to generate the final sing-box configuration.

The quality of the resulting configuration fully depends on the correctness of the templates.  
If a template contains syntax errors or has an incorrect structure, the generated configuration will also be invalid and
may fail to start.

**Default locations**

Paths can be overridden in `~/.config/tinybox/config.json`

`~/.config/tinybox/templates/config.json` — main template for the entire configuration

`~/.config/tinybox/templates/outbound.json` — template for a single outbound

`~/.config/tinybox/templates/outbound_urltest.json` — template for an urltest group

## License

The project is distributed under the MIT License. For details, refer to the [LICENSE][github-license-link] file.

<!-- LINKS -->

[github-release-link]: https://github.com/anvilm/tinybox/releases

[github-release-shield]: https://img.shields.io/github/v/release/anvilm/tinybox?style=flat-square&sort=semver&logo=github&labelColor=black

[github-release-date-link]: https://github.com/anvilm/tinybox/releases

[github-release-date-shield]: https://img.shields.io/github/release-date/anvilm/tinybox?labelColor=black&style=flat-square

[github-license-link]: LICENSE

[github-license-shield]: https://img.shields.io/github/license/anvilm/tinybox?color=white&labelColor=black&style=flat-square

[status-link]: https://github.com/AnvilM/tinybox/

[status-shield]: https://img.shields.io/badge/status-active-brightgreen?labelColor=black&style=flat-square

[last-commit-link]: https://github.com/AnvilM/tinybox/commits

[last-commit-shield]: https://img.shields.io/github/last-commit/anvilm/tinybox?labelColor=black&style=flat-square

[phpstan-link]: https://github.com/AnvilM/tinybox/

[phpstan-shield]: https://img.shields.io/badge/PHPStan-Level%20max-blue?logo=php&labelColor=black&style=flat-square

[php-version-link]: https://github.com/AnvilM/tinybox/

[php-version-shield]: https://img.shields.io/badge/PHP-8.4-blue?logo=php&labelColor=black&style=flat-square