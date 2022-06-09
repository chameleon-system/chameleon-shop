<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface IPkgShopViewMyOrderDetailsDbAdapter
{
    /**
     * @param string $userId
     * @param string $orderId
     *
     * @return bool
     */
    public function hasOrder($userId, $orderId);

    /**
     * @param string $orderId
     *
     * @return TdbShopOrder|null
     */
    public function getOrder($orderId);
}
