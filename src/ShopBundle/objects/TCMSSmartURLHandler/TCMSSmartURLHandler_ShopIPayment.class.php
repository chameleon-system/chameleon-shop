<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Response;

/**
 * assumes the path in the TCMSSmartURLData is a simple tree path.
 *
 * @psalm-suppress UndefinedPropertyAssignment
 * @FIXME Writing data into `$OURLData` when there is no magic `__set` method for them defined.
 */
class TCMSSmartURLHandler_ShopIPayment extends TCMSSmartURLHandler_ShopBasketSteps
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();

        $iIPaymentPos = strpos($oURLData->sRelativeURL, TShopPaymentHandlerIPayment::URL_PARAMETER_NAME);
        if (false !== $iIPaymentPos) {
            $sIPaymentCode = substr($oURLData->sRelativeURL, $iIPaymentPos + strlen(TShopPaymentHandlerIPayment::URL_PARAMETER_NAME));
            $oURLData->sRelativeURL = substr($oURLData->sRelativeURL, 0, $iIPaymentPos);
            $aUrlParts = array();
            if (!empty($oURLData->sRelativeURLPortalIdentifier)) {
                $aUrlParts[] = $oURLData->sRelativeURLPortalIdentifier;
            }
            if (!empty($oURLData->sLanguageIdentifier)) {
                $aUrlParts[] = $oURLData->sLanguageIdentifier;
            }
            $sPrefix = implode('/', $aUrlParts);
            if (!empty($sPrefix)) {
                $sPrefix = '/'.$sPrefix;
            }
            $oURLData->sRelativeFullURL = $sPrefix.$oURLData->sRelativeURL;
            $sIPaymentMessage = substr($sIPaymentCode, 1);
            $oStep = $this->GetActiveStep($oURLData);
            $this->SetStepParameter($oStep);

            $aRedirectParameter = $oURLData->aParameters;
            $aRedirectParameter['module_fnc'] = array($oURLData->aParameters['spot'] => 'PostProcessExternalPaymentHandlerHook');
            $aRedirectParameter['cmspaymentstate'] = $sIPaymentMessage;
            $sURL = $oURLData->sRelativeFullURL.'?'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aRedirectParameter));

            $this->getRedirect()->redirect($sURL, Response::HTTP_MOVED_PERMANENTLY);
        }

        return $iPageId;
    }

    /**
     * Set needed parameter for next step like step completed and active payment method.
     *
     * @param TShopOrderStep $oStep
     *
     * @return bool $bParameterSet
     */
    protected function SetStepParameter($oStep)
    {
        $bParameterSet = false;
        $oBasket = TShopBasket::GetInstance();
        $oGlobal = TGlobal::instance();
        $sPaymentMethodId = $oGlobal->GetUserData('shop_payment_method_id');
        $oActivePaymentMethod = TdbShopPaymentMethod::GetNewInstance();
        if ($oActivePaymentMethod->load($sPaymentMethodId)) {
            $bParameterSet = $oBasket->SetActivePaymentMethod($oActivePaymentMethod);
        }

        return $bParameterSet;
    }

    /**
     * Delete existing order if payment wasnt done correctly.
     *
     * @param string $sUniqId
     *
     * @return void
     */
    protected function DeleteNotExecutedOrder($sUniqId)
    {
        $oNotExecutedOrder = TdbShopOrder::GetNewInstance();
        if ($oNotExecutedOrder->LoadFromField('order_ident', $sUniqId)) {
            $oNotExecutedOrder->AllowEditByAll(true);
            $oNotExecutedOrder->Delete();
            $oNotExecutedOrder->AllowEditByAll(false);
            unset($_SESSION[TShopBasket::SESSION_KEY_PROCESSING_BASKET]);
        }
    }
}
