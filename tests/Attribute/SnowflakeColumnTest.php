<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeColumn;

/**
 * 测试SnowflakeColumn属性类
 *
 * @internal
 */
#[CoversClass(SnowflakeColumn::class)]
final class SnowflakeColumnTest extends TestCase
{
    public function testConstructor(): void
    {
        // 测试默认值
        $attribute = new SnowflakeColumn();
        $this->assertSame('', $attribute->prefix);
        $this->assertSame(0, $attribute->length);

        // 测试设置值
        $attribute = new SnowflakeColumn('TEST_', 32);
        $this->assertSame('TEST_', $attribute->prefix);
        $this->assertSame(32, $attribute->length);
    }
}
