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
 * /**/
class TShopPaymentHandlerDebitNoSEPA extends TdbShopPaymentHandler
{
    public const MSG_MANAGER_NAME = 'TShopPaymentHandlerDebitMSG';

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerDebit';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array<string, string>
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['accountOwner'] = '';
        $aData['accountNr'] = '';
        $aData['bankNr'] = '';

        return $aData;
    }

    /**
     * return true if the user data is valid
     * data will be passed as an array.
     *
     * @return bool
     */
    public function ValidateUserInput()
    {
        $bIsValid = parent::ValidateUserInput();

        if ($bIsValid) {
            $oMsgManager = TCMSMessageManager::GetInstance();

            if (!array_key_exists('accountOwner', $this->aPaymentUserData) || empty($this->aPaymentUserData['accountOwner'])) {
                $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-accountOwner', 'ERROR-USER-REQUIRED-FIELD-MISSING');
                $bIsValid = false;
            }

            if (!array_key_exists('accountNr', $this->aPaymentUserData) || empty($this->aPaymentUserData['accountNr'])) {
                $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-accountNr', 'ERROR-USER-REQUIRED-FIELD-MISSING');
                $bIsValid = false;
            }

            if (!array_key_exists('bankNr', $this->aPaymentUserData) || empty($this->aPaymentUserData['bankNr'])) {
                $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-bankNr', 'ERROR-USER-REQUIRED-FIELD-MISSING');
                $bIsValid = false;
            }
        }

        return $bIsValid;
    }

    /**
     * executes payment for order.
     *
     * @param string $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = true;
        // run order....

        return $bPaymentOk;
    }

    /**
     * hook is called before the payment data is committed to the database. use it to cleanup/filter/add data you may
     * want to include/exclude from the database.
     *
     * called by the debit handler to restrict data to bank account data
     *
     * @param array $aPaymentData
     *
     * @return array
     */
    protected function PreSaveUserPaymentDataToOrderHook($aPaymentData)
    {
        $aPaymentData = parent::PreSaveUserPaymentDataToOrderHook($aPaymentData);

        $aFilteredData = ['accountOwner' => '', 'accountNr' => '', 'bankNr' => ''];
        if (array_key_exists('accountOwner', $aPaymentData)) {
            $aFilteredData['accountOwner'] = $aPaymentData['accountOwner'];
        }
        if (array_key_exists('accountNr', $aPaymentData)) {
            $aFilteredData['accountNr'] = $this->SanitizePaymentMethodParameterField($aPaymentData['accountNr']);
        }
        if (array_key_exists('bankNr', $aPaymentData)) {
            $aFilteredData['bankNr'] = $this->SanitizePaymentMethodParameterField($aPaymentData['bankNr']);
        }

        return $aFilteredData;
    }
}
