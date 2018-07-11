<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Interfaces;

/**
 * allows you to create an instance of a shop article based on the identification token passed in the shop url
 * Interface ShopRouteArticleFactoryInterface.
 */
interface ShopRouteArticleFactoryInterface
{
    /**
     * @param string $identificationToken
     *
     * @return \TdbShopArticle|null
     */
    public function createArticleFromIdentificationToken($identificationToken);
}
