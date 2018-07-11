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

use ChameleonSystem\ShopBundle\Exception\ConfigurationException;
use ChameleonSystem\ShopBundle\Exception\DataAccessException;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderDataAccessInterface;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderInterface;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigProviderInterface;
use IPkgShopOrderPaymentConfig;
use TPkgShopOrderPaymentConfig;

/**
 * ShopPaymentConfigLoader loads the configuration for payment handlers.
 * See the payment section in this bundle's documentation on how the configuration loading process works.
 */
class ShopPaymentConfigLoader implements ShopPaymentConfigLoaderInterface
{
    /**
     * @var ShopPaymentConfigLoaderDataAccessInterface
     */
    private $shopPaymentConfigLoaderDataAccess;
    /**
     * @var string
     */
    private $defaultEnvironment;
    /**
     * @var ShopPaymentConfigProviderInterface[]
     */
    private $configProviderList;

    /**
     * @param ShopPaymentConfigLoaderDataAccessInterface $shopPaymentConfigLoaderDataAccess
     * @param string                                     $defaultEnvironment                One of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     */
    public function __construct(
        ShopPaymentConfigLoaderDataAccessInterface $shopPaymentConfigLoaderDataAccess,
        $defaultEnvironment
    ) {
        $this->shopPaymentConfigLoaderDataAccess = $shopPaymentConfigLoaderDataAccess;
        $this->defaultEnvironment = $defaultEnvironment;
        $this->configProviderList = array();
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromOrderId($orderId)
    {
        try {
            $orderPaymentInfo = $this->shopPaymentConfigLoaderDataAccess->getDataFromOrderId(
                $orderId
            );

            return $this->loadFromPaymentHandlerId(
                $orderPaymentInfo->getPaymentHandlerId(),
                $orderPaymentInfo->getPortalId()
            );
        } catch (DataAccessException $e) {
            throw new ConfigurationException(
                'Data access error while trying to load payment configuration for orderId '.$orderId,
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPaymentHandlerId($paymentHandlerId, $portalId)
    {
        try {
            $paymentHandlerGroupId = $this->shopPaymentConfigLoaderDataAccess->getPaymentHandlerGroupIdFromPaymentHandlerId(
                $paymentHandlerId
            );

            return $this->loadConfiguration($portalId, $paymentHandlerGroupId, $paymentHandlerId);
        } catch (DataAccessException $e) {
            throw new ConfigurationException(
                'Data access error while trying to load payment configuration for paymentHandlerId '.$paymentHandlerId.' and portal ID '.$portalId,
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPaymentHandlerGroupId($paymentHandlerGroupId, $portalId)
    {
        return $this->loadConfiguration($portalId, $paymentHandlerGroupId, null);
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromPaymentHandlerGroupSystemName($systemName, $portalId)
    {
        try {
            $paymentHandlerGroupId = $this->shopPaymentConfigLoaderDataAccess->getPaymentHandlerGroupIdFromSystemName(
                $systemName
            );

            return $this->loadConfiguration($portalId, $paymentHandlerGroupId, null);
        } catch (DataAccessException $e) {
            throw new ConfigurationException(
                'Data access error while trying to load payment configuration for payment handler group with system name '.$systemName.' and portal ID '.$portalId,
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param string                             $alias
     * @param ShopPaymentConfigProviderInterface $shopPaymentConfigProvider
     */
    public function addConfigProvider($alias, ShopPaymentConfigProviderInterface $shopPaymentConfigProvider)
    {
        $this->configProviderList[$alias] = $shopPaymentConfigProvider;
    }

    /**
     * @param string      $portalId
     * @param string      $paymentHandlerGroupId
     * @param string|null $paymentHandlerId
     *
     * @return IPkgShopOrderPaymentConfig
     *
     * @throws ConfigurationException
     */
    private function loadConfiguration($portalId, $paymentHandlerGroupId, $paymentHandlerId = null)
    {
        try {
            $paymentHandlerGroupConfigList = $this->shopPaymentConfigLoaderDataAccess->loadPaymentHandlerGroupConfig(
                $paymentHandlerGroupId
            );
        } catch (DataAccessException $e) {
            throw new ConfigurationException(
                'Data access error while trying to load configuration for payment handler group with ID '.$paymentHandlerGroupId,
                $e->getCode(),
                $e
            );
        }
        $paymentConfigList = array();
        if (null !== $paymentHandlerId) {
            try {
                $paymentConfigList = $this->shopPaymentConfigLoaderDataAccess->loadPaymentHandlerConfig(
                    $paymentHandlerId
                );
            } catch (DataAccessException $e) {
                throw new ConfigurationException(
                    'Data access error while trying to load configuration for payment handler with ID '.$paymentHandlerId,
                    $e->getCode(),
                    $e
                );
            }
        }
        $environment = $this->getEnvironment($paymentHandlerGroupId);
        $additionalConfig = $this->getAdditionalConfig($paymentHandlerGroupId);
        $config = $this->getFlattenedConfiguration(
            $paymentHandlerGroupConfigList,
            $paymentConfigList,
            $additionalConfig,
            $environment,
            $portalId
        );

        return $config;
    }

    /**
     * @param string $paymentHandlerGroupId
     *
     * @return string one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     *
     * @throws ConfigurationException
     */
    private function getEnvironment($paymentHandlerGroupId)
    {
        try {
            $environment = $this->shopPaymentConfigLoaderDataAccess->getEnvironment($paymentHandlerGroupId);
        } catch (DataAccessException $e) {
            throw new ConfigurationException(
                'Data access error while trying to load environment for payment handler group with ID '.$paymentHandlerGroupId,
                $e->getCode(),
                $e
            );
        }
        if (!empty($environment) && 'default' !== $environment) {
            return $environment;
        }

        return $this->defaultEnvironment;
    }

    /**
     * @param string $paymentHandlerGroupId
     *
     * @return ShopPaymentConfigRawValue[]
     *
     * @throws ConfigurationException
     */
    private function getAdditionalConfig($paymentHandlerGroupId)
    {
        $additionalConfig = array();
        try {
            $systemName = $this->shopPaymentConfigLoaderDataAccess->getPaymentHandlerGroupSystemNameFromId(
                $paymentHandlerGroupId
            );
        } catch (DataAccessException $e) {
            throw new ConfigurationException(
                'Data access error while trying to load payment handler group system name for ID '.$paymentHandlerGroupId,
                $e->getCode(),
                $e
            );
        }

        if (isset($this->configProviderList[$systemName])) {
            /** @var ShopPaymentConfigProviderInterface $configProvider */
            $configProvider = $this->configProviderList[$systemName];
            $additionalConfig = $configProvider->getAdditionalConfiguration();
        }

        return $additionalConfig;
    }

    /**
     * @param ShopPaymentConfigRawValue[] $paymentHandlerGroupConfigList
     * @param ShopPaymentConfigRawValue[] $paymentConfigList
     * @param ShopPaymentConfigRawValue[] $additionalConfig
     * @param string                      $environment                   one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     * @param string                      $portalId
     *
     * @return IPkgShopOrderPaymentConfig
     */
    private function getFlattenedConfiguration(
        array $paymentHandlerGroupConfigList,
        array $paymentConfigList,
        array $additionalConfig,
        $environment,
        $portalId
    ) {
        $config = array();
        $config = $this->getPrioritizedConfigValues($config, $paymentHandlerGroupConfigList, $environment, $portalId);
        $config = $this->getPrioritizedConfigValues($config, $additionalConfig, $environment, $portalId);
        $config = $this->getPrioritizedConfigValues($config, $paymentConfigList, $environment, $portalId);

        $configObject = new TPkgShopOrderPaymentConfig($environment, $this->getMergedConfigValues($config));

        return $configObject;
    }

    /**
     * @param ShopPaymentConfigRawValue[] $config
     * @param ShopPaymentConfigRawValue[] $configToMerge
     * @param string                      $environment   one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     * @param string                      $portalId
     *
     * @return ShopPaymentConfigRawValue[]
     */
    private function getPrioritizedConfigValues(array $config, array $configToMerge, $environment, $portalId)
    {
        foreach ($configToMerge as $paymentConfig) {
            if (!$this->isApplicable($paymentConfig, $environment, $portalId)) {
                continue;
            }
            if (isset($config[$paymentConfig->getName()])) {
                $existingConfig = $config[$paymentConfig->getName()];
                if ($this->hasHigherPriority($paymentConfig, $existingConfig, $environment, $portalId)) {
                    $config[$paymentConfig->getName()] = $paymentConfig;
                }
            } else {
                $config[$paymentConfig->getName()] = $paymentConfig;
            }
        }

        return $config;
    }

    /**
     * Returns true if the given config value can be applied, i.e. the environment and portal do match.
     *
     * @param ShopPaymentConfigRawValue $value
     * @param string                    $environment one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     * @param string                    $portalId
     *
     * @return bool
     */
    private function isApplicable(ShopPaymentConfigRawValue $value, $environment, $portalId)
    {
        return (($environment === $value->getEnvironment()) || (IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON === $value->getEnvironment()))
            && (($portalId === $value->getPortalId()) || ('' === $value->getPortalId()));
    }

    /**
     * Returns true if value1 has a higher priority than value2.
     *
     * @param ShopPaymentConfigRawValue $value1
     * @param ShopPaymentConfigRawValue $value2
     * @param string                    $environment one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     * @param string                    $portalId
     *
     * @return bool
     */
    private function hasHigherPriority(
        ShopPaymentConfigRawValue $value1,
        ShopPaymentConfigRawValue $value2,
        $environment,
        $portalId
    ) {
        if ($this->hasHigherSourcePriority($value1, $value2)) {
            return true;
        }
        if ($this->hasHigherPortalPriority($value1, $value2, $portalId)) {
            return true;
        }
        if ($this->hasHigherEnvironmentPriority($value1, $value2, $environment)) {
            return true;
        }

        return false;
    }

    /**
     * @param ShopPaymentConfigRawValue $value1
     * @param ShopPaymentConfigRawValue $value2
     *
     * @return bool
     */
    private function hasHigherSourcePriority(ShopPaymentConfigRawValue $value1, ShopPaymentConfigRawValue $value2)
    {
        return $value1->getSource() > $value2->getSource();
    }

    /**
     * @param ShopPaymentConfigRawValue $value1
     * @param ShopPaymentConfigRawValue $value2
     * @param string                    $portalId
     *
     * @return bool
     */
    private function hasHigherPortalPriority(
        ShopPaymentConfigRawValue $value1,
        ShopPaymentConfigRawValue $value2,
        $portalId
    ) {
        return ($portalId === $value1->getPortalId()) && ('' === $value2->getPortalId());
    }

    /**
     * @param ShopPaymentConfigRawValue $value1
     * @param ShopPaymentConfigRawValue $value2
     * @param string                    $environment one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
     *
     * @return bool
     */
    private function hasHigherEnvironmentPriority(
        ShopPaymentConfigRawValue $value1,
        ShopPaymentConfigRawValue $value2,
        $environment
    ) {
        if (($environment === $value1->getEnvironment()) && ($environment !== $value2->getEnvironment())) {
            return true;
        }
        if ((IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON === $value1->getEnvironment())
            && ($environment !== $value2->getEnvironment())
            && (IPkgShopOrderPaymentConfig::ENVIRONMENT_COMMON !== $value2->getEnvironment())
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param ShopPaymentConfigRawValue[] $config
     *
     * @return string[]
     */
    private function getMergedConfigValues(array $config)
    {
        $mergedConfig = array();

        foreach ($config as $paymentConfig) {
            $mergedConfig[$paymentConfig->getName()] = $paymentConfig->getValue();
        }

        return $mergedConfig;
    }
}
