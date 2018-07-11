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

use ChameleonSystem\ShopBundle\Payment\PaymentConfig\ShopPaymentConfigRawValue;

interface ShopPaymentConfigProviderInterface
{
    /**
     * Gets custom configuration from an arbitrary source (e.g. container configuration or config files).
     * This configuration will override any configuration values of the payment handler group, and will
     * itself be overridden by the payment handler configuration.
     *
     * @return ShopPaymentConfigRawValue[]
     */
    public function getAdditionalConfiguration();
}
