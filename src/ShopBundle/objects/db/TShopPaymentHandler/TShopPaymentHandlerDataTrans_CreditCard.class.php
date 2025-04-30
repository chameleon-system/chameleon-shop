<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerDataTrans_CreditCard extends TShopPaymentHandlerDataTrans
{
    // **************************************************************************

    /**
     * constant for class specific error messages.
     */
    public const MSG_MANAGER_NAME = 'TShopDatatransHandlerCreditCarddMSG';

    /**
     * constant for class specific payment type.
     */
    public const PAYMENT_TYPE = 'ceditcard';

    /**
     * constant for class specific payment reference id.
     */
    public const PAYMENT_REF_NO_ID = 'cc';

    /**
     * Get array with all possible payment identifier.
     * Overwrite this if payment method has sub payment methods like Swiss PostFinance.
     *
     * @return array
     */
    protected function GetPaymentTypeSpecificParameter()
    {
        $aParameter = parent::GetPaymentTypeSpecificParameter();
        $aParameter['VIS'] = 'VISA';
        $aParameter['ECA'] = 'MasterCard';
        $aParameter['AMX'] = 'American Express';
        $aParameter['DIN'] = 'Diners Club';

        return $aParameter;
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
     * return the default payment data for the handler.
     *
     * @return array<string, string>
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['cardno'] = '';
        $aData['cvv'] = '';
        $aData['expm'] = date('n');
        $aData['expy'] = date('Y');

        return $aData;
    }

    /**
     * Get path to view location.
     *
     * @return string
     */
    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerDataTrans_CreditCard';
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
        $aParameter['paymenttype'] = self::PAYMENT_TYPE;

        return $aParameter;
    }

    /**
     * Returns payment id. The Payment Id is a part of the unique reference number.
     *
     * @return string
     */
    protected function GetRefNoPaymentId()
    {
        $sRefNoPaymentId = parent::GetRefNoPaymentId();
        $sRefNoPaymentId .= self::PAYMENT_REF_NO_ID;

        return $sRefNoPaymentId;
    }
}
