<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CollectPaymentConfigProvidersPass collects services that provide additional configuration
 * for payment groups. For each payment handler group a single configuration provider can be specified
 * by adding a tag "chameleon_system_shop.payment_config_provider" to the service definition.
 */
class CollectPaymentConfigProvidersPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('chameleon_system_shop.payment_config_provider');

        $loaderDefinition = $container->getDefinition('chameleon_system_shop.payment.config_loader');

        foreach ($taggedServices as $serviceId => $attributes) {
            $systemName = null;
            foreach ($attributes as $tag) {
                foreach ($tag as $key => $value) {
                    if ('system_name' === $key) {
                        $systemName = $value;
                    }
                }
            }
            if (null === $systemName) {
                throw new LogicException(
                    "Services tagged with chameleon_system_shop.payment_config_provider need to provide a system_name attribute. This service doesn't have such an attribute: ".$serviceId
                );
            }

            $loaderDefinition->addMethodCall('addConfigProvider', array($systemName, new Reference($serviceId)));
        }
    }
}
