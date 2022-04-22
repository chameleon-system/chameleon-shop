<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerDataTrans_SwissPostFinance extends TShopPaymentHandlerDataTrans
{
    // **************************************************************************

    /**
     * constant for class specific error messages.
     */
    const MSG_MANAGER_NAME = 'TShopDatatransPostFinanceHandlerdMSG';

    /**
     * constant for class specific payment type.
     */
    const PAYMENT_TYPE = 'swisspostfinance';

    /**
     * constant for class specific payment reference id.
     */
    const PAYMENT_REF_NO_ID = 'pf';

    protected function GetPaymentTypeSpecificParameter()
    {
        $aParameter = parent::GetPaymentTypeSpecificParameter();
        $aParameter['PFC'] = 'PostFinance Card';
        $aParameter['PEF'] = 'PostFinance E-Finance';

        return $aParameter;
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array<string, string>
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['pos_payment_type'] = '';

        return $aData;
    }

    /**
     * Overwrite this to get message manager name for payment.
     *
     * @return string
     */
    public function GetMsgManagerName()
    {
        return self::MSG_MANAGER_NAME;
    }

    /**
     * Overwrite this to get payment type for payment.
     *
     * @return string
     */
    public function GetPaymentType()
    {
        return self::PAYMENT_TYPE;
    }

    /**
     * Get path to view location.
     *
     * @return string
     */
    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerDataTrans_SwissPostFinance';
    }

    /**
     * Get hidden field parameter needed for payment.
     * Add payment type to hidden parameter to. was needed to check correct
     * payment method in authorisation response.
     *
     * @return array
     */
    protected function GetPaymentParameter()
    {
        $aParameter = parent::GetPaymentParameter();
        $aParameter['paymentmethod'] = 'POS';
        $aParameter['paymenttype'] = self::PAYMENT_TYPE;
        $aParameter['currency'] = 'CHF';

        return $aParameter;
    }

    /**
     * Check after response from DataTrans if the payment method is the one which sent request to DataTrans.
     *
     * Checks Swiss PostFinance response parameter.
     *
     * @return bool|mixed $bIsCorrectIPaymentType
     */
    protected function GetPaymentMethodFormAuthorisationRequest()
    {
        $sPostFinancePaymentMethod = '';
        $oGlobal = TGlobal::instance();
        $sPaymentMethod = $oGlobal->GetUserData('pmethod');
        if ('POS' == $sPaymentMethod) {
            $sPostFinancePaymentMethod = $oGlobal->GetUserData('pos_payment_type');
        }

        return $sPostFinancePaymentMethod;
    }

    /**
     * Returns payment id. The Payment Id is a part of the unique reference number.
     *
     * @return string
     */
    protected function GetRefNoPaymentId()
    {
        $sRefnoPaymentId = parent::GetRefNoPaymentId();
        $sRefnoPaymentId .= self::PAYMENT_REF_NO_ID;

        return $sRefnoPaymentId;
    }

    /**
     * return true if the the payment handler may be used by the payment method passed.
     * you can use this hook to disable payment methods based on basket contents, payment method, user data, ...
     *
     * Don't show payment PostFinance if user browser is Chrome or Opera. because PostFinance wont work with them.
     *
     * @param TdbShopPaymentMethod $oPaymentMethod
     *
     * @return bool
     */
    public function AllowUse(TdbShopPaymentMethod &$oPaymentMethod)
    {
        $bAllowUse = parent::AllowUse($oPaymentMethod);
        if ($bAllowUse) {
            if (preg_match('/Chrome/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/Opera/i', $_SERVER['HTTP_USER_AGENT'])) {
                $bAllowUse = false;
            }
        }

        return $bAllowUse;
    }
}
