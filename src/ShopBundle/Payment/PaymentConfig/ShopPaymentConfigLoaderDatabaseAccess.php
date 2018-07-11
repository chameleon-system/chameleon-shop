<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentConfig;

use ChameleonSystem\ShopBundle\Exception\DataAccessException;
use ChameleonSystem\ShopBundle\Payment\DataModel\OrderPaymentInfo;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderDataAccessInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use TdbShopOrder;
use TdbShopPaymentHandler;
use TdbShopPaymentHandlerGroup;
use TdbShopPaymentMethod;

class ShopPaymentConfigLoaderDatabaseAccess implements ShopPaymentConfigLoaderDataAccessInterface
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    public function __construct(Connection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromOrderId($orderId)
    {
        $order = TdbShopOrder::GetNewInstance();
        if (!$order->Load($orderId)) {
            throw new DataAccessException('Error while loading order with ID '.$orderId);
        }
        $paymentMethod = TdbShopPaymentMethod::GetNewInstance();
        if (!$paymentMethod->Load($order->fieldShopPaymentMethodId)) {
            throw new DataAccessException(
                'Error while loading payment method with ID '.$order->fieldShopPaymentMethodId.' for order with ID '.$orderId
            );
        }

        return new OrderPaymentInfo($orderId, $paymentMethod->fieldShopPaymentHandlerId, $order->fieldCmsPortalId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentHandlerGroupIdFromPaymentHandlerId($paymentHandlerId)
    {
        $handler = TdbShopPaymentHandler::GetNewInstance();
        if (!$handler->Load($paymentHandlerId)) {
            throw new DataAccessException('Error while loading payment handler with ID '.$paymentHandlerId);
        }

        return $handler->fieldShopPaymentHandlerGroupId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentHandlerGroupIdFromSystemName($systemName)
    {
        $handlerGroup = TdbShopPaymentHandlerGroup::GetNewInstance();
        if (!$handlerGroup->LoadFromField('system_name', $systemName)) {
            throw new DataAccessException('Error while loading payment handler group ID for systemName '.$systemName);
        }

        return $handlerGroup->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentHandlerGroupSystemNameFromId($paymentHandlerGroupId)
    {
        $handlerGroup = TdbShopPaymentHandlerGroup::GetNewInstance();
        if (!$handlerGroup->Load($paymentHandlerGroupId)) {
            throw new DataAccessException(
                'Error while loading payment handler group systemName for ID '.$paymentHandlerGroupId
            );
        }

        return $handlerGroup->fieldSystemName;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment($paymentHandlerGroupId)
    {
        $paymentHandlerGroup = TdbShopPaymentHandlerGroup::GetNewInstance();
        if (!$paymentHandlerGroup->Load($paymentHandlerGroupId)) {
            throw new DataAccessException(
                'Error while loading environment for payment handler group with ID '.$paymentHandlerGroupId
            );
        }

        return $paymentHandlerGroup->fieldEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function loadPaymentHandlerGroupConfig($shopPaymentHandlerGroupId)
    {
        $query = 'SELECT `shop_payment_handler_group_config`.`name`, `shop_payment_handler_group_config`.`value`, `shop_payment_handler_group_config`.`type`, `shop_payment_handler_group_config`.`cms_portal_id`
                    FROM `shop_payment_handler_group_config`
                   WHERE `shop_payment_handler_group_config`.`shop_payment_handler_group_id` = :shopPaymentHandlerGroupId';
        try {
            $configDataRaw = $this->databaseConnection->fetchAll(
                $query,
                array('shopPaymentHandlerGroupId' => $shopPaymentHandlerGroupId)
            );
        } catch (DBALException $e) {
            throw new DataAccessException(
                'Error while loading configuration for payment handler group with ID '.$shopPaymentHandlerGroupId,
                $e->getCode(),
                $e
            );
        }
        $configData = array();
        foreach ($configDataRaw as $row) {
            $configData[] = new ShopPaymentConfigRawValue(
                $row['name'],
                $row['value'],
                $row['type'],
                $row['cms_portal_id'],
                ShopPaymentConfigRawValue::SOURCE_GROUP
            );
        }

        return $configData;
    }

    /**
     * {@inheritdoc}
     */
    public function loadPaymentHandlerConfig($shopPaymentHandlerId)
    {
        $query = 'SELECT `shop_payment_handler_parameter`.`systemname`, `shop_payment_handler_parameter`.`value`, `shop_payment_handler_parameter`.`type`, `shop_payment_handler_parameter`.`cms_portal_id`
                    FROM `shop_payment_handler_parameter`
                   WHERE `shop_payment_handler_parameter`.`shop_payment_handler_id` = :shopPaymentHandlerId';
        try {
            $configDataRaw = $this->databaseConnection->fetchAll(
                $query,
                array('shopPaymentHandlerId' => $shopPaymentHandlerId)
            );
        } catch (DBALException $e) {
            throw new DataAccessException(
                'Error while loading configuration for payment handler with ID '.$shopPaymentHandlerId,
                $e->getCode(),
                $e
            );
        }
        $configData = array();
        foreach ($configDataRaw as $row) {
            $configData[] = new ShopPaymentConfigRawValue(
                $row['systemname'],
                $row['value'],
                $row['type'],
                $row['cms_portal_id'],
                ShopPaymentConfigRawValue::SOURCE_HANDLER
            );
        }

        return $configData;
    }
}
