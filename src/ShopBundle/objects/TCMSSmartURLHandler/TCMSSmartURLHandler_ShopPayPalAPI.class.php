<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\UrlUtil;
use Symfony\Component\HttpFoundation\Response;

/**
 * takes paypal return urls and mapps them to chameleon urls.
/**/
class TCMSSmartURLHandler_ShopPayPalAPI extends TCMSSmartURLHandler_ShopBasketSteps
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();

        $iPayPalApiPos = strpos($oURLData->sRelativeURL, TShopPaymentHandlerPayPal::URL_IDENTIFIER);
        if (false !== $iPayPalApiPos) {
            $sPayPalMethod = substr($oURLData->sRelativeURL, $iPayPalApiPos + strlen(TShopPaymentHandlerPayPal::URL_IDENTIFIER));
            $oURLData->sRelativeURL = substr($oURLData->sRelativeURL, 0, $iPayPalApiPos);
            $oURLData->sRelativeFullURL = $oURLData->sRelativeURL;
            if (!empty($oURLData->sLanguageIdentifier)) {
                $oURLData->sRelativeFullURL = '/'.$oURLData->sLanguageIdentifier.$oURLData->sRelativeFullURL;
            }
            if (!empty($oURLData->sRelativeURLPortalIdentifier)) {
                $oURLData->sRelativeFullURL = '/'.$oURLData->sRelativeURLPortalIdentifier.$oURLData->sRelativeFullURL;
            }
            $sPayPalMessage = substr($sPayPalMethod, 0, strpos($sPayPalMethod, '/'));
            $sPayPalPayload = substr($sPayPalMethod, strlen($sPayPalMessage) + 1);
            $aPayPalPayLoadTmp = explode('-', $sPayPalPayload);
            $aPayPalPayLoad = array();
            foreach ($aPayPalPayLoadTmp as $sPayLoadItem) {
                $sSplitOffset = strpos($sPayLoadItem, '_');
                if (false !== $sSplitOffset) {
                    $aPayPalPayLoad[substr($sPayLoadItem, 0, $sSplitOffset)] = substr($sPayLoadItem, $sSplitOffset + 1);
                }
            }

            $aRedirectParameter = $oURLData->aParameters;
            switch ($sPayPalMessage) {
                case 'success':
                    $aRedirectParameter['module_fnc'] = array($aPayPalPayLoad['spot'] => 'PostProcessExternalPaymentHandlerHook');
                    break;
                case 'cancel':
                    $aRedirectParameter['paypalreturn'] = '1';
                    break;
                default:
                    break;
            }
            $sURL = $oURLData->sRelativeFullURL.$this->getUrlUtil()->getArrayAsUrl($aRedirectParameter, '?', '&');
            $this->getRedirect()->redirect($sURL, Response::HTTP_MOVED_PERMANENTLY);
        }

        return $iPageId;
    }

    /**
     * @return UrlUtil
     */
    private function getUrlUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url');
    }
}
