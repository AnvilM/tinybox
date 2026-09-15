<div align="center">
    <h1 align="center">
        <br>
	    <img src="assets/icons/logo.svg" width=128 alt="logo">
        <br>
        Tinybox
    </h1>


[![php-version-shield]][php-version-link]
[![][github-release-shield]][github-release-link]
[![status-shield]][status-link]

[![last-commit-shield]][last-commit-link]
[![][github-release-date-shield]][github-release-date-link]
[![github-license-shield]][github-license-link]

</div>

## Overview

Tinybox is a command-line utility designed for managing sing-box subscriptions and configurations.  
It allows you to store subscription links, update their content, and generate sing-box configurations based on your own
templates.

## Features

- **Subscription Management**
    - Fetch subscriptions from url
    - Store subscriptions
    - Update subscriptions
    - Testing subscription outbounds (only sing-box outbounds)
    - Generate sing-box/xray configs from subscriptions

- **Other Features**
    - HWID spoofing
    - Vless UUID and Shadowsocks password spoofing
    - Filtering outbounds
    - Multiple methods for outbounds testing

## Supported protocols

- Vless
- Shadowsocks

## Supported transport

- WebSocket
- XHTTP (sing-box only)

## Supported security

- Reality
- TLS

> Other protocols and transport will be added later.

## Supported Platforms

Tinybox is supported on:

- **Linux x86_64** — full functionality.

> Other platforms and architectures are not supported.

## Dependencies

Tinybox requires the following software to function properly:

- **sing-box** — required sing-box cli. (only for outbounds testing)

## Configuration

The configuration file is stored at: `~/.config/tinybox/config.json`

If the configuration file is missing or some parameters are not specified, default values are used.

```json
{
  "subscriptions_list": "~/.local/share/tinybox/subscriptions.json",
  "groups_list": "~/.local/share/tinybox/groups.json",
  "outbounds_list": "~/.local/share/tinybox/outbounds.json",
  "config_save_path": "/etc/sing-box/config.json",
  "subscriptions": {
    "timeout": 10,
    "useragent": "tinybox/0.1",
    "hwid": null
  },
  "sing_box": {
    "binary": "sing-box",
    "templates": {
      "outbound": "~/.config/tinybox/templates/outbound.json",
      "outbound_urltest": "~/.config/tinybox/templates/outbound_urltest.json",
      "config": "~/.config/tinybox/templates/config.json"
    },
    "outbound_test": {
      "sing_box_config": "~/.local/share/tinybox/outbound_test/sing-box_config.json",
      "max_parallel_requests": 3,
      "templates": {
        "outbound": "~/.config/tinybox/templates/outbound.json",
        "config": "~/.config/tinybox/templates/config.json"
      },
      "fetch_ip": {
        "geoip_database": "~/.local/share/tinybox/geoip.mmdb",
        "url": "https://ifconfig.me/ip"
      },
      "latency": {
        "url": "https://google.com",
        "method": "proxy_get"
      },
      "timeout": 10
    }
  },
  "xray": {
    "templates": {
      "outbound": "~/.config/tinybox/templates/xray/outbound.json",
      "config": "~/.config/tinybox/templates/xray/config.json",
      "observatory": "~/.config/tinybox/templates/xray/observatory.json",
      "balancer": "~/.config/tinybox/templates/xray/balancer.json"
    }
  }
}
```

> [!WARNING]
> Tinybox does not create files automatically, you need to create the necessary files yourself before using it.

## Configuration Description

The configuration file defines paths, templates, and behavior for managing subscriptions, groups, outbounds, and
generating Sing-box / Xray configurations.

### Top-level Paths

| Parameter            | Type     | Default                                     | Description                                                   |
|----------------------|----------|---------------------------------------------|---------------------------------------------------------------|
| `subscriptions_list` | `string` | `~/.local/share/tinybox/subscriptions.json` | Path to the file that stores subscription data.               |
| `groups_list`        | `string` | `~/.local/share/tinybox/groups.json`        | Path to the file that stores groups.                          |
| `outbounds_list`     | `string` | `~/.local/share/tinybox/outbounds.json`     | Path to the file that stores all outbounds.                   |
| `config_save_path`   | `string` | `/etc/sing-box/config.json`                 | Path where the final Sing-box or Xray configuration is saved. |

### `subscriptions`

Controls how subscription URLs are fetched.

| Parameter   | Type             | Default         | Description                                                                                           |
|-------------|------------------|-----------------|-------------------------------------------------------------------------------------------------------|
| `timeout`   | `integer`        | `10`            | Timeout in seconds when fetching schemes from a subscription URL.                                     |
| `useragent` | `string`         | `"tinybox/0.1"` | User-Agent header sent when requesting subscription schemes.                                          |
| `hwid`      | `string \| null` | `null`          | Value passed in the `X-HWID` header when importing a subscription. If `null`, the header is not sent. |

### `sing_box`

Settings related to Sing-box binary and templates.

#### Binary

| Parameter | Type     | Default      | Description                              |
|-----------|----------|--------------|------------------------------------------|
| `binary`  | `string` | `"sing-box"` | Path or name of the Sing-box executable. |

#### Templates

| Parameter          | Type     | Default                                             | Description                                                                                                                                                                                                                 |
|--------------------|----------|-----------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `outbound`         | `string` | `~/.config/tinybox/templates/outbound.json`         | Path to the outbound template file.                                                                                                                                                                                         |
| `outbound_urltest` | `string` | `~/.config/tinybox/templates/outbound_urltest.json` | Path to the URLTest outbound template. Tinybox will create the `outbounds` field (if missing) and populate it with the required outbound tags. If this template is not provided, Tinybox will generate an invalid outbound. |
| `config`           | `string` | `~/.config/tinybox/templates/config.json`           | Path to the main Sing-box configuration template. Existing outbounds in the template are preserved; new ones are appended.                                                                                                  |

#### `outbound_test`

Configuration used when testing outbounds.

| Parameter               | Type      | Default                                                     | Description                                                                               |
|-------------------------|-----------|-------------------------------------------------------------|-------------------------------------------------------------------------------------------|
| `sing_box_config`       | `string`  | `~/.local/share/tinybox/outbound_test/sing-box_config.json` | Path where the temporary Sing-box config used for testing is saved.                       |
| `max_parallel_requests` | `integer` | `3`                                                         | Maximum number of outbound tests that can run in parallel.                                |
| `timeout`               | `integer` | `10`                                                        | Timeout (in seconds) for testing a group of outbounds limited by `max_parallel_requests`. |

##### Templates (inside `outbound_test`)

| Parameter  | Type     | Default                                     | Description                                                   |
|------------|----------|---------------------------------------------|---------------------------------------------------------------|
| `outbound` | `string` | `~/.config/tinybox/templates/outbound.json` | Path to the outbound template used in the test configuration. |
| `config`   | `string` | `~/.config/tinybox/templates/config.json`   | Path to the config template used for testing.                 |

##### `fetch_ip`

| Parameter        | Type     | Default                             | Description                                                                                                               |
|------------------|----------|-------------------------------------|---------------------------------------------------------------------------------------------------------------------------|
| `geoip_database` | `string` | `~/.local/share/tinybox/geoip.mmdb` | Path to the MMDB database used for GeoIP lookups.                                                                         |
| `url`            | `string` | `"https://ifconfig.me/ip"`          | URL used to obtain the current IP address. Any alternative URL must return the IP in the same format as `ifconfig.me/ip`. |

##### `latency`

| Parameter | Type     | Default                | Description                                                                                                                                 |
|-----------|----------|------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| `url`     | `string` | `"https://google.com"` | Target URL used when measuring latency with the `proxy_get` method.                                                                         |
| `method`  | `string` | `"proxy_get"`          | Latency testing method. Available values:<br>• `"proxy_get"` — sends a GET request through the outbound<br>• `"tcp_ping"` — simple TCP ping |

### `xray`

Templates used when generating Xray configurations.

| Parameter     | Type     | Default                                             | Description                                                                                            |
|---------------|----------|-----------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| `outbound`    | `string` | `~/.config/tinybox/templates/xray/outbound.json`    | Path to the Xray outbound template.                                                                    |
| `config`      | `string` | `~/.config/tinybox/templates/xray/config.json`      | Path to the main Xray configuration template. Existing outbounds are preserved; new ones are appended. |
| `observatory` | `string` | `~/.config/tinybox/templates/xray/observatory.json` | Path to the Observatory template.                                                                      |
| `balancer`    | `string` | `~/.config/tinybox/templates/xray/balancer.json`    | Path to the Balancer template.                                                                         |

## License

The project is distributed under the MIT License. For details, refer to the [LICENSE][github-license-link] file.

<!-- LINKS -->

[sing-box-docs-config-link]: https://sing-box.sagernet.org/configuration

[sing-box-docs-outbound-link]: https://sing-box.sagernet.org/configuration/outbound

[sing-box-docs-urltest-outbound-link]: https://sing-box.sagernet.org/configuration/outbound/urltest

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

[php-version-link]: https://github.com/AnvilM/tinybox/

[php-version-shield]: https://img.shields.io/badge/PHP-8.4-blue?logo=php&labelColor=black&style=flat-square

