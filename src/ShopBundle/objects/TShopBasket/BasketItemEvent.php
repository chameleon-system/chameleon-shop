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

use Symfony\Contracts\EventDispatcher\Event;

class BasketItemEvent extends Event implements BasketItemEventInterface
{
    /**
     * @var \TShopBasket
     */
    private $basket;
    /**
     * @var \TShopBasketArticle
     */
    private $basketArticle;
    /**
     * @var \TdbDataExtranetUser
     */
    private $user;

    public function __construct(\TdbDataExtranetUser $user, \TShopBasket $basket, \TShopBasketArticle $basketArticle = null)
    {
        $this->basket = $basket;
        $this->basketArticle = $basketArticle;
        $this->user = $user;
    }

    /**
     * @return \TShopBasket
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * @return \TShopBasketArticle
     */
    public function getBasketArticle()
    {
        return $this->basketArticle;
    }

    /**
     * @return \TdbDataExtranetUser
     */
    public function getUser()
    {
        return $this->user;
    }
}
