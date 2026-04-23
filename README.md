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
    - Store subscriptions
    - Update subscriptions
    - Apply subscriptions

- **Configuration Management**
    - Generate configurations
    - Store configurations
    - Apply configurations

- **Other Features**
    - Filtering outbounds
    - Multiple methods for outbounds testing

## Supported protocols

- Vless
- Shadowsocks

> Other protocols will be added later.

## Supported Platforms

Tinybox is supported on:

- **Linux x86_64** — full functionality.

> Other platforms and architectures are not supported.

## Dependencies

Tinybox requires the following software to function properly:

- **sing-box** — required sing-box cli.
- **sudo** — required for operations that need elevated privileges.

> [!NOTE]
> For tinybox to work correctly, you need to create a systemd service for sing-box. (In most cases, it is created
> automatically when installing sing-box.) You can check whether this service exists using the command
> `systemctl status sing-box`

## Configuration

The configuration file is stored at: `~/.config/tinybox/config.json`

If the configuration file is missing or some parameters are not specified, default values are used.

```json
{
  "subscriptions_list": "~/.local/share/tinybox/subscriptions.json",
  "groups_list": "~/.local/share/tinybox/groups.json",
  "schemes_list": "~/.local/share/tinybox/schemes.json",
  "subscriptions": {
    "timeout": 10,
    "useragent": "tinybox/0.1",
    "hwid": null
  },
  "sing_box": {
    "binary": "sing-box",
    "default_config_path": "/etc/sing-box/config.json",
    "systemd_service_name": "sing-box",
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
  }
}
```

> [!WARNING]
> Tinybox does not create files automatically, you need to create the necessary files yourself before using it.

## Configuration Description

The tinybox configuration file is a JSON object that fully defines the application's behavior when working with
subscriptions, groups, schemes, and integration with **sing-box**. All paths are specified in a POSIX-compatible
format (the \~ symbol is supported for the user's home directory).

The configuration is divided into two main levels:

- **Root parameters** — paths to the main data storage files.
- **sing_box section** — settings for integration with the sing-box core, including template paths, systemd service
  management, and the outbound testing system.

### Root Parameters

| Parameter          | Type   | Description                                                                |
|--------------------|--------|----------------------------------------------------------------------------|
| subscriptions_list | string | Path to the JSON file that stores the list of all subscriptions (sources). |
| groups_list        | string | Path to the JSON file containing the list of groups.                       |
| schemes_list       | string | Path to the JSON file containing all available schemes (outbound schemes). |

These files serve as the primary state storage for the application and are automatically created or updated by tinybox.

### subscriptions Section

| Parameter | Type         | Description                                                                                                                                                     |
|-----------|--------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| timeout   | int          | Timeout in seconds for fetch schemes from subscription url                                                                                                      |
| useragent | string       | The user agent to be passed when requesting schemes.                                                                                                            |
| hwid      | string\|null | If specified, tinybox will pass the specified value in the X-HWID header when importing a subscription. If not specified, the X-HWID header will not be passed. |

### sing_box Section

#### Basic Settings

| Parameter            | Type   | Description                                                                                 |
|----------------------|--------|---------------------------------------------------------------------------------------------|
| binary               | string | Name of the sing-box executable (must be available in $PATH).                               |
| default_config_path  | string | Path to the main sing-box configuration file used by the systemd service.                   |
| systemd_service_name | string | Name of the systemd service (default: sing-box) that tinybox controls (start/stop/restart). |

#### Configuration Templates (templates)

tinybox uses JSON template files to generate working sing-box configurations. The parameters below contain **paths to
these template files**. When applying a scheme, the necessary fields are overwritten on top of the loaded template.

| Parameter        | Type   | Description                                                    |
|------------------|--------|----------------------------------------------------------------|
| outbound         | string | Path to the base template for a single outbound.               |
| outbound_urltest | string | Path to the template for an outbound of type urltest.          |
| config           | string | Path to the full template for the main sing-box configuration. |

#### Outbound Testing Settings (outbound_test)

This block is responsible for automatic testing of proxy server performance and availability.

**General parameters:**

- sing_box_config — path to the temporary configuration file created specifically for running tests.
- max_parallel_requests — maximum number of parallel requests/tests.
- timeout - Timeout for fetch ip and latency test

**Test templates:**

The parameters below are **paths to template files** used during test configuration generation:

- outbound — path to the outbound template.
- config — path to the full sing-box configuration template for tests.

**Real IP and Geolocation Determination (fetch_ip):**

| Parameter      | Type   | Description                                                                               |
|----------------|--------|-------------------------------------------------------------------------------------------|
| geoip_database | string | Path to the GeoIP2 .mmdb database (MaxMind), used to determine location by IP.            |
| url            | string | HTTP endpoint that must return **only** the IP address in the response body (plain text). |

**Latency Testing (latency):**

| Parameter | Type   | Description                                                                                                                                                                                 |
|-----------|--------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| url       | string | Target URL for the test (used with the proxy_get method).                                                                                                                                   |
| method    | string | Testing method. Supported values: • proxy_get — performs a GET request through the outbound to the specified URL. • tcp_ping — direct TCP ping to the IP address specified in the outbound. |

### Recommendations

- All paths may use \~ to represent the home directory.
- Template files must contain valid JSON compatible with sing-box.
- When generating a configuration, tinybox performs a deep merge of the template and scheme data.
- For correct test operation, it is recommended to use stable and fast endpoints.

This structure provides high flexibility for managing multiple subscriptions and sing-box schemes within a single
utility.

### Template Purpose

`config.json`

The main template for the complete sing-box configuration.
It acts as the structural skeleton of the final config file. Generated outbound blocks and other dynamic elements are
inserted into this template  
Reference documentation: [Configuration Structure][sing-box-docs-config-link]

`outbound.json`

Template for a single outbound object.
Used to generate individual outbound entries based on subscription data.  
Reference documentation: [Outbound][sing-box-docs-outbound-link]

`outbound_urltest.json`

Template for an outbound group of type urltest.
Used to create a group that automatically selects the best server based on connectivity testing.  
Reference documentation: [URLTest outbound][sing-box-docs-urltest-outbound-link]

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

