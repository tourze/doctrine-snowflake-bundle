<?php

declare(strict_types=1);

namespace Tourze\DoctrineSnowflakeBundle\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\PHPUnitDoctrineEntity\EntityChecker;

/**
 * 检查使用 SnowflakeIdGenerator 的实体是否应该使用 SnowflakeKeyAware trait
 *
 * @implements Rule<InClassNode>
 */
class UseSnowflakeKeyAwareTraitRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        // 只检查实体类
        if (!EntityChecker::isEntityClass($classReflection->getNativeReflection())) {
            return [];
        }

        // 检查是否已经使用了 SnowflakeKeyAware trait
        if ($classReflection->hasTraitUse(SnowflakeKeyAware::class)) {
            return [];
        }

        // 检查是否使用了 SnowflakeIdGenerator 且有 getId 方法
        if (!$this->usesSnowflakeIdGenerator($classReflection) || !$this->hasGetIdMethod($classReflection)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                sprintf(
                    '实体类 %s 使用了 SnowflakeIdGenerator 并定义了 getId 方法，建议使用 \Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware trait 并删除手动定义的 getId/setId 方法。',
                    $classReflection->getName()
                )
            )->build(),
        ];
    }

    private function usesSnowflakeIdGenerator(ClassReflection $classReflection): bool
    {
        $nativeReflection = $classReflection->getNativeReflection();
        $properties = $nativeReflection->getProperties();

        foreach ($properties as $property) {
            // 检查是否有 CustomIdGenerator 注解并且使用了 SnowflakeIdGenerator
            $customIdAttributes = $property->getAttributes('Doctrine\ORM\Mapping\CustomIdGenerator');

            foreach ($customIdAttributes as $attribute) {
                $arguments = $attribute->getArguments();
                if (isset($arguments['class']) && SnowflakeIdGenerator::class === $arguments['class']) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasGetIdMethod(ClassReflection $classReflection): bool
    {
        return $classReflection->hasMethod('getId');
    }
}
