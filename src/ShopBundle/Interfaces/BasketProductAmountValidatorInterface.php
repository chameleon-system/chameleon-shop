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
 * BasketProductAmountValidatorInterface checks if a certain amount of products may be added to a basket.
 */
interface BasketProductAmountValidatorInterface
{
    /**
     * Returns true if $requestedAmount is a valid numeric value so that $product may be added to the basket. Note that
     * this method is only intended to check the amount format, not any logical constraints (such as checks if there are
     * sufficient products on stock).
     *
     * @param int|float|string $requestedAmount
     *
     * @return bool
     */
    public function isAmountValid(\TdbShopArticle $product, $requestedAmount);
}
