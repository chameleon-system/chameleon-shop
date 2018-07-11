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

/**
 * ShopPaymentConfigRawValue describes a configuration parameter that has been read from a data source, but is
 * not computed yet.
 */
class ShopPaymentConfigRawValue
{
    const SOURCE_GROUP = 1;
    const SOURCE_ADDITIONAL = 2;
    const SOURCE_HANDLER = 3;

    private $name;
    private $value;
    private $environment;
    private $portalId;
    private $source;

    /**
     * @param string $name        The configuration key
     * @param string $value       The configuration value
     * @param string $environment the environment for which this value is valid (one of IPkgShopPaymentConfig::ENVIRONMENT_*)
     * @param string $portalId    The portal for which this value is valid, or '' if valid for all portals
     * @param int    $source      Depicts from which configuration level the value is from (the payment handler group,
     *                            the configuration provider or the payment handler). One of SOURCE_GROUP, SOURCE_ADDITIONAL or SOURCE_HANDLER.
     */
    public function __construct($name, $value, $environment, $portalId, $source)
    {
        $this->name = $name;
        $this->value = $value;
        $this->environment = $environment;
        $this->portalId = $portalId;
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string One of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getPortalId()
    {
        return $this->portalId;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
