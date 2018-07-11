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

use ChameleonSystem\ShopBundle\Exception\DataAccessException;
use ChameleonSystem\ShopBundle\Payment\DataModel\OrderPaymentInfo;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\ShopPaymentConfigRawValue;

/**
 * ShopPaymentConfigLoaderDataAccessInterface is only meant to be used by an implementation
 * of ShopPaymentConfigLoaderInterface. It separates the data access from business logic in the config loader.
 */
interface ShopPaymentConfigLoaderDataAccessInterface
{
    /**
     * Returns an object that holds all data required to create a payment handler from an order.
     *
     * @param string $orderId
     *
     * @return OrderPaymentInfo
     *
     * @throws DataAccessException
     */
    public function getDataFromOrderId($orderId);

    /**
     * Returns a paymentHandlerGroupId of the payment handler group that is assigned to the payment handler with the
     * given ID.
     *
     * @param string $paymentHandlerId
     *
     * @return string
     *
     * @throws DataAccessException
     */
    public function getPaymentHandlerGroupIdFromPaymentHandlerId($paymentHandlerId);

    /**
     * Returns a paymentHandlerGroupId of the payment handler group with the given systemName.
     *
     * @param string $systemName
     *
     * @return string
     *
     * @throws DataAccessException
     */
    public function getPaymentHandlerGroupIdFromSystemName($systemName);

    /**
     * Returns a payment handler group with the given paymentHandlerGroupId.
     *
     * @param string $paymentHandlerGroupId
     *
     * @return string
     *
     * @throws DataAccessException
     */
    public function getPaymentHandlerGroupSystemNameFromId($paymentHandlerGroupId);

    /**
     * Returns the active environment for the payment handler group with the given paymentHandlerGroupId.
     *
     * @param string $paymentHandlerGroupId
     *
     * @return string One of the environment constants in IPkgShopOrderPaymentConfig
     *
     * @throws DataAccessException
     */
    public function getEnvironment($paymentHandlerGroupId);

    /**
     * Returns the raw configuration data for the payment handler group with the given paymentHandlerGroupId.
     *
     * @param string $paymentHandlerGroupId
     *
     * @return ShopPaymentConfigRawValue[]
     *
     * @throws DataAccessException
     */
    public function loadPaymentHandlerGroupConfig($paymentHandlerGroupId);

    /**
     * Returns the raw configuration data for the payment handler with the given paymentHandlerId.
     *
     * @param string $paymentHandlerId
     *
     * @return ShopPaymentConfigRawValue[]
     *
     * @throws DataAccessException
     */
    public function loadPaymentHandlerConfig($paymentHandlerId);
}
