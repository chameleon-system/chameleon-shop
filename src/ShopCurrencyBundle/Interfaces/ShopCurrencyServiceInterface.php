<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopCurrencyBundle\Interfaces;

interface ShopCurrencyServiceInterface
{
    /**
     * @return string
     */
    public function getSymbol();

    /**
     * @return string
     */
    public function getIso4217Code();

    /**
     * @param float $value
     *
     * @return string
     */
    public function formatNumber($value);

    /**
     * @return void
     */
    public function reset();

    public function getObject(): ?\TdbPkgShopCurrency;

    /**
     * @param bool $bUseDefaultIfNotDefinedForUser
     *
     * @return string|null
     */
    public function getActiveCurrencyId($bUseDefaultIfNotDefinedForUser = true);
}
