<?php

namespace Tourze\DoctrineSnowflakeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class DoctrineSnowflakeBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \Tourze\SnowflakeBundle\SnowflakeBundle::class => ['all' => true],
        ];
    }
}
