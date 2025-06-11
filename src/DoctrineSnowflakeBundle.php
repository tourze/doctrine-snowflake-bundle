<?php

namespace Tourze\DoctrineSnowflakeBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\SnowflakeBundle\SnowflakeBundle;

class DoctrineSnowflakeBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            SnowflakeBundle::class => ['all' => true],
        ];
    }
}
