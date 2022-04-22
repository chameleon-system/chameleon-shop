<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

/**
 * !!!ATTENTION: This never worked as expected, so if you want to use PayPal with OGONE, you have to work on this.
 *
/**/
class TShopPaymentHandlerOgoneBase extends TdbShopPaymentHandler
{
    const URL_IDENTIFIER = 'ogone_cms_call';
    const URL_IDENTIFIER_NOTIFY = 'ogonenotifycmscall';

    /**
     * Get the active currency.
     *
     * @return string
     */
    protected function GetCurrency()
    {
        return 'EUR';
    }

    /**
     * return SEO URL for the requested response.
     *
     * @param string $sResponse
     *
     * @return string
     */
    protected function GetResponseURL($sResponse)
    {
        $oActivePage = $this->getActivePageService()->getActivePage();
        $sReturnURLBase = $oActivePage->GetRealURLPlain(array(), true);
        if ('.html' === substr($sReturnURLBase, -5)) {
            $sReturnURLBase = substr($sReturnURLBase, 0, -5);
        }
        if ('/' !== substr($sReturnURLBase, -1)) {
            $sReturnURLBase .= '/';
        }

        return $sReturnURLBase.self::URL_IDENTIFIER.'/'.$sResponse;
    }

    /**
     * Get the payment service URL to redirect or send post request.
     *
     * @return string
     */
    protected function GetPaymentURL()
    {
        if (IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION === $this->getEnvironment()) {
            $sPaymentURL = $this->GetConfigParameter('sOgonePaymentURLLive');
        } else {
            $sPaymentURL = $this->GetConfigParameter('sOgonePaymentURLTest');
        }

        return $sPaymentURL;
    }

    /**
     * build the outgoing hash string from input array.
     *
     * HEADS UP! the OUT key in the shop config has to match the IN Key in the Ogone backend
     * and vice versa
     *
     * @param array $aParameter
     *
     * @return string
     */
    protected function BuildOutgoingHash($aParameter)
    {
        $sHash = '';
        $sSharedSecret = $this->GetConfigParameter('sSharedSecretOut');
        if ('' != $sSharedSecret) {
            ksort($aParameter);
            foreach ($aParameter as $sParameterName => $sParameterValue) {
                if ('' != $sParameterValue || is_numeric($sParameterValue)) {
                    $sHash .= strtoupper($sParameterName).'='.$sParameterValue.$sSharedSecret;
                }
            }
            $sHash = hash('sha256', $sHash);
        }

        return $sHash;
    }

    /**
     * Generate hash with transfer parameter and saved shared secret and compare it with transfer hash.
     *
     * HEADS UP! the OUT key in the shop config has to match the IN Key in the Ogone backend
     * and vice versa
     *
     * @param array $aURLParameter
     *
     * @return bool
     */
    protected function CheckIncomingHash($aURLParameter)
    {
        $bIsValid = false;
        $oGlobal = TGlobal::instance();
        $sSharedSecret = $this->GetConfigParameter('sSharedSecretIn');
        $sIncomingHash = $oGlobal->GetUserData('SHASIGN');
        $aHashParameter = $this->GetIncomingPaymentParameter();
        $sHash = '';
        $aURLParameter = array_change_key_case($aURLParameter, CASE_UPPER);
        if ('' != $sIncomingHash) {
            foreach ($aHashParameter as $sHashParameter) {
                if (array_key_exists($sHashParameter, $aURLParameter) && '' != $aURLParameter[$sHashParameter]) {
                    $sHash .= $sHashParameter.'='.$aURLParameter[$sHashParameter].$sSharedSecret;
                }
            }
            $sHash = strtoupper(hash('sha256', $sHash));
            if ($sHash == $sIncomingHash) {
                $bIsValid = true;
            }
        } else {
            $bIsValid = true;
        }

        return $bIsValid;
    }

    /**
     * Returns all possible parameter Ogone sends back to shop.
     * Was needed for hash checking and to save user payment data.
     *
     * @return array
     */
    protected function GetIncomingPaymentParameter()
    {
        return array('AAVADDRESS', 'AAVCHECK', 'AAVZIP', 'ACCEPTANCE', 'ALIAS', 'AMOUNT', 'BIN', 'BRAND', 'CARDNO', 'CCCTY', 'CN', 'COMPLUS', 'CREATION_STATUS', 'CURRENCY', 'CVCCHECK', 'DCC_COMMPERCENTAGE', 'DCC_CONVAMOUNT', 'DCC_CONVCCY', 'DCC_EXCHRATE', 'DCC_EXCHRATESOURCE', 'DCC_EXCHRATETS', 'DCC_INDICATOR', 'DCC_MARGINPERCENTAGE', 'DCC_VALIDHOURS', 'DIGESTCARDNO', 'ECI', 'ED', 'ENCCARDNO', 'IP', 'IPCTY', 'NBREMAILUSAGE', 'NBRIPUSAGE', 'NBRIPUSAGE_ALLTX', 'NBRUSAGE', 'NCERROR', 'ORDERID', 'PAYID', 'PM', 'SCO_CATEGORY', 'SCORING', 'STATUS', 'SUBSCRIPTION_ID', 'TRXDATE', 'VC');
    }

    /**
     * method handles the server response from Ogone - this will set/update the status of the
     * order.
     *
     * @param array $aParameter
     *
     * @return bool
     */
    public function HandleNotifyMessage($aParameter)
    {
        TTools::WriteLogEntry('OGONE: handle notify message: '.print_r($aParameter, true), 1, __FILE__, __LINE__);
        if ($this->CheckIncomingHash($aParameter)) {
            $aParameter = array_change_key_case($aParameter, CASE_UPPER);
            $oOrder = TdbShopOrder::GetNewInstance();
            if ($oOrder->LoadFromField('ordernumber', $aParameter['ORDERID'])) {
                $sPaymentState = $aParameter['STATUS'];
                $this->HandleNotifyMessageForPaymentState($sPaymentState, $aParameter, $oOrder);
            } else {
                TTools::WriteLogEntry('OGONE: handle notify message order ('.$aParameter['ORDERID'].') not exists', 1, __FILE__, __LINE__);
            }
        } else {
            TTools::WriteLogEntry('OGONE: handle notify message incorrect hash check: ', 1, __FILE__, __LINE__);
        }

        return true;
    }

    /**
     * Handles Ogone notify and update order state depending on notify state.
     *
     * @param string       $sPaymentState state of the notified transaction
     * @param array        $aParameter
     * @param TdbShopOrder $oOrder
     *
     * @return void
     */
    protected function HandleNotifyMessageForPaymentState($sPaymentState, $aParameter, $oOrder)
    {
        if (!$oOrder->fieldOrderIsPaid) {
            if ('9' === $sPaymentState) {
                $oOrder->SetStatusPaid(true);
                $oOrder->SetStatusCanceled(false);
                $this->HandleNotifyOrderOnChange($oOrder, true, true, true);
                TTools::WriteLogEntry('OGONE: handle notify message order ('.$aParameter['ORDERID'].') was activated and paid by notify (Status 9 paid)', 1, __FILE__, __LINE__);
            } elseif ('5' === $sPaymentState) {
                if ($oOrder->fieldCanceled || !$oOrder->fieldSystemOrderSaveCompleted) {
                    $oOrder->SetStatusCanceled(false);
                    $oOrder->SetStatusPaid(false);
                    TTools::WriteLogEntry('OGONE: handle notify message order ('.$aParameter['ORDERID'].') was activated by notify and set to not paid (Status 5 reservation)', 1, __FILE__, __LINE__);
                    $this->HandleNotifyOrderOnChange($oOrder, false, !$oOrder->fieldSystemOrderSaveCompleted, true);
                }
            } else {
                if ('6' === $sPaymentState || '1' === $sPaymentState || '2' === $sPaymentState || '0' === $sPaymentState) {
                    $oOrder->SetStatusCanceled(true);
                    $oOrder->SetStatusPaid(false);
                    $this->HandleNotifyOrderOnChange($oOrder, false, false, true);
                    TTools::WriteLogEntry('OGONE: handle notify message order ('.$aParameter['ORDERID'].') was canceled and set to not paid by notify (Status 0,1,2,6)', 1, __FILE__, __LINE__);
                }
            }
        } else {
            TTools::WriteLogEntry('OGONE: handle notify message order ('.$aParameter['ORDERID'].') was already paid nothing to do)', 1, __FILE__, __LINE__);
        }
    }

    /**
     * If Ogone notify transaction state change the order then complete the order(save Payment data to order, Wawi Export,notification Email etc.).
     *
     * @param TdbShopOrder $oOrder
     * @param bool         $bAllowExportWawi
     * @param bool         $bAllowSendNotificationEmail
     * @param bool         $bAllowSaveNewPaymentData
     *
     * @return void
     */
    protected function HandleNotifyOrderOnChange($oOrder, $bAllowExportWawi, $bAllowSendNotificationEmail, $bAllowSaveNewPaymentData)
    {
        if ($bAllowSaveNewPaymentData) {
            $this->SaveUserPaymentDataToOrder($oOrder->id);
        }
        if ($oOrder->fieldSystemOrderExportedDate <= '0000-00-00 00:00:00' && $bAllowExportWawi) {
            if ($oOrder->ExportOrderForWaWiHook($this)) {
                $oOrder->MarkOrderAsExportedToWaWi(true);
            } else {
                TTools::WriteLogEntry('OGONE: handle notify message order ('.$oOrder->fieldOrdernumber.') error in export wawi ', 1, __FILE__, __LINE__);
            }
        }
        if (false === $oOrder->fieldSystemOrderNotificationSend && $bAllowSendNotificationEmail) {
            $oOrder->SendOrderNotification();
        }
        $oOrder->CreateOrderInDatabaseCompleteHook();
        if (false === $oOrder->fieldSystemOrderPaymentMethodExecuted) {
            $aData = $oOrder->sqlData;
            $aData['system_order_payment_method_executed'] = '1';
            $aData['system_order_payment_method_executed_date'] = date('Y-m-d H:i:s');
            $oOrder->LoadFromRow($aData);
            $oOrder->AllowEditByAll(true);
            $oOrder->Save();
        }
    }

    /**
     * load user payment data.
     *
     * @return array
     */
    protected function GetUserPaymentData()
    {
        parent::GetUserPaymentData();
        $oGlobal = TGlobal::instance();
        $aPossibleReturnParameterList = $this->GetIncomingPaymentParameter();
        $aReturnedParameter = $oGlobal->GetUserData();
        $aReturnedParameter = array_change_key_case($aReturnedParameter, CASE_UPPER);
        foreach ($aPossibleReturnParameterList as $sPossibleParameterName) {
            if (array_key_exists($sPossibleParameterName, $aReturnedParameter)) {
                $this->aPaymentUserData[$sPossibleParameterName] = $aReturnedParameter[$sPossibleParameterName];
            }
        }

        return $this->aPaymentUserData;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
