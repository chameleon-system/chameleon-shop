<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopViewMyOrderDetails implements IPkgShopViewMyOrderDetails
{
    /**
     * @var IPkgShopViewMyOrderDetailsDbAdapter
     */
    private $dbAdapter;
    /**
     * @var IPkgShopViewMyOrderDetailsSessionAdapter
     */
    private $sessionAdapter;

    public function __construct(
        IPkgShopViewMyOrderDetailsDbAdapter $dbAdapter,
        IPkgShopViewMyOrderDetailsSessionAdapter $sessionAdapter
    ) {
        $this->dbAdapter = $dbAdapter;
        $this->sessionAdapter = $sessionAdapter;
    }

    public function addOrderIdToMyList($orderId, $userId = null)
    {
        if (null === $userId) {
            $this->sessionAdapter->addOrderId($orderId);
        }
    }

    public function orderIdBelongsToUser($orderId, $userId = null)
    {
        if (null === $userId) {
            return $this->sessionAdapter->hasOrder($orderId);
        }

        return $this->dbAdapter->hasOrder($userId, $orderId);
    }
}
