<?php declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\DependencyInjection\Compiler;

use ChameleonSystem\EcommerceStatsBundle\StatsProvider\StatsProviderCollection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CollectStatsProvidersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(StatsProviderCollection::class);

        $taggedServices = $container->findTaggedServiceIds('chameleon_system_ecommerce_stats.stats_provider');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addProvider', [ new Reference($id) ]);
        }
    }
}
