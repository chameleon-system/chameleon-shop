<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The object is used to collect all articles in the basket to which the Discount applies. the object is managed by
 * TShopBasket.
 *
 * @psalm-suppress InvalidReturnType
 * @FIXME This class is not implemented
 */
class TShopBasketDiscountCore extends TdbShopDiscount
{
    /**
     * @var array
     */
    protected $aShopBasketArticles = array();

    /**
     * @return bool
     *              return true if the discount may be used for the current content of the discount object
     */
    public function AllowDiscountForContentValue()
    {
        // Not yet implemented
    }

    /**
     * @return float
     *               return the discount value for this discount using the articles in aShopBasketArticles
     */
    public function GetDiscountValue()
    {
        // Not yet implemented
    }
}
