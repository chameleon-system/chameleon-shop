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

use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;

class ConfigValidator
{
    /**
     * @param \IPkgShopOrderPaymentConfig $config
     *
     * @throws \InvalidArgumentException
     */
    public function validate(\IPkgShopOrderPaymentConfig $config)
    {
        $required = array(
            'merchantId',
            'accessKey',
            'applicationName',
            'applicationVersion',
            'region',
            'payWithAmazonButtonURL',
        );
        foreach ($required as $field) {
            if (null === $config->getValue($field, null)) {
                throw new \InvalidArgumentException("required parameter {$field} missing");
            }
        }
        $region = $config->getValue('region');
        $allowedRegions = array(
            AmazonPaymentGroupConfig::REGION_DE,
            AmazonPaymentGroupConfig::REGION_NA,
            AmazonPaymentGroupConfig::REGION_UK,
            AmazonPaymentGroupConfig::REGION_US,
        );
        if (false === in_array($region, $allowedRegions)) {
            throw new \InvalidArgumentException("{$region} is not a supported region");
        }

        $urls = array('serviceURL', 'widgetURL', 'payWithAmazonButtonURL');
        foreach ($urls as $url) {
            if (false === \TCMSUserInput::FilterValue($url, \TCMSUserInput::FILTER_URL)) {
                throw new \InvalidArgumentException("{$url} is not a valid url");
            }
        }
    }
}
