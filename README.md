# Doctrine Snowflake Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)
[![Build Status](https://img.shields.io/travis/tourze/doctrine-snowflake-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/doctrine-snowflake-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/doctrine-snowflake-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)

A Snowflake ID generator bundle for Symfony, providing distributed unique ID generation for Doctrine entities.

[English](README.md) | [中文](README.zh-CN.md)

## Features

- Based on [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake)
- Auto-generate Snowflake IDs for Doctrine entity properties
- Optional Redis sequence resolver to reduce ID duplication under high concurrency
- Custom ID prefix and length limit
- WorkerId auto-generated by hostname, supporting distributed deployment

## Installation

```bash
composer require tourze/doctrine-snowflake-bundle
```

## Quick Start

Add a Snowflake ID to your entity:

```php
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;

class YourEntity
{
    #[SnowflakeColumn(prefix: 'ORDER_', length: 32)]
    private string $id;

    // ... other properties and methods
}
```

## Configuration

### Redis Sequence Resolver (optional)

To use Redis for sequence resolution and reduce ID duplication:

```yaml
# config/packages/snc_redis.yaml
snc_redis:
    clients:
        default:
            type: phpredis
            alias: default
            dsn: redis://localhost
```

## Requirements

- PHP 8.1 or newer
- Symfony 6.4 or newer
- Doctrine Bundle 2.13 or newer

## License

MIT License

## Contributing

Contributions are welcome! Please submit issues or pull requests.

## Changelog

See [Releases](https://packagist.org/packages/tourze/doctrine-snowflake-bundle#releases) for version history.
