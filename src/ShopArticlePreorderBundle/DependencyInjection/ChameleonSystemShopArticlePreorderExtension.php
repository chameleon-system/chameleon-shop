<?php

namespace ChameleonSystem\ShopArticlePreorderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ChameleonSystemShopArticlePreorderExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XMLFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('services.xml');
    }


}
