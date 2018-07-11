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

use Doctrine\DBAL\Connection;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;

class AmazonPaymentGroupConfig implements \IPkgShopOrderPaymentConfig
{
    const REGION_DE = 'de';
    const REGION_UK = 'uk';
    const REGION_US = 'us';
    const REGION_NA = 'na';

    const AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_SHOP_ORDER_ID = 1;
    const AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID = 2;
    const AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_AMAZON_ORDER_REFERENCE_ID = 3;
    const ESONO_PLATFORM_ID = 'A1PW9JHPACAPIV';

    /**
     * @var \IPkgShopOrderPaymentConfig
     */
    private $config;

    public function __construct(\IPkgShopOrderPaymentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @var null|\OffAmazonPaymentsService_Client
     */
    private $amazonApi = null;

    /**
     * @var \IPkgCmsCoreLog
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function getValue($key, $default = null)
    {
        return $this->config->getValue($key, $default);
    }

    /**
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getMerchantId();
    }

    /**
     * @return string|null
     */
    public function getAccessKey()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getAccessKey();
    }

    /**
     * @return string|null
     */
    public function getSecretKey()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getSecretKey();
    }

    /**
     * @return string|null
     */
    public function getApplicationName()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getApplicationName();
    }

    /**
     * @return string|null
     */
    public function getApplicationVersion()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getApplicationVersion();
    }

    /**
     * @return string - one of self::REGION_*
     */
    public function getRegion()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getRegion();
    }

    /**
     * returns the full localized service URL.
     *
     * @return string|null
     */
    public function getServiceURL()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getServiceUrl();
    }

    /**
     * returns the full localized widgetURL.
     *
     * @return string|null
     */
    public function getWidgetURL()
    {
        return $this->getAmazonAPI()->getMerchantValues()->getWidgetUrl();
    }

    /**
     * return buy button url inc. merchant id parameter.
     *
     * @return string
     */
    public function getPayWithAmazonButton()
    {
        $buttonURL = $this->getValue('payWithAmazonButtonURL', null);
        if (null !== $buttonURL) {
            $buttonURL .= '?sellerId='.urlencode($this->getMerchantId());
        }

        return $buttonURL;
    }

    /**
     * return buy button text.
     *
     * @return string
     */
    public function getPayWithAmazonButtonText()
    {
        return $this->getValue('payWithAmazonButtonText', null);
    }

    public function getPlatformId()
    {
        return self::ESONO_PLATFORM_ID;
    }

    /**
     * @return \OffAmazonPaymentsService_Client
     */
    public function getAmazonAPI()
    {
        if (null !== $this->amazonApi) {
            return $this->amazonApi;
        }
        $config = array();
        $config['merchantId'] = $this->getValue('merchantId');
        $config['accessKey'] = $this->getValue('accessKey');
        $config['secretKey'] = $this->getValue('secretKey');
        $config['applicationName'] = $this->getValue('applicationName');
        $config['applicationVersion'] = $this->getValue('applicationVersion');
        $config['region'] = $this->getValue('region');
        $config['environment'] = (self::ENVIRONMENT_PRODUCTION === $this->getEnvironment()) ? 'Live' : 'Sandbox';

        $config['serviceURL'] = $this->getValue('serviceURL');
        $config['widgetURL'] = ''; //$this->getValue('widgetURL');

        $config['caBundleFile'] = '';
        $config['clientId'] = '';

        $this->amazonApi = new \OffAmazonPaymentsService_Client($config);

        return $this->amazonApi;
    }

    /**
     * return the IPN api.
     *
     * @return \OffAmazonPaymentsNotifications_Client
     */
    public function getAmazonIPNAPI()
    {
        return new \OffAmazonPaymentsNotifications_Client();
    }

    /**
     * returns a text displayed on the auth email sent by amazon to the buyer.
     *
     * @param \TdbShopOrder $order
     * @param float         $amount
     * @param bool          $captureNow
     * @param array         $itemList
     *
     * @return string
     */
    public function getSellerAuthorizationNote(\TdbShopOrder $order, $amount, $captureNow, array $itemList = array())
    {
        $text = $this->getValue('sellerAuthorizationNote');
        $data = $this->getTemplateDataFromOrder($order);
        $data['captureNow'] = $captureNow;
        $data['transaction__totalValue'] = $amount;
        $data['transaction__items'] = $itemList;

        return $this->render($text, $data);
    }

    /**
     * Represents a description of the order that is displayed in emails to the buyer. (max 1024 chars).
     *
     * @param \TdbShopOrder $order
     *
     * @return string
     */
    public function getSellerOrderNote(\TdbShopOrder $order)
    {
        $text = $this->getValue('sellerNote');
        $data = $this->getTemplateDataFromOrder($order);

        return mb_substr($this->render($text, $data), 0, 1024);
    }

    /**
     * The description to be shown on the buyer’s payment instrument statement. Maximum: 16 characters.
     *
     * @param \TdbShopOrder $order
     * @param null|string   $invoiceNumber - optionally you may pass an invoice number
     *
     * @return string
     */
    public function getSoftDescriptor(\TdbShopOrder $order, $invoiceNumber = null)
    {
        $text = $this->getValue('softDescriptor');
        $data = $this->getTemplateDataFromOrder($order);
        $data['invoiceNumber'] = $invoiceNumber;

        return mb_substr($this->render($text, $data), 0, 16);
    }

    /**
     * extract data from oder to be used by text generated via template.
     *
     * @param \TdbShopOrder $order
     *
     * @return array
     */
    protected function getTemplateDataFromOrder(\TdbShopOrder $order)
    {
        $data = $order->GetSQLWithTablePrefix($order->table);
        $shop = $order->GetFieldShop();
        $shopData = $shop->GetSQLWithTablePrefix($shop->table);
        $data = array_merge($data, $shopData);

        return $data;
    }

    /**
     * @param $templateString
     * @param array $data
     *
     * @return string
     */
    private function render($templateString, array $data)
    {
        $snippetRenderer = \TPkgSnippetRenderer::GetNewInstance(
            $templateString,
            \IPkgSnippetRenderer::SOURCE_TYPE_STRING
        );
        foreach ($data as $key => $value) {
            $snippetRenderer->setVar($key, $value);
        }

        return trim($snippetRenderer->render());
    }

    /**
     * @param string $amazonOrderReferenceId
     *
     * @return AmazonOrderReferenceObject
     */
    public function amazonOrderReferenceObjectFactory($amazonOrderReferenceId)
    {
        return new AmazonOrderReferenceObject($this, $amazonOrderReferenceId, $this->getLogger());
    }

    /**
     * @param Connection $dbal
     * @param int        $sourceType one of self::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_*
     * @param string     $sourceId
     *
     * @return AmazonReferenceIdManager|null
     *
     * @throws \InvalidArgumentException
     */
    public function amazonReferenceIdManagerFactory(Connection $dbal, $sourceType, $sourceId)
    {
        switch ($sourceType) {
            case self::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_SHOP_ORDER_ID:
                return AmazonReferenceIdManager::createFromShopOrderId($dbal, $sourceId);
                break;
            case self::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_AMAZON_ORDER_REFERENCE_ID:
                return AmazonReferenceIdManager::createFromOrderReferenceId($dbal, $sourceId);
                break;
            case self::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID:
                return AmazonReferenceIdManager::createFromLocalId($dbal, $sourceId);
                break;
            default:
                throw new \InvalidArgumentException('sourceType must be one of AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_*');
        }
    }

    /**
     * @return \IPkgCmsCoreLog
     */
    private function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \IPkgCmsCoreLog $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->config->getEnvironment();
    }

    /**
     * {@inheritdoc}
     */
    public function isCaptureOnShipment()
    {
        return $this->config->isCaptureOnShipment();
    }

    /**
     * {@inheritdoc}
     */
    public function setCaptureOnShipment($captureOnShipment)
    {
        $this->config->setCaptureOnShipment($captureOnShipment);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllValues()
    {
        return $this->config->getAllValues();
    }
}
