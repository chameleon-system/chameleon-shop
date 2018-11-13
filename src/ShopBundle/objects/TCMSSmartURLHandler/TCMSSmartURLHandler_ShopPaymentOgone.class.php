<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ShopBundle\Exception\ConfigurationException;
use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerFactoryInterface;
use Psr\Log\LoggerInterface;

class TCMSSmartURLHandler_ShopPaymentOgone extends TCMSSmartURLHandler_ShopBasketSteps
{
    /**
     * If response from Ogone contains "ogonenotifycmscall", function triggers the HandleNotifyMessage from PaymentHandler
     * On success exit an return http header 200 OK. On Failure exit and return http header 404 Not found. So Ogone send
     * notify again.
     *
     * If response from Ogone contains "ogone_cms_call" the trigger der parent function to get pagedefid.
     *
     * @return bool
     */
    public function GetPageDef()
    {
        $oGlobal = TGlobal::instance();
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();
        $iMessagePos = strpos($oURLData->sRelativeURL, TShopPaymentHandlerOgone::URL_IDENTIFIER_NOTIFY);
        $bNotifyIsValid = false;
        if (false !== $iMessagePos) {
            TTools::WriteLogEntry('OGONE: incoming notify message: '.print_r($oGlobal->GetUserData(), true), 1, __FILE__, __LINE__);
            if ($oGlobal->UserDataExists('PAYHAID') && $oGlobal->UserDataExists('PAYCALL')) {
                $sPaymentHandlerCMSIdent = $oGlobal->GetUserData('PAYHAID');
                $sInoParameter = $oGlobal->GetUserData('PAYCALL');
                if (TShopPaymentHandlerOgone::URL_IDENTIFIER == $sInoParameter && !empty($sPaymentHandlerCMSIdent)) {
                    $bNotifyIsValid = true;
                } else {
                    TTools::WriteLogEntry('OGONE: incoming notify message parameter incorrect: '.print_r($sInoParameter, true), 1, __FILE__, __LINE__);
                }
            } else {
                TTools::WriteLogEntry('OGONE: incoming notify message parameter missing: '.print_r($oGlobal->GetUserData(), true), 1, __FILE__, __LINE__);
            }
            if ($bNotifyIsValid) {
                /** @var $oPaymentHandler TShopPaymentHandlerOgone */
                $oPaymentHandler = TdbShopPaymentHandler::GetNewInstance();
                $oPaymentHandler->LoadFromField('cmsident', $sPaymentHandlerCMSIdent);
                $activePortal = $this->getPortalDomainService()->getActivePortal();
                try {
                    $oPaymentHandler = $this->getShopPaymentHandlerFactory()->createPaymentHandler($oPaymentHandler->id, $activePortal->id);
                    $oGlobal = TGlobal::instance();
                    if ($oPaymentHandler->HandleNotifyMessage($oGlobal->GetUserData())) {
                        // done... exit
                        TTools::WriteLogEntry('Ogone Payment notify Response completed', 4, __FILE__, __LINE__);
                        header('HTTP/1.1 200 OK');
                        exit(0);
                    } else {
                        $this->handleError();
                    }
                } catch (ConfigurationException $e) {
                    $this->getLogger()->error(
                        sprintf('Unable to create payment handler: %s', $e->getMessage()),
                        [
                            'paymentHandlerId' => $oPaymentHandler->id,
                            'portalId' => $activePortal->id,
                        ]
                    );
                    $this->handleError();
                }
            } else {
                $this->handleError();
            }
        } else {
            $iMessagePos = strpos($oURLData->sRelativeURL, TShopPaymentHandlerOgone::URL_IDENTIFIER);
            if (false !== $iMessagePos) {
                TTools::WriteLogEntry('OGONE: incoming payment redirect: '.print_r($oGlobal->GetUserData(), true), 1, __FILE__, __LINE__);
                $oURLData->sRelativeURL = substr($oURLData->sRelativeURL, 0, $iMessagePos);
                $sNewRelativeURL = '';
                if ('' != $oURLData->sRelativeURLPortalIdentifier) {
                    $sNewRelativeURL .= '/'.$oURLData->sRelativeURLPortalIdentifier;
                }
                if ('' != $oURLData->sLanguageIdentifier) {
                    $sNewRelativeURL .= '/'.$oURLData->sLanguageIdentifier;
                }
                $sNewRelativeURL .= $oURLData->sRelativeURL;
                $oURLData->sRelativeFullURL = $sNewRelativeURL;
                $iPageId = parent::GetPageDef();
                if (false !== $iPageId) {
                    $oURLData->bPagedefFound = false;
                } // prevent caching
            }
        }

        return $iPageId;
    }

    private function handleError()
    {
        header('HTTP/1.1 404 Not Found');
        exit(0);
    }

    /**
     * @return ShopPaymentHandlerFactoryInterface
     */
    private function getShopPaymentHandlerFactory()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.payment.handler_factory');
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    private function getLogger(): LoggerInterface
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.shop_payment');
    }
}
