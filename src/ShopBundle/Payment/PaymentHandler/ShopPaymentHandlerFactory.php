<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentHandler;

use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderInterface;
use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerDataAccessInterface;
use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerFactoryInterface;

class ShopPaymentHandlerFactory implements ShopPaymentHandlerFactoryInterface
{
    /**
     * @var ShopPaymentConfigLoaderInterface
     */
    private $shopPaymentConfigLoader;
    /**
     * @var ShopPaymentHandlerDataAccessInterface
     */
    private $shopPaymentHandlerDataAccess;

    public function __construct(ShopPaymentConfigLoaderInterface $shopPaymentConfigLoader, ShopPaymentHandlerDataAccessInterface $shopPaymentHandlerDataAccess)
    {
        $this->shopPaymentConfigLoader = $shopPaymentConfigLoader;
        $this->shopPaymentHandlerDataAccess = $shopPaymentHandlerDataAccess;
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentHandler($paymentHandlerId, $portalId, array $userParameterList = [])
    {
        $paymentHandler = $this->shopPaymentHandlerDataAccess->getBarePaymentHandler($paymentHandlerId);
        $configData = $this->shopPaymentConfigLoader->loadFromPaymentHandlerId($paymentHandlerId, $portalId);
        $paymentHandler->setConfigData($configData);
        $paymentHandler->SetPaymentUserData($userParameterList);

        return $paymentHandler;
    }
}
