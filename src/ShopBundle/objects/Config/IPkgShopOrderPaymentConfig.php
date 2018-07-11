<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface IPkgShopOrderPaymentConfig
{
    const ENVIRONMENT_PRODUCTION = 'production';
    const ENVIRONMENT_SANDBOX = 'sandbox';
    /**
     * ENVIRONMENT_COMMON is a pseudo-environment. It is a marker to allow configuration parameters to be valid for
     * all environments.
     */
    const ENVIRONMENT_COMMON = 'common';

    /**
     * @return string - self::ENVIRONMENT_PRODUCTION|self::ENVIRONMENT_SANDBOX
     */
    public function getEnvironment();

    /**
     * @return bool
     */
    public function isCaptureOnShipment();

    /**
     * @param bool $captureOnShipment
     *
     * @throws InvalidArgumentException
     */
    public function setCaptureOnShipment($captureOnShipment);

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getValue($key, $default = null);

    /**
     * @return array
     */
    public function getAllValues();
}
