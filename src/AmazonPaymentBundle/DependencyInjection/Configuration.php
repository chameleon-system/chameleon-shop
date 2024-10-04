<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): \Symfony\Component\Config\Definition\Builder\TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chameleon_system_amazon_payment');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->append($this->getCommon())
                ->append($this->getSandBox())
                ->append($this->getProduction())
            ->end();

        return $treeBuilder;
    }

    protected function getCommon()
    {
        $treeBuilder = new TreeBuilder('common');
        $rootNode = $treeBuilder->getRootNode()->addDefaultsIfNotSet();
        $rootNode->children()
                    ->scalarNode('applicationName')
                        ->defaultValue('Chameleon Amazon API')
                    ->end()
                    ->scalarNode('applicationVersion')
                        ->defaultValue('1.0')
                    ->end()
                  ->end();

        return $rootNode;
    }

    protected function getProduction()
    {
        $treeBuilder = new TreeBuilder('production');
        $rootNode = $treeBuilder->getRootNode()->addDefaultsIfNotSet();
        $rootNode->children()
                    ->scalarNode('payWithAmazonButtonURL')
                      ->defaultValue('https://payments.amazon.de/gp/widgets/button')
                    ->end()
                ->end();

        return $rootNode;
    }

    protected function getSandBox()
    {
        $treeBuilder = new TreeBuilder('sandbox');
        $rootNode = $treeBuilder->getRootNode()->addDefaultsIfNotSet();
        $rootNode->children()
                    ->scalarNode('payWithAmazonButtonURL')
                        ->defaultValue('https://payments-sandbox.amazon.de/gp/widgets/button')
                    ->end()
                 ->end();

        return $rootNode;
    }
}
