<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle;

use ChameleonSystem\AmazonPaymentBundle\Configuration\ConfigValidator;
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderInterface;
use IPkgCmsCoreLog;
use Psr\Log\LoggerInterface;

class AmazonPaymentConfigFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ShopPaymentConfigLoaderInterface
     */
    private $shopPaymentConfigLoader;
    /**
     * @var ConfigValidator
     */
    private $configValidator;

    private $internalCache;

    /**
     * @param LoggerInterface                  $logger
     * @param ShopPaymentConfigLoaderInterface $shopPaymentConfigLoader
     * @param ConfigValidator                  $configValidator
     */
    public function __construct(LoggerInterface $logger, ShopPaymentConfigLoaderInterface $shopPaymentConfigLoader, ConfigValidator $configValidator)
    {
        $this->logger = $logger;
        $this->shopPaymentConfigLoader = $shopPaymentConfigLoader;
        $this->configValidator = $configValidator;
        $this->internalCache = array();
    }

    /**
     * @param string $portalId - uses active portal if you pass null
     *
     * @throws \InvalidArgumentException
     *
     * @return AmazonPaymentGroupConfig
     */
    public static function createConfig($portalId)
    {
        /* @var $configService AmazonPaymentConfigFactory */
        $configService = self::getConfigService();

        return $configService->getConfig($portalId);
    }

    protected static function getConfigService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_amazon_payment.config');
    }

    public function getConfig($portalId)
    {
        if (array_key_exists($portalId, $this->internalCache)) {
            return $this->internalCache[$portalId];
        }
        $config = $this->shopPaymentConfigLoader->loadFromPaymentHandlerGroupSystemName('amazon', $portalId);
        $this->configValidator->validate($config);
        $amazonConfig = new AmazonPaymentGroupConfig($config);
        $amazonConfig->setLogger($this->logger);
        $this->internalCache[$portalId] = $amazonConfig;

        return $amazonConfig;
    }
}
