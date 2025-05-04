<?php

namespace Tourze\DoctrineSnowflakeBundle\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\SnowflakeBundle\SnowflakeBundle;

class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SnowflakeBundle();
        yield new DoctrineSnowflakeBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'TEST_SECRET',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'validation' => [
                'email_validation_mode' => 'html5'
            ],
            'php_errors' => [
                'log' => true,
            ],
            'uid' => [
                'default_uuid_version' => 7,
                'time_based_uuid_version' => 7,
            ],
        ]);
    }
}
