<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces;

use ChameleonSystem\ShopBundle\Exception\ConfigurationException;

/**
 * ShopPaymentHandlerFactoryInterface defines an interface for creating fully configured payment handlers.
 */
interface ShopPaymentHandlerFactoryInterface
{
    /**
     * Creates a payment handler that is configured with the configuration data taken from the default configuration
     * sources.
     *
     * @param string $paymentHandlerId
     * @param string $portalId
     * @param array  $userParameterList
     *
     * @return \TdbShopPaymentHandler
     *
     * @throws ConfigurationException
     */
    public function createPaymentHandler($paymentHandlerId, $portalId, array $userParameterList = array());
}
