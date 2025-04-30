<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopProductExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExportHandlerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @api
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $exporter = $container->getDefinition('chameleon_system_shop_product_export.exporter');
        $exportHandlerIds = $container->findTaggedServiceIds('chameleon_system_shop_product_export.export_handler');
        $aliasRegistered = [];
        foreach ($exportHandlerIds as $exportHandlerId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $alias = (isset($attributes['alias'])) ? $attributes['alias'] : null;
                if (null === $alias) {
                    throw new \ErrorException("the service {$exportHandlerId} is tagged als an export_handler but the tag is missing the alias attribute.", 0, E_USER_ERROR, __FILE__, __LINE__);
                }

                if (in_array($alias, $aliasRegistered)) {
                    throw new \ErrorException("unable to register the export handler {$exportHandlerId} because there is already a handler registered with the alias {$alias}", 0, E_USER_ERROR, __FILE__, __LINE__);
                }
                $aliasRegistered[] = $alias;
                $exporter->addMethodCall('registerHandler', [$alias, $container->getDefinition($exportHandlerId)]);
            }
        }

        if (0 === count($aliasRegistered)) {
            throw new \ErrorException('no export handlers found for the shop article list exporter! Please unregister the bundle or add export handler (by tagging them with chameleon_system_shop_product_export.export_handler)', 0, E_USER_ERROR, __FILE__, __LINE__);
        }
    }
}
