# Doctrine Snowflake Bundle

一个用于 Symfony 的雪花 ID 生成器 Bundle。

A Snowflake ID generator bundle for Symfony.

## 功能特性 | Features

- 基于 [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake) 实现
- 支持自动为实体属性生成雪花 ID
- 支持 Redis 序列解析器，减少高并发下 ID 重复的概率
- 支持自定义 ID 前缀
- 支持 ID 长度限制
- 基于主机名自动生成 workerId，支持分布式部署

## 安装 | Installation

```bash
composer require tourze/doctrine-snowflake-bundle
```

## 使用方法 | Usage

### 1. 在实体中使用 | Use in Entity

```php
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;

class YourEntity
{
    #[SnowflakeColumn(prefix: 'ORDER_', length: 32)]
    private string $id;

    // ... 其他属性和方法
}
```

### 2. 手动生成 ID | Generate ID Manually

```php
use Tourze\DoctrineSnowflakeBundle\Service\Snowflake;

class YourService
{
    public function __construct(
        private readonly Snowflake $snowflake,
    ) {
    }
    
    public function generateId(): string
    {
        return $this->snowflake->id();
    }
}
```

## 配置 | Configuration

### Redis 配置 | Redis Configuration

如果你想使用 Redis 序列解析器来减少 ID 重复的概率，需要配置 Redis 服务：

If you want to use Redis sequence resolver to reduce ID duplication probability, you need to configure Redis service:

```yaml
# config/packages/snc_redis.yaml
snc_redis:
    clients:
        default:
            type: phpredis
            alias: default
            dsn: redis://localhost
```

## 要求 | Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine Bundle 2.13+

## 许可证 | License

MIT License
