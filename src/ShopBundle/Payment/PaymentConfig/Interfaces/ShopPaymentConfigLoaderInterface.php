<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces;

use ChameleonSystem\ShopBundle\Exception\ConfigurationException;

/**
 * ShopPaymentConfigLoaderInterface defines an interface that allows loading configuration data for payment handlers and
 * payment groups.
 */
interface ShopPaymentConfigLoaderInterface
{
    /**
     * Loads a payment configuration for a given orderId.
     *
     * @param string $orderId
     *
     * @return \IPkgShopOrderPaymentConfig
     *
     * @throws ConfigurationException
     */
    public function loadFromOrderId($orderId);

    /**
     * Loads a payment configuration for a payment handler in a portal.
     *
     * @param string $paymentHandlerId
     * @param string $portalId
     *
     * @return \IPkgShopOrderPaymentConfig
     *
     * @throws ConfigurationException
     */
    public function loadFromPaymentHandlerId($paymentHandlerId, $portalId);

    /**
     * Loads a payment configuration for a payment group [not a payment handler!] within a portal.
     *
     * @param string $paymentGroupId
     * @param string $portalId
     *
     * @return \IPkgShopOrderPaymentConfig
     *
     * @throws ConfigurationException
     */
    public function loadFromPaymentHandlerGroupId($paymentGroupId, $portalId);

    /**
     * Loads a payment configuration for a payment group within a portal based on the payment groups systemName.
     *
     * @param string $systemName
     * @param string $portalId
     *
     * @return \IPkgShopOrderPaymentConfig
     *
     * @throws ConfigurationException
     */
    public function loadFromPaymentHandlerGroupSystemName($systemName, $portalId);
}
