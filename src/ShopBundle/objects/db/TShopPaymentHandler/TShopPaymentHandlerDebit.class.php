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
class TShopPaymentHandlerDebit extends TdbShopPaymentHandler
{
    const MSG_MANAGER_NAME = 'TShopPaymentHandlerDebitMSG';

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerDebit';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['accountOwner'] = '';
        $aData['accountNr'] = '';
        $aData['bankNr'] = '';
        $aData['iban'] = '';
        $aData['bic'] = '';

        return $aData;
    }

    protected function GetUserPaymentData()
    {
        $aData = parent::GetUserPaymentData();
        $process = array('iban', 'bic');
        foreach ($process as $field) {
            if (false === isset($aData[$field])) {
                continue;
            }
            $aData[$field] = mb_strtoupper(str_replace(' ', '', $aData[$field]));
        }

        $this->aPaymentUserData = $aData;

        return $this->aPaymentUserData;
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
            $required = array('iban');
            if (!self::isGermanIBAN($this->aPaymentUserData['iban'])) {
                $required[] = 'bic';
            }
            foreach ($required as $field) {
                if (!isset($this->aPaymentUserData[$field]) || empty($this->aPaymentUserData[$field])) {
                    $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-'.$field, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                    $bIsValid = false;
                }
            }
        }
        if ($bIsValid) {
            $bIsValid = $this->validateIBAN($this->aPaymentUserData['iban']) && $bIsValid;
            $bIsValid = $this->validateBIC($this->aPaymentUserData['bic']) && $bIsValid;
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

        $fields = array('accountOwner', 'accountNr', 'bankNr', 'iban', 'bic');
        $aFilteredData = array();
        foreach ($fields as $field) {
            if (isset($aPaymentData[$field])) {
                $aFilteredData[$field] = $aPaymentData[$field];
            }
        }

        return $aFilteredData;
    }

    private function isGermanIBAN($iban)
    {
        return ('' !== $iban) && ('DE' === substr($iban, 0, 2));
    }

    private function validateIBAN($iban)
    {
        $oMsgManager = TCMSMessageManager::GetInstance();
        $validator = \ChameleonSystem\CoreBundle\ServiceLocator::get('validator');
        $result = $validator->validateValue($iban, new \Symfony\Component\Validator\Constraints\Iban());

        if ($result->count() > 0) {
            // invalid iban
            $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-iban', 'VALIDATOR_CONSTRAINT_FINANCE_IBAN', array('value' => $iban));

            return false;
        }

        return true;
    }

    private function validateBIC($bic)
    {
        if ('' === $bic) {
            return true;
        }

        $oMsgManager = TCMSMessageManager::GetInstance();
        $validator = \ChameleonSystem\CoreBundle\ServiceLocator::get('validator');
        $result = $validator->validateValue($bic, new \esono\pkgCoreValidatorConstraints\finance\Bic());

        if ($result->count() > 0) {
            // invalid iban
            $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-bic', $result->get(0)->getMessageTemplate(), array('value' => $bic));

            return false;
        }

        return true;
    }
}
