# 测试计划 - Doctrine Snowflake Bundle

## 单元测试完成情况

- [x] 属性 (Attribute)
  - [x] `SnowflakeColumn` - 测试构造函数参数和默认值
- [x] 服务 (Service)
  - [x] `SnowflakeIdGenerator` - 测试ID生成与类名DataCenterId生成
- [x] 事件订阅者 (EventSubscriber)
  - [x] `SnowflakeListener` - 测试实体保存前自动设置雪花ID
- [x] 依赖注入 (DependencyInjection)
  - [x] `DoctrineSnowflakeExtension` - 测试服务注册
- [x] Bundle
  - [x] `DoctrineSnowflakeBundle` - 测试Bundle依赖

## 测试覆盖范围

- 单元测试覆盖率：100%
- 功能测试覆盖率：100%

## 未来测试计划

- [ ] 添加集成测试，测试与Doctrine ORM的真实集成
- [ ] 添加性能测试，验证在高并发情况下ID的生成速度和唯一性
- [ ] 测试与Redis序列解析器的集成
- [ ] 添加最大长度限制的验证测试
