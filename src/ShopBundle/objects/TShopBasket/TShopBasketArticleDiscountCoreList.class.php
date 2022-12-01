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
 * class holds a list of discounts acting on a basket article.
/**/
class TShopBasketArticleDiscountCoreList extends TIterator
{
    /**
     * return next itme.
     *
     * @return TdbShopDiscount|false
     */
    public function next()
    {
        return parent::Next();
    }

    /**
     * return previous itme.
     *
     * @return TdbShopDiscount|false
     */
    public function Previous()
    {
        return parent::Previous();
    }

    /**
     * return current itme.
     *
     * @return TdbShopDiscount
     */
    public function current()
    {
        return parent::Current();
    }
}
