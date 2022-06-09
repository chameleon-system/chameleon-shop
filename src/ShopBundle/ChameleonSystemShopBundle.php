<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle;

use ChameleonSystem\ShopBundle\DependencyInjection\Compiler\ArticleListPass;
use ChameleonSystem\ShopBundle\DependencyInjection\Compiler\CollectPaymentConfigProvidersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ChameleonSystemShopBundle extends Bundle
{
    /**
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ArticleListPass());
        $container->addCompilerPass(new CollectPaymentConfigProvidersPass());
    }
}
