<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerIPaymentDebit extends TShopPaymentHandlerIPayment
{
    /**
     * constant for class specific error messages.
     */
    const MSG_MANAGER_NAME = 'TShopIPaymentHandlerDebitMSG';

    /**
     * Get path to view location.
     *
     * @return string
     */
    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerIPaymentDebit';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['bank_code'] = '';
        $aData['bank_accountnumber'] = '';
        $aData['bank_country'] = '';
        $aData['bank_name'] = '';
        $aData['bank_iban'] = '';
        $aData['bank_bic'] = '';

        return $aData;
    }

    /**
     * Return payment method specific parameter
     * Overwrite this if you want to add specific parameters.
     *
     * @param array $aParameter
     *
     * @return array $aParameter
     */
    protected function GetPaymentTypeSpecifivParameter($aParameter = array())
    {
        if (!is_array($aParameter)) {
            $aParameter = array();
        }
        $aParameter['trx_paymenttyp'] = 'elv';

        return $aParameter;
    }

    /**
     * if request to IPayment was not successfully create a error message.
     */
    protected function SetErrorCodesFromResponseToMessageManager()
    {
        $SReturnMessage = $this->GetErrorCodesFromResponse();
        if (!empty($SReturnMessage)) {
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage(self::MSG_MANAGER_NAME, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => $SReturnMessage));
        }
    }

    /**
     * Get user address data as array
     * On ELV add user name to parameter.
     *
     * @return array $aUserAddressData
     */
    protected function GetUserAddressData()
    {
        $aUserAddressData = parent::GetUserAddressData();
        $oActiveUser = TdbDataExtranetUser::GetInstance();
        if ($oActiveUser) {
            $oBillingAddress = $oActiveUser->GetBillingAddress();
            if ($oBillingAddress) {
                $aUserAddressData['addr_name'] = $oBillingAddress->fieldFirstname.' '.$oBillingAddress->fieldLastname;
            }
        }

        return $aUserAddressData;
    }
}
