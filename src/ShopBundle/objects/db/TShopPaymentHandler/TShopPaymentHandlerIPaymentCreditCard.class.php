<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerIPaymentCreditCard extends TShopPaymentHandlerIPayment
{
    /**
     * constant for class specific error messages.
     */
    public const MSG_MANAGER_NAME = 'TShopIPaymentHandlerCreditCardMSG';

    /**
     * Get path to view location.
     *
     * @return string
     */
    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerIPaymentCreditCard';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array<string, string>
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['cc_typ'] = '';
        $aData['cc_number'] = '';
        $aData['addr_name'] = '';
        $aData['cc_expdate_month'] = date('n');
        $aData['cc_expdate_year'] = date('Y');
        $aData['cc_expdate_month'] = '';

        return $aData;
    }

    /**
     * Return payment method specific parameter
     * Overwrite this if you want to add specific paramters.
     *
     * @param array $aParameter
     *
     * @return array $aParameter
     */
    protected function GetPaymentTypeSpecifivParameter($aParameter = [])
    {
        if (!is_array($aParameter)) {
            $aParameter = [];
        }
        $aParameter['trx_paymenttyp'] = 'cc';

        return $aParameter;
    }

    /**
     * if request to IPayment was not successfully create a error message.
     *
     * @param string $sMessageConsumer
     *
     * @return void
     */
    protected function SetErrorCodesFromResponseToMessageManager($sMessageConsumer = '')
    {
        if (empty($sMessageConsumer)) {
            $sMessageConsumer = self::MSG_MANAGER_NAME;
        }
        $SReturnMessage = $this->GetErrorCodesFromResponse();
        if (!empty($SReturnMessage)) {
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', ['errorMsg' => $SReturnMessage]);
        }
    }
}
