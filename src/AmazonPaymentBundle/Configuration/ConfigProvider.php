<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\Configuration;

use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigProviderInterface;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\ShopPaymentConfigRawValue;

/**
 * ConfigProvider adds configuration data that was given through its constructor argument.
 * It is designed to provide Symfony configuration values, and so does not take care of any portal
 * specifics.
 */
class ConfigProvider implements ShopPaymentConfigProviderInterface
{
    private $configValues = null;

    public function __construct(array $configList)
    {
        $this->configValues = array();
        foreach ($configList as $environment => $environmentConfig) {
            foreach ($environmentConfig as $name => $value) {
                $this->configValues[] = new ShopPaymentConfigRawValue(
                    $name,
                    $value,
                    $environment,
                    '',
                    ShopPaymentConfigRawValue::SOURCE_ADDITIONAL
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalConfiguration()
    {
        return $this->configValues;
    }
}
