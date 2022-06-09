<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ChameleonSystemShopExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XMLFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('cronjobs.xml');
        $loader->load('logging.xml');
        $loader->load('mappers.xml');
        $loader->load('services.xml');
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('monolog', ['channels' => ['order', 'order_payment_ipn']]);
    }
}
