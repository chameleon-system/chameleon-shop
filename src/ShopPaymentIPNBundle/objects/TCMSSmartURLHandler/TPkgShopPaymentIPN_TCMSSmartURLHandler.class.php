<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

class TPkgShopPaymentIPN_TCMSSmartURLHandler extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $oURLData = TCMSSmartURLData::GetActive();

        $oGlobal = TGlobal::instance();
        $aRawData = $oGlobal->GetRawUserData();

        $oRequest = new TPkgShopPaymentIPNRequest($oURLData->sRelativeURL, $aRawData);

        if (false === $oRequest->isIPNRequest()) {
            return false;
        }

        $clientIp = $this->getCurrentRequest()->getClientIp();

        $aData = array(
            'datecreated' => date('Y-m-d H:i:s'),
            'payload' => $aRawData,
            'success' => '0',
            'completed' => '0',
            'ip' => $clientIp,
            'request_url' => $oURLData->sOriginalURL,
            'cms_portal_id' => $oURLData->iPortalId,
        );
        $oMessage = TdbPkgShopPaymentIpnMessage::GetNewInstance($aData);
        $oMessage->Save();

        try {
            $oRequest->parseRequest($oMessage);
            if (false === $oRequest->allowRequestFromIP($clientIp)) {
                throw new TPkgShopPaymentIPNException_InvalidIP($clientIp, $oRequest, 'IPN called from an invalid IP', 0);
            }

            $oStatus = $oRequest->getIpnStatus();
            if (null === $oStatus) {
                throw new TPkgShopPaymentIPNException_InvalidStatus($oRequest, 'IPN Request has no status or the status passed is not understood by the payment handler');
            }

            $oMessage->SaveFieldsFast(array('pkg_shop_payment_ipn_status_id' => $oStatus->id));

            $oRequest->getPaymentHandlerGroup()->validateIPNRequestData($oRequest);

            $oRequest->getPaymentHandlerGroup()->handleIPN($oRequest);

            $oMessage->SaveFieldsFast(array('success' => '1', 'completed' => '1'));
        } catch (TPkgShopPaymentIPNException_RequestError $e) {
            // something went wrong - add to message

            $aData = array(
                'success' => '0',
                'completed' => '1',
                'errors' => (string) $e,
                'error_type' => $e->getErrorType(),
            );
            $oMessage->SaveFieldsFast($aData);
            header($e->getResponseHeader());
        }

        exit();
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }
}
