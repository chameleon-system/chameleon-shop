<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\DataModel;

class OrderPaymentInfo
{
    /**
     * @var string
     */
    private $orderId;
    /**
     * @var string
     */
    private $paymentHandlerId;
    /**
     * @var string
     */
    private $portalId;

    /**
     * @param string $orderId
     * @param string $paymentHandlerId
     * @param string $portalId
     */
    public function __construct($orderId, $paymentHandlerId, $portalId)
    {
        $this->orderId = $orderId;
        $this->paymentHandlerId = $paymentHandlerId;
        $this->portalId = $portalId;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getPaymentHandlerId()
    {
        return $this->paymentHandlerId;
    }

    /**
     * @return string
     */
    public function getPortalId()
    {
        return $this->portalId;
    }
}
