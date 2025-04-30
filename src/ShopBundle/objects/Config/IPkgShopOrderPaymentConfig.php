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
    public const ENVIRONMENT_PRODUCTION = 'production';
    public const ENVIRONMENT_SANDBOX = 'sandbox';
    /**
     * ENVIRONMENT_COMMON is a pseudo-environment. It is a marker to allow configuration parameters to be valid for
     * all environments.
     */
    public const ENVIRONMENT_COMMON = 'common';

    /**
     * @return string - self::ENVIRONMENT_PRODUCTION|self::ENVIRONMENT_SANDBOX
     *
     * @psalm-return self::ENVIRONMENT_*
     */
    public function getEnvironment();

    /**
     * @return bool
     */
    public function isCaptureOnShipment();

    /**
     * @param bool $captureOnShipment
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function setCaptureOnShipment($captureOnShipment);

    /**
     * @param string $key
     *
     * @return mixed
     *
     * Assumption: The return type is defined by the default parameter
     *
     * @psalm-template T
     *
     * @psalm-param T $default
     *
     * @psalm-return (T is null ? mixed : T)
     */
    public function getValue($key, $default = null);

    /**
     * @return array
     */
    public function getAllValues();
}
