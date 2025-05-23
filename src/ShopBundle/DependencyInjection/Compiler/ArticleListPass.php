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

class ArticleListPass implements CompilerPassInterface
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
        $this->registerStateExtractors($container);
        $this->registerStateElements($container);
        $this->registerResultModifications($container);
    }

    /**
     * @return void
     */
    private function registerStateExtractors(ContainerBuilder $container)
    {
        $extractorCollection = $container->getDefinition('chameleon_system_shop.state_request_extractor_collection');
        $extractorIds = $container->findTaggedServiceIds('chameleon_system_shop.article_list_module.state_extractor');
        foreach (array_keys($extractorIds) as $extractorId) {
            $extractorCollection->addMethodCall('registerExtractor', [$container->getDefinition($extractorId)]);
        }
    }

    /**
     * @return void
     */
    private function registerResultModifications(ContainerBuilder $container)
    {
        $modifierCollection = $container->getDefinition('chameleon_system_shop.result_modifier');
        $modifierIds = $container->findTaggedServiceIds('chameleon_system_shop.article_list_module.result_modification');
        foreach (array_keys($modifierIds) as $modifierId) {
            $modifierCollection->addMethodCall('addModification', [$container->getDefinition($modifierId)]);
        }
    }

    /**
     * @return void
     */
    private function registerStateElements(ContainerBuilder $container)
    {
        $stateFactory = $container->getDefinition('chameleon_system_shop.state_factory.state_factory');
        $stateElementIds = $container->findTaggedServiceIds('chameleon_system_shop.article_list_module.state_element');
        foreach (array_keys($stateElementIds) as $stateElementId) {
            $stateFactory->addMethodCall('registerStateElement', [$container->getDefinition($stateElementId)]);
        }
    }
}
