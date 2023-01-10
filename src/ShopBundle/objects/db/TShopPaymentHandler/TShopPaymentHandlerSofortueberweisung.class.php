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
 * Handler build based on the 2.0 API (see documentation in ./TShopPaymentHandlerSofortueberweisung/Handbuch_Eigenintegration_sofortueberweisung.de_v.2.0.pdf
 * Basic Flow
 *  1. The user is shown the payment details in the payment step after selecting sofortüberweisung
 *  2. when clicking on next, the user is moved to the "review order screen"
 *  3. when clicking the "confirm order" button, the order creation process is started
 *     a) order is created
 *     b) user is redirected to the sofortüberweisungspage
 *     c) on success, the user is returned, the order is marked as paid, and the thankyou page is shown
 *     d) on failure, the order is marked as canceled, the user is returned to the overview page with the error message.
 *
 * note: sofortüberweisung sends the REAL order status via a notify url. the notify is the real status - so it will
 *       overwrite the status set for the order
 *
/**/
use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

class TShopPaymentHandlerSofortueberweisung extends TdbShopPaymentHandler
{
    const URL_IDENTIFIER = '_sftransferapi_';

    /**
     * pass control to sofortueberweisung.de - the method is called after the order is created, but before the order is
     * marked as completed/paid
     * we mark our session as payment interrupted. Control will be passed upon return to the OnPaymentSuccessHook
     * AFTER we return from GUI. So OnPaymentSuccessHook will need to check the response.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        TTools::WriteLogEntry('In ExecutePayment Sofortueberweisung for order id '.$oOrder->id." (nr: {$oOrder->fieldOrdernumber}", 4, __FILE__, __LINE__);
        $bPaymentOk = parent::ExecutePayment($oOrder);
        if ($bPaymentOk) {
            $sExternalPaymentURL = $this->GetExternalPaymentHandlerURL($oOrder);
            TTools::WriteLogEntry('sofortüberweisung: request string '.$sExternalPaymentURL, 4, __FILE__, __LINE__);
            TdbShopPaymentHandler::SetExecutePaymentInterrupt(true);
            $this->getRedirect()->redirect($sExternalPaymentURL);
        }

        return $bPaymentOk;
    }

    /**
     * return url to sofortueberweisung.de.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return string
     */
    protected function GetExternalPaymentHandlerURL(TdbShopOrder $oOrder)
    {
        $aParameter = array('hash' => '', 'user_id' => $this->GetConfigParameter('user_id'), // Ihre Kundennummer bei sofortüberweisung.de
            'project_id' => $this->GetConfigParameter('project_id'), // Ihre Projektnummer bei sofortüberweisung.de
            'amount' => $oOrder->fieldValueTotal, 'currency_id' => 'EUR', 'interface_version' => 'es_ch_v3.3',
        );
        $aReason = $this->GetTransferText($oOrder);
        foreach ($aReason as $sKey => $sValue) {
            $aParameter[$sKey] = $sValue;
        }
        $aUserParameter = $this->GetTransferUserVariables($oOrder);
        foreach ($aUserParameter as $sKey => $sValue) {
            $aParameter[$sKey] = $sValue;
        }
        TTools::WriteLogEntry('sofortüberweisung: request data '.print_r($aParameter, true), 4, __FILE__, __LINE__);

        $aParameter['hash'] = $this->BuildOutgoingHash($aParameter);

        $sExternalHandlerURL = $this->GetConfigParameter('apiURL').'?'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aParameter));

        return $sExternalHandlerURL;
    }

    /**
     * return user vars - they are passed through to sofortüberwiesung and back.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return array
     */
    protected function GetTransferUserVariables(TdbShopOrder $oOrder)
    {
        $aUserVar = array();
        $sReturnURLBase = $this->getActivePageService()->getActivePage()->GetRealURLPlain(array(), true);
        if ('.html' === substr($sReturnURLBase, -5)) {
            $sReturnURLBase = substr($sReturnURLBase, 0, -5);
        }
        if ('/' !== substr($sReturnURLBase, -1)) {
            $sReturnURLBase .= '/';
        }
        $sReturnURLBase.self::URL_IDENTIFIER;

        $sSpot = '';
        $oGlobal = TGlobal::instance();
        if ($oGlobal) {
            $oModule = $oGlobal->GetExecutingModulePointer();
            if ($oModule) {
                $sSpot = $oModule->sModuleSpotName;
            }
        }

        $aUserVar['user_variable_0'] = $this->GetResponseURL('success');
        $aUserVar['user_variable_1'] = $this->GetResponseURL('failure');
        $aUserVar['user_variable_2'] = $this->GetResponseURL('notify').'/idnt_'.$this->sqlData['cmsident'];
        $aUserVar['user_variable_3'] = $oOrder->id;
        $aUserVar['user_variable_4'] = $sSpot;
        $aUserVar['user_variable_5'] = $this->sqlData['cmsident'];

        return $aUserVar;
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
        $sReturnURLBase = $this->getActivePageService()->getActivePage()->GetRealURLPlain(array(), true);
        if ('.html' === substr($sReturnURLBase, -5)) {
            $sReturnURLBase = substr($sReturnURLBase, 0, -5);
        }
        if ('/' !== substr($sReturnURLBase, -1)) {
            $sReturnURLBase .= '/';
        }
        // remove http:// and https:// since these are set in the interface
        $sReturnURLBase = str_replace('https://', '', $sReturnURLBase);
        $sReturnURLBase = str_replace('http://', '', $sReturnURLBase);

        return $sReturnURLBase.self::URL_IDENTIFIER.$sResponse;
    }

    /**
     * return array with text lines for the bank transfer.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return array
     */
    protected function GetTransferText(TdbShopOrder $oOrder)
    {
        $aReason = array('reason_1' => 'BNR '.$oOrder->fieldOrdernumber, 'reason_2' => 'KNR '.$oOrder->fieldCustomerNumber);

        return $aReason;
    }

    /**
     * The method is called from TShopBasket AFTER ExecutePayment was successfully executed.
     * The method is ALSO called, if the payment handler passed execution to an external service from within the ExecutePayment
     * Method. Note: if you return false, then the system will return to the called order step and the
     * order will be canceled (shop_order.canceled = 1) - the article stock will be returned.
     *
     * if you return true, then the order is marked as paid.
     *
     * Note: system_order_payment_method_executed will be set to true either way
     *
     * In sofortueberweisung we only arrive at this method, when returning from the GUI Form. We need to check if
     * a) the order has already been marked as paid (as may be the case if the notify URL arrives BEFORE the user returns)
     * b) the payment was a success
     * c) there was an error in the payment.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function PostExecutePaymentHook(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::PostExecutePaymentHook($oOrder, $sMessageConsumer);

        if ($bPaymentOk) {
            $bPaymentOk = false;
            // we never mark the order as paid... we wait for the notification to do that....
            // but we check if we allow the user to continue, or if we need to abort due to an error
            $oGlobal = TGlobal::instance();
            if ('success' == $oGlobal->GetUserData('cmsPaymentMessage')) {
                // validate data...
                $bPaymentOk = $this->ValidateResponseData($oGlobal->GetUserData(), $oOrder, $sMessageConsumer);

                if ($bPaymentOk) {
                    // payment ok. note: we should not set the status of the order based on this result... instead we need to wait
                    // for the server-to-server notify. so - we need to perform any additional action (marking the order as paid is the job of the notify response
                }
            } elseif ('failure' == $oGlobal->GetUserData('cmsPaymentMessage')) {
                // user clicked abort link. cancel order
                TTools::WriteLogEntry("sofortueberweisung: user aborted payment for order [{$oOrder->id}]", 1, __FILE__, __LINE__);

                if (isset($oOrder->sqlData['internal_comment'])) {
                    $sComment = 'Sofortueberweisung: Payment aborted by user';
                    $aData = $oOrder->sqlData;
                    $aData['internal_comment'] .= $sComment;
                    $oOrder->LoadFromRow($aData);
                    $bEditState = $oOrder->HasEditByAllPermission();
                    $oOrder->AllowEditByAll(true);
                    $oOrder->Save();
                    $oOrder->AllowEditByAll($bEditState);
                }

                $oOrder->SetStatusCanceled(true);
                $bPaymentOk = false;
            } else {
                $bOrderWasProcessed = false;
                //it is possible that order has been processed by notify, so try reloading it from db
                $oProcessedOrder = TdbShopOrder::GetNewInstance();
                if (!empty($oOrder->id) && $oProcessedOrder->Load($oOrder->id)) {
                    if ($oProcessedOrder->fieldOrderIsPaid) {
                        $bOrderWasProcessed = true;
                        $oBasket = TShopBasket::GetInstance();
                        $oOrder = TdbShopOrder::GetNewInstance();
                        $oOrder->LoadFromBasket($oBasket);
                        TdbShopPaymentHandler::SetExecutePaymentInterrupt(false);
                        $oOrderSteps = TdbShopOrderStepList::GetList();
                        $oOrderSteps->ChangeOrderBy(array('position' => 'ASC'));
                        $oFirst = $oOrderSteps->Current();
                        $oStep = TdbShopOrderStep::GetNewInstance();
                        $oStep->JumpToStep($oFirst);
                    }
                }
                if (!$bOrderWasProcessed) {
                    // error - find out what and return false
                    $oURLData = TCMSSmartURLData::GetActive();
                    TTools::WriteLogEntry("sofortueberweisung: unknown response for order [{$oOrder->id}]: ".print_r($oURLData, true), 1, __FILE__, __LINE__);
                    $bPaymentOk = false;
                }
            }
        }

        return $bPaymentOk;
    }

    /**
     * return true if the get/post response data is valid (order exits, amount matches, hash is valid, etc).
     *
     * @param array        $aResponseData
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer
     *
     * @return bool
     */
    protected function ValidateResponseData($aResponseData, TdbShopOrder $oOrder, $sMessageConsumer)
    {
        $bIsValid = true;
        $oGlobal = TGlobal::instance();
        // todo: add user messages on invalid response
        if ($bIsValid) {
            // valid hash?
            $sCalculatedHash = hash('sha256', $oOrder->id.$this->GetConfigParameter('project_password'));
            $sPassedHash = (true == array_key_exists('user_variable_3_hash_pass', $aResponseData)) ? $aResponseData['user_variable_3_hash_pass'] : '';
            if (0 != strcmp($sCalculatedHash, $sPassedHash)) {
                TTools::WriteLogEntry("Sofortüberweisung: unable to validate response hash [{$sPassedHash}] against calculated hash [{$sCalculatedHash}]", 1, __FILE__, __LINE__);
                $bIsValid = false;
            }
        }

        if ($bIsValid) {
            // order matches?
            $sOrderId = (true == array_key_exists('user_variable_3', $aResponseData)) ? $aResponseData['user_variable_3'] : '';
            if (0 != strcmp($sOrderId, $oOrder->id)) {
                TTools::WriteLogEntry("Sofortüberweisung: order ID returned [{$sOrderId}] does not match order ID passed to method [{$oOrder->id}]", 1, __FILE__, __LINE__);
                $bIsValid = false;
            }
        }

        if ($bIsValid) {
            // validate amount
            $iAmount = (true == array_key_exists('amount', $aResponseData)) ? $aResponseData['amount'] : '';
            if ($iAmount != $oOrder->fieldValueTotal) {
                TTools::WriteLogEntry("Sofortüberweisung [order id {$oOrder->id}]: invalid amount paid [{$iAmount}] (should have been [".$oOrder->fieldValueTotal.']) '.print_r($aResponseData, true), 1, __FILE__, __LINE__);
                $bIsValid = false;
            }
        }

        return $bIsValid;
    }

    /**
     * return true if the payment handler connects to a sofort bank.
     *
     * @return bool
     */
    protected function UsesAccountFromSofortBank()
    {
        return false;
    }

    /**
     * build the outgoing hash string from input array.
     *
     * @param array $aParameter
     *
     * @return string
     */
    protected function BuildOutgoingHash($aParameter)
    {
        $sHash = '';
        $aBaseData = array('user_id' => '', 'project_id' => '', 'sender_holder' => '', 'sender_account_number' => '', 'sender_bank_code' => '', 'sender_country_id' => '', 'amount' => '', 'currency_id' => '', 'reason_1' => '', 'reason_2' => '', 'user_variable_0' => '', 'user_variable_1' => '', 'user_variable_2' => '', 'user_variable_3' => '', 'user_variable_4' => '', 'user_variable_5' => '', 'project_password' => $this->GetConfigParameter('project_password'));
        foreach (array_keys($aBaseData) as $sBaseDataKey) {
            if (array_key_exists($sBaseDataKey, $aParameter)) {
                $aBaseData[$sBaseDataKey] = $aParameter[$sBaseDataKey];
            }
        }
        $sRawHash = implode('|', array_values($aBaseData));
        $sHash = hash('sha256', $sRawHash);

        return $sHash;
    }

    /**
     * build incoming hash string from data provided by sofortueberweisung.
     *
     * @param array $aParameter
     *
     * @return string
     */
    protected function BuildIncomingHash($aParameter)
    {
        $sHash = '';
        $aBaseData = array();
        if (false == $this->UsesAccountFromSofortBank()) {
            $aBaseData = array('transaction' => '', 'user_id' => '', 'project_id' => '', 'sender_holder' => '', 'sender_account_number' => '', 'sender_bank_code' => '', 'sender_bank_name' => '', 'sender_bank_bic' => '', 'sender_iban' => '', 'sender_country_id' => '', 'recipient_holder' => '', 'recipient_account_number' => '', 'recipient_bank_code' => '', 'recipient_bank_name' => '', 'recipient_bank_bic' => '', 'recipient_iban' => '', 'recipient_country_id' => '', 'international_transaction' => '', 'amount' => '', 'currency_id' => '', 'reason_1' => '', 'reason_2' => '', 'security_criteria' => '', 'user_variable_0' => '', 'user_variable_1' => '', 'user_variable_2' => '', 'user_variable_3' => '', 'user_variable_4' => '', 'user_variable_5' => '', 'created' => '', //          'status'=>'',
//          'status_modified'=>'',
                'notification_password' => $this->GetConfigParameter('notification_password'), );
        } else {
            $aBaseData = array('transaction' => '', 'user_id' => '', 'project_id' => '', 'sender_holder' => '', 'sender_account_number' => '', 'sender_bank_code' => '', 'sender_bank_name' => '', 'sender_bank_bic' => '', 'sender_iban' => '', 'sender_country_id' => '', 'recipient_holder' => '', 'recipient_account_number' => '', 'recipient_bank_code' => '', 'recipient_bank_name' => '', 'recipient_bank_bic' => '', 'recipient_iban' => '', 'recipient_country_id' => '', 'international_transaction' => '', 'amount' => '', 'currency_id' => '', 'reason_1' => '', 'reason_2' => '', 'security_criteria' => '', 'user_variable_0' => '', 'user_variable_1' => '', 'user_variable_2' => '', 'user_variable_3' => '', 'user_variable_4' => '', 'user_variable_5' => '', 'created' => '', 'notification_password' => $this->GetConfigParameter('notification_password'));
        }

        foreach (array_keys($aBaseData) as $sBaseDataKey) {
            if (array_key_exists($sBaseDataKey, $aParameter)) {
                $aBaseData[$sBaseDataKey] = $aParameter[$sBaseDataKey];
            }
        }
        $sRawHash = implode('|', array_values($aBaseData));
        $sHash = hash('sha256', $sRawHash);

        return $sHash;
    }

    /**
     * method handles the server response from sofortueberweisung - this will set/update the status of the
     * order.
     *
     * @param array<string, mixed> $aParameter
     * @return void
     */
    public function HandleNotifyMessage($aParameter)
    {
        TTools::WriteLogEntry('sofortüberweisung: handle notify message: '.print_r($aParameter, true), 4, __FILE__, __LINE__);

        // tradebyte extension...
        $sOrderId = (array_key_exists('user_variable_3', $aParameter)) ? $aParameter['user_variable_3'] : '';
        $oOrder = TdbShopOrder::GetNewInstance();
        $oOrder->Load($sOrderId);
        $this->SetOwningPaymentMethodId($oOrder->fieldShopPaymentMethodId);

        // if data is valid, mark order as paid
        $bNotifyDataValid = $this->NotifyPayloadIsValid($aParameter);
        if (true === $bNotifyDataValid) {
            // load order
            /** @var $oOrder TdbShopOrder */
            $sOrderId = (array_key_exists('user_variable_3', $aParameter)) ? $aParameter['user_variable_3'] : '';
            $oOrder = TdbShopOrder::GetNewInstance();
            $oOrder->Load($sOrderId);
            if ($oOrder->fieldOrderIsPaid && false) {
                TTools::WriteLogEntry("sofortüberweisung-notify: order [{$oOrder->id}] ALREADY marked as paid: ".print_r($aParameter, true), 4, __FILE__, __LINE__);
                echo 'ALREADY-MARKED-AS-PAID';
            } else {
                // save payment info
                reset($aParameter);
                foreach ($aParameter as $sKey => $sVal) {
                    $query = "INSERT INTO `shop_order_payment_method_parameter`
                              SET `shop_order_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oOrder->id)."',
                                  `name` = '".MySqlLegacySupport::getInstance()->real_escape_string($sKey)."',
                                  `value` = '".MySqlLegacySupport::getInstance()->real_escape_string($sVal)."'
                     ";
                    $iTries = 5;
                    $bDone = false;
                    do {
                        --$iTries;
                        $query = $query.", `id` = '".TTools::GetUUID()."'";
                        MySqlLegacySupport::getInstance()->query($query);
                        $bDone = (MySqlLegacySupport::getInstance()->affected_rows() > 0);
                    } while ($iTries > 0 && false == $bDone);
                }
                reset($aParameter);
                // mark as paid
                $oOrder->SetStatusPaid(true);

                // if the order was marked as canceled (for whatever reason) we reactivate it... after all, it was paid
                if (true == $oOrder->fieldCanceled) {
                    $oOrder->SetStatusCanceled(false);
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

                // trigger export - but only if not already exported
                if ($oOrder->fieldSystemOrderExportedDate <= '0000-00-00 00:00:00') {
                    if ($oOrder->ExportOrderForWaWiHook($this)) {
                        $oOrder->MarkOrderAsExportedToWaWi(true);
                    } else {
                        TTools::WriteLogEntry("sofortüberweisung-notify: order [{$oOrder->id}] NOT EXPORTED: ".print_r($aParameter, true), 1, __FILE__, __LINE__);
                    }
                }

                // if the order notification has not been send, do so now
                if (false == $oOrder->fieldSystemOrderNotificationSend) {
                    $oOrder->SendOrderNotification();
                }

                echo 'OK';
                TTools::WriteLogEntry("sofortüberweisung-notify: order [{$oOrder->id}] marked as paid: ".print_r($aParameter, true), 4, __FILE__, __LINE__);
            }
        } else {
            echo 'FAILED';
            TTools::WriteLogEntry("sofortüberweisung-notify: order NOT marked as paid - response not valid [{$bNotifyDataValid}]: ".print_r($aParameter, true), 1, __FILE__, __LINE__);
        }
    }

    /**
     * validate input. return true if valid, or error string on error.
     *
     * @param array $aParameter
     *
     * @return true|string
     */
    protected function NotifyPayloadIsValid($aParameter)
    {
        $bIsValid = true;

        $aMessage = array('msg' => '', 'line' => 0);
        // validate hash
        $sCalculatedHash = $this->BuildIncomingHash($aParameter);
        $sHash = (array_key_exists('hash', $aParameter)) ? $aParameter['hash'] : '';
        if (0 != strcmp($sHash, $sCalculatedHash)) {
            $aMessage = array('msg' => "sofortueberweisung-notify: calculated hash [{$sCalculatedHash}] does not match hash passed [{$sHash}] for order", 'line' => __LINE__);
            $bIsValid = false;
        }

        /** @var TdbShopOrder $oOrder */
        $oOrder = null;
        if ($bIsValid) {
            // order id exists?
            $sOrderId = (array_key_exists('user_variable_3', $aParameter)) ? $aParameter['user_variable_3'] : '';
            $oOrder = TdbShopOrder::GetNewInstance();
            if (false == $oOrder->Load($sOrderId)) {
                $aMessage = array('msg' => "sofortueberweisung-notify: order id passed [{$sOrderId}] was not found in database: ", 'line' => __LINE__);
                $bIsValid = false;
            }
        }

        if ($bIsValid) {
            // order amount matches?
            $dOrderAmount = (array_key_exists('amount', $aParameter)) ? $aParameter['amount'] : '';
            if (0 != strcmp($dOrderAmount, $oOrder->fieldValueTotal)) {
                $aMessage = array('msg' => "sofortueberweisung-notify: amount paid [{$dOrderAmount}] for order [{$oOrder->id}] does not match order value [{$oOrder->fieldValueTotal}]: ", 'line' => __LINE__);
                $bIsValid = false;
            }
        }

        if (false == $bIsValid) {
            TTools::WriteLogEntry($aMessage['msg'].' DATA: '.print_r($aParameter, true), 1, __FILE__, $aMessage['line']);
            $bIsValid = $aMessage['msg'];
        }

        return $bIsValid;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
