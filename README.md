<div align="center">
    <h1>Slim · RoadRunner Boilerplate</h1>
    <p>PHP Boilerplate Powered by <b>Slim Framework</b> and <b>RoadRunner</b></p>

[![php-version-shield]][php-version-link]
[![phpstan-shield]][phpstan-link]
[![][github-release-shield]][github-release-link]

[![status-shield]][status-link]
[![last-commit-shield]][last-commit-link]
[![][github-release-date-shield]][github-release-date-link]
[![github-license-shield]][github-license-link]

</div>

## Project Overview

This template is designed for the development of web applications utilizing
the [Slim Framework](https://www.slimframework.com/) and the high-performance [RoadRunner](https://roadrunner.dev/)
server. The project incorporates tools for building scalable PHP applications, including a dependency injection
container, logging system, Object-Relational Mapping (ORM), database migrations, and a Command Line Interface (CLI).

## Core Components

- **Container**: [PHP-DI](https://php-di.org/)
- **HTTP**: [nyholm/psr7](https://github.com/Nyholm/psr7)
- **Logger**: [Monolog](https://seldaek.github.io/monolog/)
- **ORM**: [CycleORM](https://cycle-orm.dev/)
- **Migrations**: [Cycle Migrations](https://cycle-orm.dev/docs/database-migrations/)
- **CLI**: [Symfony Console](https://symfony.com/doc/current/components/console.html)
- **Testing**: [Pest](https://pestphp.com/)
- **Static Analysis**: [PHPStan](https://phpstan.org/)

## Getting Started

### Installation

#### Via Composer

Create a new project using Composer:

```bash
composer create-project anvilm/slim-rr-boilerplate my-project
```

#### Via GitHub

Clone the repository and install dependencies:

```bash
git clone https://github.com/anvilm/slim-rr-boilerplate.git my-project
cd my-project
composer install
```

### Directory Structure

```
.
├── app/                          # Core application logic and infrastructure
│   ├── Bootstrappers/            # Classes for initializing application components
│   ├── Commands/                 # Custom CLI commands
│   ├── Config/                   # Configuration files
│   ├── Endpoints/                # Definitions of HTTP endpoints
│   ├── Providers/                # Dependency injection container providers
│   │   ├── ApplicationProviders/ # Providers for application functionality
│   │   └── Providers/            # Custom providers
│   └── Kernel.php                # Application entry point and bootstrap management
├── bin/                          # CLI entry point
│   └── console.php               # Entry point for Symfony Console
├── database/                     # Migrations and SQLite database files
├── logs/                         # Application logs
├── src/                          # Source code for custom logic
├── tests/                        # Pest tests
└── index.php                     # Entry point for RoadRunner
```

### Directory Organization

The boilerplate is structured to separate infrastructural logic from user-defined code:

- **Directory `app/`**: Contains the core infrastructure of the application,
  including [configurations](#configuration), [providers](#providers), [endpoints](#endpoints),
  and [bootstrappers](#bootstrappers). This directory is intended for foundational application setup and operation.
- **Directory `src/`**: Designated for user-defined source code, where developers can implement the primary business
  logic of the application.

### Configuration

Application configurations are organized in the `app/Config/` directory and provide type-safe access to settings via
classes with static methods. For further details, refer to the [Configuration](#configuration-1) section.

#### Environment Variables

Environment variables are managed using the [oscarotero/env](https://github.com/oscarotero/env) library. These variables
are read directly from the system, not from a `.env` file, and are intended for use within configuration classes.

Predefined environment variables:

- **APP_ENV**: Defines the application environment (e.g., `production`, `development`), affecting logging levels.
- **APP_DEBUG**: Enables or disables debug mode, influencing the display of detailed error information in Slim.

#### Application Configuration

The `ApplicationConfig` class provides the following parameters:

- **baseDir**: The root directory of the project.
- **appEnv**: The application environment, determined by the `APP_ENV` variable. Available environments are listed in
  the `ApplicationEnvironmentEnum` enumeration. To add a new environment, update this enumeration and the
  `ApplicationConfig` class.
- **appDebug**: Debug mode, determined by the `APP_DEBUG` variable. Enables detailed error messages.

#### Database Configuration

The `DatabaseConfig` class defines settings for CycleORM. By default, it is configured for SQLite, but other database
management systems (e.g., MySQL, PostgreSQL) are supported with appropriate configuration.

#### Logging Configuration

The `LoggerConfig` class configures logging parameters using Monolog. It includes the path to log files (in the `logs/`
directory) and the logging level, which depends on the `appEnv` value.

#### Migration Configuration

Database migration settings are defined for Cycle Migrations. Migrations are stored in the `database/migrations/`
directory and managed via [CLI commands](#commands).

## Architectural Concepts

### Configuration

Configurations are stored in the `app/Config/` directory. Each configuration is implemented as a class with static
methods, ensuring type-safe access to settings.

Example:

```php
namespace Application\Config\ApplicationConfig;

use function Env\env;

final readonly class ApplicationConfig
{
    public static function baseDir(): string
    {
        return dirname(__DIR__, 3);
    }
}
```

### Endpoints

HTTP endpoints are defined in the `app/Endpoints/` directory. Routing is handled using the standard Slim Framework
mechanism. Each endpoint class must implement the `EndpointInterface` and be registered in
the [EndpointBootstrapper](#bootstrappers).

Example of creating an endpoint:

```php
namespace App\Endpoints;

final readonly class ApiEndpoints implements EndpointInterface
{
    public static function register(App $app): void
    {
        $app->get('/', function (RequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('Example response');
            return $response;
        });
    }
}
```

Example of registration in [EndpointBootstrapper](#bootstrappers):

```php
private static array $endpoints = [
    \App\Endpoints\ApiEndpoints::class,
];
```

### Providers

Service providers, located in the `app/Providers/` directory, are responsible for registering dependencies in the PHP-DI
container, making them accessible to the application. Providers are categorized as follows:

- `ApplicationProviders/`: Providers essential for application functionality.
- `Providers/`: Custom providers for specific logic.

Each provider must implement the `ProviderInterface` and be registered in the [ProvidersBootstrapper](#bootstrappers).

Example of creating a provider:

```php
namespace App\Providers;

final readonly class DBALProvider implements ProviderInterface
{
    public static function register(): array
    {
        return [DatabaseManager::class => new DatabaseManager(
            new CycleDatabaseConfig(
                DatabaseConfig::config()
            )
        )];
    }
}
```

Example of registration in [ProvidersBootstrapper](#bootstrappers):

```php
private static array $appProviders = [
    \App\Providers\LoggerProvider::class,
    \App\Providers\DBALProvider::class,
];

private static array $providers = [
    // Custom providers
];
```

### Bootstrappers

Bootstrapper classes in the `app/Bootstrappers/` directory initialize key application components. The primary
bootstrappers are:

- `ApplicationBootstrapper`: Initializes the Slim application.
- `ContainerBootstrapper`: Configures the PHP-DI container.
- `ProvidersBootstrapper`: Registers [providers](#providers).
- `EndpointBootstrapper`: Registers [endpoints](#endpoints).
- `CommandsBootstrapper`: Registers [CLI commands](#commands).

The `Kernel.php` file manages the initialization process.

### Commands

CLI commands are defined in the `app/Commands/` directory. Each command must be registered in
the [CommandsBootstrapper](#bootstrappers).

The Symfony Console library is used for CLI commands.

Example of registration in [CommandsBootstrapper](#bootstrappers):

```php
private static array $commands = [
    \App\Commands\MigrationGenerateCommand::class,
    \App\Commands\MigrationUpCommand::class,
];
```

Available commands:

- `migration:generate`: Generates a migration template in the `database/migrations/` directory.
- `migration:up`: Executes pending migrations.

Running commands:

```bash
php bin/console {commandName}
```

## License

The project is distributed under the MIT License. For details, refer to the [LICENSE](LICENSE) file.

<!-- LINKS -->

[github-release-link]: https://github.com/anvilm/slim-rr-boilerplate/releases

[github-release-shield]: https://img.shields.io/github/v/release/anvilm/slim-rr-boilerplate?style=flat-square&sort=semver&logo=github&labelColor=black

[github-release-date-link]: https://github.com/anvilm/slim-rr-boilerplate/releases

[github-release-date-shield]: https://img.shields.io/github/release-date/anvilm/slim-rr-boilerplate?labelColor=black&style=flat-square

[github-license-link]: https://github.com/anvilm/slim-rr-boilerplate/blob/master/LICENSE

[github-license-shield]: https://img.shields.io/github/license/anvilm/slim-rr-boilerplate?color=white&labelColor=black&style=flat-square

[status-link]: https://github.com/AnvilM/slim-rr-boilerplate/

[status-shield]: https://img.shields.io/badge/status-active-brightgreen?labelColor=black&style=flat-square

[last-commit-link]: https://github.com/AnvilM/slim-rr-boilerplate/commits

[last-commit-shield]: https://img.shields.io/github/last-commit/anvilm/slim-rr-boilerplate?labelColor=black&style=flat-square

[phpstan-link]: https://github.com/AnvilM/slim-rr-boilerplate/

[phpstan-shield]: https://img.shields.io/badge/PHPStan-Level%20max-blue?logo=php&labelColor=black&style=flat-square

[php-version-link]: https://github.com/AnvilM/slim-rr-boilerplate/

[php-version-shield]: https://img.shields.io/badge/PHP-8.4-blue?logo=php&labelColor=black&style=flat-square