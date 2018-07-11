<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopViewMyOrderDetailsSessionAdapter implements IPkgShopViewMyOrderDetailsSessionAdapter
{
    /**
     * @var Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    const SESSIONNAME = 'pkgshop/myOrderList';

    public function __construct(\Symfony\Component\HttpFoundation\Session\SessionInterface $session)
    {
        $this->session = $session;
    }

    public function addOrderId($orderId)
    {
        $orderIdList = $this->session->get(self::SESSIONNAME, array());
        $orderIdList[] = $orderId;
        $this->session->set(self::SESSIONNAME, $orderIdList);
    }

    /**
     * @param $orderId
     *
     * @return bool
     */
    public function hasOrder($orderId)
    {
        $orderIdList = $this->session->get(self::SESSIONNAME, array());

        return true === in_array($orderId, $orderIdList);
    }
}
