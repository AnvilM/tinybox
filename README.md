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
    - Selecting outbounds with expressions
    - Multiple methods for outbounds testing

## Outbound selection

The `export` and `test` commands choose outbounds with a single expression language, passed only through the CLI.
The `--select` and `--exclude` flags are the global selection: they choose the outbounds that go into the final config.

| Flag                              | Meaning                                                                    |
|-----------------------------------|----------------------------------------------------------------------------|
| `--select EXPR`                   | Repeatable. Keep only outbounds matching `EXPR`. Several flags are ANDed.  |
| `--exclude EXPR`, `-e EXPR`       | Repeatable. Remove outbounds matching `EXPR`. Several flags are ORed.      |

The result is `select₁ & … & selectₙ & !(exclude₁ | … | excludeₘ)`. Without flags every outbound is selected.
Outbounds that the chosen core cannot run are always dropped; an expression cannot bring them back.

### Expressions

```
or     := and  ( '|' and )*
and    := unary ( '&' unary )*
unary  := '!' unary | '(' or ')' | cond
cond   := property op value ( ',' value )*
op     := '=' | '!=' | '~' | '!~'
```

| Construct               | Meaning                                                                  |
|-------------------------|--------------------------------------------------------------------------|
| `prop = a,b`            | the value equals `a` or `b`                                              |
| `prop != a,b`           | the value equals neither `a` nor `b`                                     |
| `prop ~ pattern`        | the value matches a glob pattern (`*` - any sequence, `?` - one char)    |
| `prop !~ pattern`       | the value does not match the pattern                                     |
| `a & b`, `a \| b`, `!a` | AND, OR, NOT; `( … )` groups. Priority: `!` > `&` > `\|`                 |

Properties (names are case-insensitive):

| Property      | Value                                                        | Applies to                         | Example                          |
|---------------|--------------------------------------------------------------|------------------------------------|----------------------------------|
| `type`        | `vless`, `shadowsocks` (alias `ss`), `urltest`               | all                                | `type=vless,ss`                  |
| `tag`         | any string                                                   | all                                | `tag~RU-*`                       |
| `server`      | server host (domain or IP)                                   | all                                | `server~*.example.com`           |
| `port`        | integer 1-65535, only `=` and `!=`                           | all                                | `port=443,8443`                  |
| `security`    | `reality`, `tls`                                             | vless with security                | `security=reality`               |
| `sni`         | server name of the security                                  | vless with security                | `sni~*.google.com`               |
| `fingerprint` | fingerprint of the security                                  | vless with a fingerprint           | `fingerprint=chrome`             |
| `transport`   | `http`, `ws`, `quic`, `grpc`, `httpupgrade`, `xhttp`         | vless with transport               | `transport=ws,xhttp`             |
| `flow`        | flow                                                         | vless with a flow                  | `flow=xtls-rprx-vision`          |
| `method`      | encryption method, e.g. `aes-256-gcm`                        | shadowsocks                        | `method~2022-*`                  |
| `plugin`      | `obfs-local`, `v2ray-plugin`                                 | shadowsocks with a plugin          | `plugin=v2ray-plugin`            |

`type` additionally accepts the alias `ss`. The value `urltest` only makes sense inside `--urltest` expressions: the global selection (`--select`, `--exclude`) runs before urltests are created, so it never sees them.

Values may be quoted with `'…'` or `"…"` (escapes: `\'`, `\"`, `\\`). An outbound without a value for a property never
matches `=` and `~`, so `!=` and `!~` do match it (`security!=tls` also selects shadowsocks and vless without security). Glob patterns do not expand type aliases (`type~ss` does not match `shadowsocks`).

Wrap expressions containing shell special characters (`| & ! ( ) * ?`) in single quotes.

### Examples

```bash
# only vless
tinybox export sub --select type=vless

# vless and shadowsocks, without tags starting with RU-
tinybox export sub --select type=vless,ss --exclude 'tag~RU-*'

# exclude by tag
tinybox export sub -e tag=test -e tag=old

# vless OR a specific tag
tinybox export sub --select 'type=vless | tag=my-vps'

# exclude ss, but keep a specific server
tinybox export sub --exclude 'type=ss & tag!=my-vps'

# everything except test
tinybox export sub --exclude tag=test
```

## Urltest groups

The `export` command can add any number of urltest groups to the config (sing-box: one urltest outbound per group;
xray: one balancer per group and a single observatory). One `--urltest` flag creates one group.

A urltest is an outbound like any other: it has a tag and the type `urltest`, and it can be selected by an expression
(`type=urltest`, `tag=auto`, `tag~fast-*`). Its parameters are read from the templates once, when the urltest is created,
and are stored in the urltest itself.

| Flag                         | Meaning                                                                     |
|------------------------------|-----------------------------------------------------------------------------|
| `--urltest SPEC`, `-u SPEC`  | `export` only. Repeatable. Add a urltest group described by `SPEC`.         |

```
SPEC := TAG ':' EXPR
```

- `SPEC` is split by the **first** colon: everything before it is the tag, everything after it is the expression
  (colons inside the expression are allowed: `auto:tag~srv:*`). The colon is required.
- `TAG` is trimmed. An empty tag (`:type=vless`) means "not specified".
- `EXPR` uses the same language as `--select`. An empty expression (`auto:`) takes every outbound of the group's pool
  (see below), including the urltests created before it.
- A group is built from the outbounds that already passed the global selection (`--select`, `--exclude`) and overrides.
  To exclude something inside a group, negate it in the expression: `auto:!(tag=test)`.
- Groups are placed in the config in the order of the flags. Urltest is not added with `--scheme`.
- Without `--urltest` flags no urltest is added.

All urltest groups share one template (`outbound_urltest` for sing-box, the observatory and balancer templates for xray).
Urltest parameters (`url`, `interval`, `tolerance`, ...) are set only there. They are read once when the urltests are
created and stored in each urltest. The outbound template (`outbound`) is not applied to urltests.

### Nested urltests

| Core     | Pool of group N                                                   |
|----------|-------------------------------------------------------------------|
| sing-box | regular outbounds + urltests of the groups 1…N-1                  |
| xray     | regular outbounds only (a balancer cannot be a member of another) |

- sing-box: a group can include a urltest created by an earlier flag. The order of the `--urltest` flags matters.
  An empty expression (`b:`) takes the whole pool, including earlier urltests; an expression with negation
  (`type!=ss`) also catches them.
- A group cannot include itself: it selects only from the urltests created before it.
- xray: nesting is not supported, `type=urltest` and urltest tags in expressions select nothing
  (an empty result is the usual "matched no outbounds" error).

### Tags

The tag of a group is, in order of priority: the tag from `SPEC`, the `tag` field of the template, `urltest`.
When there is more than one group, give each of them its own tag.

### Errors

| Situation                                                       | Error                                                                    |
|-----------------------------------------------------------------|--------------------------------------------------------------------------|
| `SPEC` has no colon                                             | `Urltest group must have the form TAG:EXPRESSION, got '...'`             |
| No outbound got into a group                                    | `Urltest group '...' matched no outbounds`                               |
| Two urltests get the same tag, or a tag equals an outbound's tag | `Urltest tag '...' is used more than once. Specify a unique tag: ...`   |
| Invalid expression                                              | the same syntax errors as for `--select`, with a caret                   |

### Examples

```bash
# one urltest from everything, tag from the template (or 'urltest')
tinybox export sub --urltest ':'

# one urltest with the tag 'auto' from everything
tinybox export sub -u 'auto:'

# two urltests: vless and ss separately
tinybox export sub -u 'auto-vless:type=vless' -u 'auto-ss:type=ss'

# everything except test globally, urltest 'fast' from fast-* tags, 'proxy' from vless without RU-
tinybox export sub --exclude tag=test -u 'fast:tag~fast-*' -u 'proxy:type=vless & tag!~RU-*'

# xray: two balancers
tinybox export sub --xray -u 'a:type=vless' -u 'b:type=ss'

# nesting (sing-box): 'auto' consists of vless outbounds and of the urltest 'fast'
tinybox export sub -u 'fast:tag~fast-*' -u 'auto:type=vless | tag=fast'

# nesting by type (sing-box): 'all' consists of the urltests 'a' and 'b'
tinybox export sub -u 'a:type=vless' -u 'b:type=ss' -u 'all:type=urltest'
```

Wrap expressions containing shell special characters (`| & ! ( ) * ?`) in single quotes, and the whole `SPEC` as well.

### What changed

| Before                                                                  | Now                                         |
|-------------------------------------------------------------------------|---------------------------------------------|
| `--outboundType vless`                                                  | `--select type=vless`                       |
| `--excludeOutboundType ss`                                              | `--exclude type=ss`                         |
| `--excludeOutbound a b`, `-e a`                                         | `--exclude tag=a,b`, `-e tag=a`             |
| `--outboundType vless --outboundTypeExcept my-vps`                      | `--select 'type=vless \| tag=my-vps'`       |
| `--excludeOutboundType ss --excludeOutboundTypeExcept my-vps`           | `--exclude 'type=ss & tag!=my-vps'`         |
| `--exceptOutbound my-vps`                                               | removed, write the condition explicitly     |
| `--urltest` / `-u` (a flag, one urltest from everything)                | `--urltest ':'` / `-u ':'`                  |
| `--urltestSelect EXPR`                                                  | `--urltest ':EXPR'`                         |
| `--urltestExclude EXPR`                                                 | `--urltest ':!(EXPR)'`                      |
| `--urltestSelect A --urltestExclude B`                                  | `--urltest ':A & !(B)'`                     |
| `--detourOutbound TAG`                                                  | `--detour 'TAG:'` / `-d 'TAG:'`             |

Selection by country (`--countryCode`, `--excludeCountryCode`, `--*OnlyAvailable`, `--countryOutboundIpFallback`) has been
removed and is temporarily unavailable.

## Detour

The `export` and `test` commands can set a detour (the outbound the traffic goes through) to the outbounds chosen by an
expression. Different outbounds can get different detours: one `--detour` flag is one rule.

| Flag                          | Meaning                                                                        |
|-------------------------------|--------------------------------------------------------------------------------|
| `--detour SPEC`, `-d SPEC`    | `export` and `test`. Repeatable. Set a detour by the rule described by `SPEC`. |

```
SPEC       := DETOUR_TAG ':' EXPR
DETOUR_TAG := tag of an existing outbound (not empty), as is or in quotes '...' / "..."
EXPR       := expression of the selection language; empty - all outbounds
```

- If the first non-space character of `SPEC` is a quote, `DETOUR_TAG` is the quoted string (escapes: `\'`, `\"`, `\\`),
  then optional spaces and a **required** colon. Everything after the colon is the expression.
- Otherwise `SPEC` is split by the **first** colon: everything before it is `DETOUR_TAG`, everything after it is the
  expression (colons inside the expression are allowed).
- Quotes are needed for tags that contain a colon: `-d "'RU: 1':type=vless"`.
- `DETOUR_TAG` cannot be empty. `EXPR` uses the same language as `--select`.

### Rules

- The target is found by tag among the final set of outbounds: in `export` these are the regular outbounds and the
  urltests (sing-box: a urltest can be the target), in `test` the outbounds after the global selection.
- The subjects are chosen by the expression from the same set. Only vless and shadowsocks get a detour; the rest
  (urltests) are skipped. Urltest members keep the detour they got as ordinary outbounds.
- An outbound never gets itself as the detour.
- The first rule wins (in the order of the flags): an outbound that already has a detour is skipped by the next rules.
  So "specific rules, then a general one" works: `-d 'a:tag~RU-*' -d 'b:'`.
- A rule that matched no outbounds is an error. The check is made before the skips, so a general rule whose outbounds
  all got a detour from earlier rules is not an error.
- xray: a urltest cannot be the target (`dialerProxy` accepts only an outbound, not a balancer).
- Loops are not checked.
- `--scheme` exports outbounds without detour and urltest.
- `test` checks outbounds in chunks: if the target gets into a different chunk than its subject, the target is absent
  in the test config of that chunk.
- Without `--detour` flags no detour is set.

### Errors

| Situation                                          | Error                                                                                              |
|----------------------------------------------------|----------------------------------------------------------------------------------------------------|
| No colon, empty tag, unclosed quote                | `Detour rule must have the form DETOUR_TAG:EXPRESSION, got '...'`                                  |
| No outbound with the tag `DETOUR_TAG`              | `Outbound with tag '...' not found (detour rule '...'). Try to remove selection flags`             |
| The expression matched no outbounds                | `Detour rule '...' matched no outbounds`                                                           |
| xray, the target is a urltest                      | `Detour outbound '...' is a urltest and cannot be used as detour in xray`                          |
| Invalid expression                                 | the same syntax errors as for `--select`, with a caret                                             |

### Examples

```bash
# set the detour 'proxy-1' to all vless, ss stay as they are
tinybox export sub -d 'proxy-1:type=vless'

# different detours for different groups
tinybox export sub -d 'proxy-1:tag~RU-*' -d 'proxy-2:tag~DE-*'

# a specific rule, then a general one: 'proxy-3' for all the rest
tinybox export sub -d 'proxy-1:tag~RU-*' -d 'proxy-3:'

# a tag with a colon
tinybox export sub -d "'RU: 1':type=vless"

# detour to a urltest (sing-box)
tinybox export sub -u 'auto:type=vless' -d 'auto:type=ss'

# check latency through a detour
tinybox test sub -d 'proxy-1:'
```

Wrap expressions containing shell special characters (`| & ! ( ) * ?`) in single quotes, and the whole `SPEC` as well.

## Supported protocols

- Vless
- Shadowsocks

## Supported transport

- WebSocket
- XHTTP (xray only)

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
  "common": {
    "dns": {
      "resolve_timeout": 10,
      "endpoints": [
        "https://8.8.8.8/resolve",
        "https://1.1.1.1/dns-query"
      ]
    }
  },
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

### `common`

Common settings shared across Tinybox features.

#### `dns`

Controls how domain names are resolved.

| Parameter         | Type       | Default                                                    | Description                                                                                                                                                                                                                                  |
|-------------------|------------|------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `resolve_timeout` | `integer`  | `10`                                                       | Timeout in seconds to wait for a response from a DNS resolver when resolving each individual domain.                                                                                                                                         |
| `endpoints`       | `string[]` | `["https://8.8.8.8/resolve", "https://1.1.1.1/dns-query"]` | List of DoH endpoint URLs used to resolve domains. Endpoints are queried in order, from first to last: if an endpoint does not return an answer, the next one is tried; once an answer is received, the remaining endpoints are not queried. |

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
| `outbound_urltest` | `string` | `~/.config/tinybox/templates/outbound_urltest.json` | Path to the URLTest outbound template, common to all urltest groups. It is read once when the urltests are created and its parameters are stored in each urltest. For each group Tinybox sets the `tag` (the group tag from `--urltest`, otherwise the `tag` field of this template, otherwise `urltest`) and creates the `outbounds` field (if missing), populating it with the tags of the group members (which can be other urltests). Keys with an empty string or an empty array value (except `outbounds`) are not written to the config. If this template is not provided, Tinybox will generate an invalid outbound. |
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
| `observatory` | `string` | `~/.config/tinybox/templates/xray/observatory.json` | Path to the Observatory template (read once when the urltests are created).                          |
| `balancer`    | `string` | `~/.config/tinybox/templates/xray/balancer.json`    | Path to the Balancer template (read once when the urltests are created).                             |

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