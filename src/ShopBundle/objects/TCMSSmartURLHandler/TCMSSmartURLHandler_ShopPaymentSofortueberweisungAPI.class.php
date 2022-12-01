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

/**
 * takes sofortueberweisung return urls and maps them to chameleon urls.
 *
 * @psalm-suppress UndefinedPropertyAssignment
 * @FIXME Writing data into `$OURLData` when there is no magic `__set` method for them defined.
 */
class TCMSSmartURLHandler_ShopPaymentSofortueberweisungAPI extends TCMSSmartURLHandler_ShopBasketSteps
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = TCMSSmartURLData::GetActive();

        $iMessagePos = strpos($oURLData->sRelativeURL, TShopPaymentHandlerSofortueberweisung::URL_IDENTIFIER);
        $sPaymentPayload = '';
        if (false !== $iMessagePos) {
            TTools::WriteLogEntry('sofortueberweisung Payment Response: '.print_r($oURLData, true), 4, __FILE__, __LINE__);
            $sPaymentMethod = substr($oURLData->sRelativeURL, $iMessagePos + strlen(TShopPaymentHandlerSofortueberweisung::URL_IDENTIFIER));
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
            $aPaymentPayLoad = array();
            if (false !== strpos($sPaymentMethod, '/')) {
                $sPaymentMessage = substr($sPaymentMethod, 0, strpos($sPaymentMethod, '/'));
                $sPaymentPayload = substr($sPaymentMethod, strlen($sPaymentMessage) + 1);
                $aPaymentPayLoadTmp = explode('-', $sPaymentPayload);
                foreach ($aPaymentPayLoadTmp as $sPayLoadItem) {
                    $aItemParts = explode('_', $sPayLoadItem);
                    if (2 == count($aItemParts)) {
                        $aPaymentPayLoad[$aItemParts[0]] = $aItemParts[1];
                    }
                }
                $sPaymentMethod = $sPaymentMessage;
            }
            $this->aCustomURLParameters['cmsPaymentMessage'] = $sPaymentMethod;

            TTools::WriteLogEntry("sofortueberweisung Payment Response [method= <{$sPaymentMethod}> with control <{$sPaymentPayload}> param: ".print_r($aPaymentPayLoad, true), 4, __FILE__, __LINE__);

            if ('notify' == $sPaymentMethod) {
                $oGlobal = TGlobal::instance();
                $oGlobal->SetUserData('cmsPaymentMessage', $sPaymentMethod);
                TTools::WriteLogEntry('sofortueberweisung Payment notify Response', 4, __FILE__, __LINE__);
                /** @var TShopPaymentHandlerSofortueberweisung $oPaymentHandler */
                $oPaymentHandler = TdbShopPaymentHandler::GetNewInstance();
                $oPaymentHandler->LoadFromField('cmsident', $aPaymentPayLoad['idnt']);
                $activePortal = $this->getPortalDomainService()->getActivePortal();
                try {
                    /** @var TShopPaymentHandlerSofortueberweisung $oPaymentHandler */
                    $oPaymentHandler = $this->getShopPaymentHandlerFactory()->createPaymentHandler($oPaymentHandler->id, $activePortal->id);
                    $oPaymentHandler->HandleNotifyMessage($oGlobal->GetUserData());
                    TTools::WriteLogEntry('sofortueberweisung Payment notify Response completed', 4, __FILE__, __LINE__);
                } catch (ConfigurationException $e) {
                    $this->getLogger()->error(
                        sprintf('Unable to create payment handler: %s', $e->getMessage()),
                        [
                            'paymentHandlerId' => $oPaymentHandler->id,
                            'portalId' => $activePortal->id,
                        ]
                    );
                }
                exit(0);
            }

            // transfer control to the parent, but first we need to transform the request url

            TTools::WriteLogEntry('sofortueberweisung Payment Response cut payment payload from URL. new URL:  '.$oURLData->sRelativeURL, 4, __FILE__, __LINE__);

            $iPageId = parent::GetPageDef();
            if (false !== $iPageId) {
                $oURLData->bPagedefFound = false;
            } // prevent caching
        }

        return $iPageId;
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
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order');
    }
}
