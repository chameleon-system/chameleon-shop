<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * the paymenthandlers are used to handle the different payment methods. They ensure that the right
 * information is collected from the user, and that the payment is executed (as may be the case for online payment)
 * Note that the default handler has no functionality. it must be extended in order to do anything usefull.
/**/
class TShopPaymentHandlerCreditCard extends TdbShopPaymentHandler
{
    const MSG_MANAGER_NAME = 'TShopPaymentHandlerCreditCardMSG';

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerCreditCard';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['creditCardType'] = '';
        $aData['creditCardNumber'] = '';
        $aData['creditCardOwnerName'] = '';
        $aData['creditCardValidToMonth'] = date('n');
        $aData['creditCardValidToYear'] = date('Y');
        $aData['creditCardChecksum'] = '';

        return $aData;
    }

    /**
     * return true if the user data is valid
     * data will be passed as an array.
     *
     * @param array $aUserData - the user data
     *
     * @return bool
     */
    public function ValidateUserInput()
    {
        $bIsValid = parent::ValidateUserInput();

        if ($bIsValid) {
            $oMsgManager = TCMSMessageManager::GetInstance();

            $aDefaultVals = $this->GetDefaultUserPaymentData();

            foreach (array_keys($aDefaultVals) as $sField) {
                if (!array_key_exists($sField, $this->aPaymentUserData) || empty($this->aPaymentUserData[$sField])) {
                    $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-'.$sField, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                    $bIsValid = false;
                }
            }
        }

        return $bIsValid;
    }

    /**
     * executes payment for order.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = true;
        // run order....

        return $bPaymentOk;
    }

    /**
     * store user payment data in order.
     *
     * @param int $iOrderId
     */
    public function SaveUserPaymentDataToOrder($iOrderId)
    {
        $aPayment = $this->aPaymentUserData;
        $this->aPaymentUserData = array();

        $this->aPaymentUserData['creditCardType'] = $aPayment['creditCardType'];
        $this->aPaymentUserData['creditCardNumber'] = str_pad(substr($aPayment['creditCardNumber'], 0, 4), 10, '*');
        $this->aPaymentUserData['creditCardOwnerName'] = $aPayment['creditCardOwnerName'];
        $this->aPaymentUserData['creditCardValidToMonth'] = $aPayment['creditCardValidToMonth'];
        $this->aPaymentUserData['creditCardValidToYear'] = $aPayment['creditCardValidToYear'];
        $this->aPaymentUserData['creditCardChecksum'] = '***';

        parent::SaveUserPaymentDataToOrder($iOrderId);

        $this->aPaymentUserData = $aPayment;
    }
}
