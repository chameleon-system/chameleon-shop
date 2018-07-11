<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\TShopBasket;

interface BasketItemEventInterface
{
    /**
     * @return \TShopBasket
     */
    public function getBasket();

    /**
     * @return \TShopBasketArticle
     */
    public function getBasketArticle();

    /**
     * @return \TdbDataExtranetUser
     */
    public function getUser();
}
