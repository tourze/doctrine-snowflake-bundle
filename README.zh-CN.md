# Doctrine Snowflake Bundle

[![PHP 版本要求](https://img.shields.io/packagist/dependency-v/tourze/doctrine-snowflake-bundle/php?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)
[![最新版本](https://img.shields.io/packagist/v/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)
[![License](https://img.shields.io/packagist/l/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)
[![构建状态](https://img.shields.io/github/actions/workflow/status/tourze/doctrine-snowflake-bundle/tests.yml?branch=master&style=flat-square)](https://github.com/tourze/doctrine-snowflake-bundle/actions)
[![Coverage Status](https://img.shields.io/codecov/c/github/tourze/doctrine-snowflake-bundle?style=flat-square)](https://codecov.io/gh/tourze/doctrine-snowflake-bundle)
[![下载总量](https://img.shields.io/packagist/dt/tourze/doctrine-snowflake-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/doctrine-snowflake-bundle)

一个为 Symfony 提供 Doctrine 实体雪花 ID 生成能力的 Bundle，
支持分布式唯一 ID 生成。

[English](README.md) | [中文](README.zh-CN.md)

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装方法](#安装方法)
- [配置](#配置)
  - [包注册](#包注册)
- [快速开始](#快速开始)
  - [方法一：使用 SnowflakeKeyAware Trait（推荐）](#方法一使用-snowflakekeyaware-trait推荐)
  - [方法二：使用 SnowflakeColumn 属性](#方法二使用-snowflakecolumn-属性)
  - [方法三：手动配置 ID 生成器](#方法三手动配置-id-生成器)
- [高级用法](#高级用法)
  - [自定义配置](#自定义配置)
  - [多个雪花属性](#多个雪花属性)
  - [性能调优](#性能调优)
- [API 参考](#api-参考)
  - [SnowflakeColumn 属性](#snowflakecolumn-属性)
  - [SnowflakeKeyAware Trait](#snowflakekeyaware-trait)
  - [SnowflakeIdGenerator 服务](#snowflakeidgenerator-服务)
- [测试](#测试)
- [性能考虑](#性能考虑)
- [贡献指南](#贡献指南)
- [开源协议](#开源协议)
- [变更日志](#变更日志)

## 功能特性

- **雪花 ID 生成**: 基于 [godruoyi/php-snowflake](https://github.com/godruoyi/php-snowflake)
- **Doctrine 集成**: 为 Doctrine 实体主键自动生成雪花 ID
- **属性支持**: 使用 `#[SnowflakeColumn]` 属性标记自定义 ID 属性
- **Trait 支持**: 使用 `SnowflakeKeyAware` trait 自动生成主键
- **分布式就绪**: 基于主机名自动生成 workerId，适合分布式部署
- **数据中心 ID**: 基于实体类名自动生成，实现更好的 ID 分布
- **字符串格式**: 返回字符串格式的 ID，避免 JavaScript 精度问题
- **高性能**: 针对高并发场景优化

## 系统要求

- PHP 8.1 或更高版本
- Symfony 7.3 或更高版本
- Doctrine Bundle 2.13 或更高版本
- Doctrine ORM 3.0 或更高版本
- Doctrine DBAL 4.0 或更高版本
- tourze/symfony-snowflake-bundle

## 安装方法

```bash
composer require tourze/doctrine-snowflake-bundle
```

## 配置

### 包注册

使用 Symfony Flex 时会自动注册。手动安装需要：

```php
// config/bundles.php
return [
    // ... 其他包
    Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle::class => ['all' => true],
];
```

## 快速开始

### 方法一：使用 SnowflakeKeyAware Trait（推荐）

用于主键生成：

```php
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;

#[ORM\Entity]
class YourEntity
{
    use SnowflakeKeyAware;
    
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;
    
    // getter/setter 方法...
}
```

### 方法二：使用 SnowflakeColumn 属性

用于自定义 ID 属性（非主键）：

```php
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;

#[ORM\Entity]
class YourEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;
    
    #[SnowflakeColumn(prefix: 'ORDER_', length: 32)]
    #[ORM\Column(type: 'string', length: 32)]
    private string $orderId;
    
    // getter/setter 方法...
}
```

### 方法三：手动配置 ID 生成器

```php
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;

#[ORM\Entity]
class YourEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: SnowflakeIdGenerator::class)]
    #[ORM\Column(type: 'bigint', nullable: false)]
    private string $id;
    
    // getter/setter 方法...
}
```

## 高级用法

### 自定义配置

您可以自定义雪花 ID 生成行为：

```yaml
# config/packages/doctrine_snowflake.yaml
doctrine_snowflake:
    worker_id: 1  # 可选：覆盖自动生成的 worker ID
    data_center_id: 1  # 可选：覆盖自动生成的数据中心 ID
```

### 多个雪花属性

您可以在单个实体中使用多个雪花属性：

```php
#[ORM\Entity]
class Order
{
    use SnowflakeKeyAware;  // 主键
    
    #[SnowflakeColumn(prefix: 'ORDER_', length: 32)]
    #[ORM\Column(type: 'string', length: 32)]
    private string $orderNumber;
    
    #[SnowflakeColumn(prefix: 'TXN_', length: 24)]
    #[ORM\Column(type: 'string', length: 24)]
    private string $transactionId;
}
```

### 性能调优

对于高吞吐量应用：

- 在雪花 ID 列上使用适当的数据库索引
- 考虑使用字符串列类型以避免整数溢出
- 在分布式环境中监控 ID 生成性能

## API 参考

### SnowflakeColumn 属性

```php
#[SnowflakeColumn(prefix: 'ORDER_', length: 32)]
```

- `prefix`: ID 前缀（默认：空）
- `length`: 最大 ID 长度（默认：0，无限制）

### SnowflakeKeyAware Trait

提供以下方法：

- `getId(): ?string` - 获取实体 ID
- `setId(?string $id): void` - 设置实体 ID

### SnowflakeIdGenerator 服务

- 自动生成唯一的雪花 ID
- 使用主机名生成 worker ID
- 使用实体类名生成数据中心 ID
- 手动设置 ID 时保留现有值

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/doctrine-snowflake-bundle/tests
```

## 性能考虑

- 雪花 ID 以字符串形式返回，避免 JavaScript 精度问题
- 生成器使用类名的 CRC32 哈希值进行数据中心 ID 分布
- Worker ID 由主机名生成，适合分布式部署
- 高并发场景下处理效率高

## 贡献指南

欢迎贡献！请：

1. Fork 本仓库
2. 创建功能分支
3. 进行更改
4. 为新功能添加测试
5. 运行测试套件
6. 提交 Pull Request

## 开源协议

MIT 协议。更多信息请参见 [License File](LICENSE)。

## 变更日志

详见 [Releases](https://packagist.org/packages/tourze/doctrine-snowflake-bundle#releases) 获取版本历史。
