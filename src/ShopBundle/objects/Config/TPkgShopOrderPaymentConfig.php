<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\ParameterBag;

class TPkgShopOrderPaymentConfig implements IPkgShopOrderPaymentConfig
{
    /**
     * @var string
     */
    private $environment;
    /**
     * @var ParameterBag
     */
    private $data = null;

    /**
     * @param string   $environment self::ENVIRONMENT_SANDBOX or self::ENVIRONMENT_PRODUCTION
     * @param string[] $configData
     */
    public function __construct($environment, array $configData)
    {
        $this->environment = $environment;
        $this->data = new ParameterBag();

        if (isset($configData['captureOnShipment'])) {
            $configData['captureOnShipment'] = (true === $configData['captureOnShipment'] || 'true' === $configData['captureOnShipment'] || 1 === $configData['captureOnShipment'] || '1' === $configData['captureOnShipment']);
        }

        $this->data->add($configData);
    }

    /**
     * @return string - self::ENVIRONMENT_PRODUCTION|self::ENVIRONMENT_SANDBOX
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    public function isCaptureOnShipment()
    {
        return $this->getValue('captureOnShipment', false);
    }

    /**
     * @param bool $captureOnShipment
     *
     * @throws InvalidArgumentException
     */
    public function setCaptureOnShipment($captureOnShipment)
    {
        if (false === is_bool($captureOnShipment)) {
            throw new \InvalidArgumentException('captureOnShipment must be boolean');
        }
        $this->data->set('captureOnShipment', $captureOnShipment);
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return string|bool
     */
    public function getValue($key, $default = null)
    {
        return $this->data->get($key, $default);
    }

    /**
     * @return string[]
     */
    public function getAllValues()
    {
        return $this->data->all();
    }
}
