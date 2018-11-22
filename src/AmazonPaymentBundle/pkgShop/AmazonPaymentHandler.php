<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop;

use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use esono\pkgshoppaymenttransaction\PaymentHandlerWithTransactionSupportInterface;
use TdbShopOrder;
use TShopBasket;

class AmazonPaymentHandler extends \TdbShopPaymentHandler implements \IPkgShopPaymentIPNPaymentHandler, PaymentHandlerWithTransactionSupportInterface
{
    const PARAMETER_ORDER_REFERENCE_ID = 'amazonOrderReferenceId';
    const PARAMETER_IS_PAYMENT_ON_SHIPMENT = 'isPaymentOnShipping';

    /**
     * @var AmazonPaymentGroupConfig
     */
    private $amazonPaymentGroupConfig = null;

    public function PostSelectPaymentHook($sMessageConsumer)
    {
        if (false === parent::PostSelectPaymentHook($sMessageConsumer)) {
            return false;
        }

        $basket = \TShopBasket::GetInstance();
        $this->SetPaymentUserData(
            array(
                self::PARAMETER_ORDER_REFERENCE_ID => $basket->getAmazonOrderReferenceId(),
            )
        );

        return true;
    }

    public function ExecutePayment(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        if (false === parent::ExecutePayment($oOrder, $sMessageConsumer)) {
            return false;
        }

        $continue = false;

        try {
            $amazonConfig = $this->getAmazonPaymentGroupConfig($oOrder->fieldCmsPortalId);
            $amazonPayment = new AmazonPayment($amazonConfig);
            $transactionManager = new \TPkgShopPaymentTransactionManager($oOrder);
            $amazonPayment->captureOrder($transactionManager, $oOrder);
            $continue = true;
        } catch (\InvalidArgumentException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.chameleon_order')->warning(
                'error loading amazon payment config - unable to execute payment',
                array(
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                )
            );
            $msgManager = \TCMSMessageManager::GetInstance();
            $sMessageConsumer = ('' !== $sMessageConsumer) ? $sMessageConsumer : \TCMSMessageManager::GLOBAL_CONSUMER_NAME;
            $msgManager->AddMessage(
                $sMessageConsumer,
                AmazonPayment::ERROR_CODE_API_ERROR
            );
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $msgManager = \TCMSMessageManager::GetInstance();
            $sMessageConsumer = ('' !== $sMessageConsumer) ? $sMessageConsumer : \TCMSMessageManager::GLOBAL_CONSUMER_NAME;
            $msgManager->AddMessage($sMessageConsumer, $e->getMessageCode(), $e->getAdditionalData());
        }

        return $continue;
    }

    /**
     * @return string
     */
    public function getAmazonOrderReferenceId()
    {
        return $this->GetUserPaymentDataItem(self::PARAMETER_ORDER_REFERENCE_ID);
    }

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/ChameleonSystemAmazonPaymentBundlepkgShopAmazonPaymentHandler';
    }

    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $data = parent::GetAdditionalViewVariables($sViewName, $sViewType);

        $data['amazonConfig'] = null;
        $data['oBasket'] = \TShopBasket::GetInstance();
        try {
            $data['amazonConfig'] = $this->getAmazonPaymentGroupConfig($this->getActivePortalId());
        } catch (\InvalidArgumentException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.chameleon_order')->warning(
                'error loading amazon payment config'
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function paymentTransactionHandlerFactory($portalId)
    {
        return new AmazonPayment($this->getAmazonPaymentGroupConfig($portalId));
    }

    /**
     * return true if capture on shipment is active.
     *
     * @return bool
     */
    public function isCaptureOnShipment()
    {
        $isPaymentOnShipmentStored = $this->GetUserPaymentDataItem(self::PARAMETER_IS_PAYMENT_ON_SHIPMENT);
        if (false !== $isPaymentOnShipmentStored) {
            return '1' === $isPaymentOnShipmentStored;
        }

        $config = $this->getAmazonPaymentGroupConfig($this->getActivePortalId());

        return $config->isCaptureOnShipment();
    }

    /**
     * hook is called before the payment data is committed to the database. use it to cleanup/filter/add data you may
     * want to include/exclude from the database.
     *
     * @param array $aPaymentData
     *
     * @return array
     */
    protected function PreSaveUserPaymentDataToOrderHook($aPaymentData)
    {
        $config = $this->getAmazonPaymentGroupConfig($this->getActivePortalId());
        $aPaymentData[self::PARAMETER_IS_PAYMENT_ON_SHIPMENT] = (true === $config->isCaptureOnShipment()) ? '1' : '0';

        return $aPaymentData;
    }

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }

    /**
     * @return AmazonPaymentGroupConfig
     */
    public function getAmazonPaymentGroupConfig($portalId)
    {
        if (null !== $this->amazonPaymentGroupConfig) {
            return $this->amazonPaymentGroupConfig;
        }

        return AmazonPaymentConfigFactory::createConfig($portalId);
    }

    /**
     * @param AmazonPaymentGroupConfig $amazonPaymentGroupConfig
     */
    public function setAmazonPaymentGroupConfig($amazonPaymentGroupConfig)
    {
        $this->amazonPaymentGroupConfig = $amazonPaymentGroupConfig;
    }

    /**
     * some payment methods (such as paypal) get a reference number from the external
     * service, that allows the shop owner to identify the payment executed in their
     * Webservice. Since it is sometimes necessary to provided this identifier.
     *
     * every payment method that provides such an identifier needs to overwrite this method
     *
     * returns an empty string, if the method has no identifier.
     *
     * @return string
     */
    public function GetExternalPaymentReferenceIdentifier()
    {
        return $this->getAmazonOrderReferenceId();
    }

    /**
     * if amazon not blocked in cms then show it only
     * if amazon reference id is set.
     *
     * @return bool
     */
    public function isBlockForUserSelection()
    {
        if (null === TShopBasket::GetInstance()->getAmazonOrderReferenceId()) {
            return true;
        }

        return false;
    }
}
