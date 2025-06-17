<?php

namespace Tourze\DoctrineSnowflakeBundle\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;

/**
 * 自动加上主键相关字段
 *
 * 注意这里加上的主键是一个Snowflake ID，跟普通的自增不太一样
 * 本来这里的功能应该跟 PrimaryKeyAware 合并的，但是因为有历史的表在，我们没办法统一替换，只能先新增一个.
 * 这里返回的数据，我们必须使用字符串。因为前端语言例如JS对于大数值是可能存在精度问题的。
 */
trait SnowflakeKeyAware
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = '0';

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}
