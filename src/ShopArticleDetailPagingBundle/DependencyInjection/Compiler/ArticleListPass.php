<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ArticleListPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $listModuleService = $container->getDefinition('chameleon_system_shop.module.article_list');
        $requestToListConverter = $container->getDefinition('chameleon_system_shop_article_detail_paging.request_to_list_url_converter');
        $listModuleService->addMethodCall('setRequestToListUrlConverter', array($requestToListConverter));
    }
}
