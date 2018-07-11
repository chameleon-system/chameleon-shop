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

    public function addOrderIdToMyList($orderId, $userId = null);

    public function orderIdBelongsToUser($orderId, $userId = null);
}
