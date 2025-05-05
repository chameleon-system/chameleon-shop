<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface IPkgShopViewMyOrderDetails
{
    public function __construct(IPkgShopViewMyOrderDetailsDbAdapter $dbAdapter, IPkgShopViewMyOrderDetailsSessionAdapter $sessionAdapter);

    /**
     * @param string $orderId
     * @param string|null $userId
     *
     * @return void
     */
    public function addOrderIdToMyList($orderId, $userId = null);

    /**
     * @param string $orderId
     * @param string|null $userId
     *
     * @return bool
     */
    public function orderIdBelongsToUser($orderId, $userId = null);
}
