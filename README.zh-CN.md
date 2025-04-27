# Doctrine Snowflake Bundle

[![最新版本](https://img.shields.io/packagist/v/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)
[![构建状态](https://img.shields.io/travis/tourze/doctrine-snowflake-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/doctrine-snowflake-bundle)
[![质量评分](https://img.shields.io/scrutinizer/g/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/doctrine-snowflake-bundle)
[![下载总量](https://img.shields.io/packagist/dt/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)

一个为 Symfony 提供分布式雪花 ID 生成能力的 Doctrine 扩展 Bundle。

[English](README.md) | [中文](README.zh-CN.md)

## 功能特性

- 基于 [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake)
- 支持为 Doctrine 实体属性自动生成雪花 ID
- 可选 Redis 序列解析器，降低高并发下 ID 冲突概率
- 支持自定义 ID 前缀和长度限制
- 基于主机名自动生成 workerId，适合分布式部署

## 安装方法

```bash
composer require tourze/doctrine-snowflake-bundle
```

## 快速开始

在实体类中添加雪花 ID 字段：

```php
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;

class YourEntity
{
    #[SnowflakeColumn(prefix: 'ORDER_', length: 32)]
    private string $id;

    // ... 其他属性和方法
}
```

## 配置说明

### 可选：启用 Redis 序列解析器

如需通过 Redis 解决高并发下的 ID 冲突问题，可参考如下配置：

```yaml
# config/packages/snc_redis.yaml
snc_redis:
    clients:
        default:
            type: phpredis
            alias: default
            dsn: redis://localhost
```

## 环境要求

- PHP 8.1 及以上
- Symfony 6.4 及以上
- Doctrine Bundle 2.13 及以上

## 贡献指南

欢迎提交 Issue 或 PR 参与贡献。

## 变更日志

详见 [Releases](https://packagist.org/packages/tourze/doctrine-snowflake-bundle#releases) 获取版本历史。

## 开源协议

MIT License
