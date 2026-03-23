<?php

declare(strict_types=1);

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class FileLogExtension implements ExtensionInterface
{
    public function getConfigKey(): string
    {
        return 'file_log';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $definition = new Definition(FileLogFormatter::class);
        $container->setDefinition('file_log.formatter', $definition)
            ->addTag('output.formatter');
    }

    public function process(ContainerBuilder $container): void
    {
    }
}
