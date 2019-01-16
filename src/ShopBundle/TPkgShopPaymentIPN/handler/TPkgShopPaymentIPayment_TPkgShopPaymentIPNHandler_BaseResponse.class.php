<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use Psr\Log\LoggerInterface;

class TPkgShopPaymentIPayment_TPkgShopPaymentIPNHandler_BaseResponse implements IPkgShopPaymentIPNHandler
{
    /**
     * process the IPN request - the request object contains all details (payment handler, group, order etc)
     * the call should return true if processing should continue, false if it is to stop. On Error it should throw an error
     * extending AbstractPkgShopPaymentIPNHandlerException.
     *
     * @param TPkgShopPaymentIPNRequest $oRequest
     * @trows AbstractPkgShopPaymentIPNHandlerException
     *
     * @return bool
     */
    public function handleIPN(TPkgShopPaymentIPNRequest $oRequest)
    {
        $oStatus = $oRequest->getIpnStatus();
        if ($oStatus && 'success' === $oStatus->fieldCode) {
            // #23380 some payment methods redirect the user in the last order step to an external source - there the user
            // confirms (and executes) the payment and is then sent back to our thank-you-page. Only if the user arrives there is
            // the order marked as completed - some users close the browser before arriving back at the shop. these orders are then never
            // marked as completed - even though they are paid. This case should be handled here
            $oOrder = $oRequest->getOrder();
            $aPayload = $oRequest->getRequestPayload();
            $oPaymentHandler = $oOrder->GetPaymentHandler();
            if (!is_null($oPaymentHandler)) {
                $oPaymentHandler->SetPaymentUserData($aPayload);
                $oPaymentHandler->SaveUserPaymentDataToOrder($oOrder->id);
            }
            if ($oOrder && false === $oOrder->fieldSystemOrderSaveCompleted) {
                $aInfo = array();
                if ($oOrder->fieldSystemOrderExportedDate <= '0000-00-00 00:00:00') {
                    $aInfo[] = 'wawi export called';
                    if ($oOrder->ExportOrderForWaWiHook($oOrder->GetPaymentHandler())) {
                        $oOrder->MarkOrderAsExportedToWaWi(true);
                        $aInfo[] = 'wawi export success';
                    }
                }

                // send notification only if not already send
                if (false == $oOrder->fieldSystemOrderNotificationSend) {
                    $aInfo[] = 'user order notification sent';
                    if (true === $oOrder->SendOrderNotification()) {
                        $aInfo[] = 'notification sent success';
                    }
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
                $log = $this->getLogger();
                $log->info(
                    sprintf('IPN marked order %s (id: %s) as completed when it was marked as paid.', $oOrder->fieldOrdernumber, $oOrder->id),
                    $aInfo
                );
            }
        }

        return true;
    }

    /**
     * return an instance of TPkgShopPaymentIPN_TransactionDetails if your IPN should trigger a transaction for the order
     * (ie payment or refunds etc). if you return null, then no transaction will be triggered.
     *
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @return null|TPkgShopPaymentIPN_TransactionDetails
     */
    public function getIPNTransactionDetails(TPkgShopPaymentIPNRequest $oRequest)
    {
        $oStatus = $oRequest->getIpnStatus();
        if (null === $oStatus) {
            return null;
        }
        $aPayload = $oRequest->getRequestPayload();

        $sTransactionType = TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_PAYMENT;
        $sSequenceNumber = (isset($aPayload['ret_trx_number'])) ? ($aPayload['ret_trx_number']) : (null);
        $iTransactionTimestamp = (isset($aPayload['ret_transdate']) && isset($aPayload['ret_transtime'])) ? (strtotime(
            $aPayload['ret_transdate'].' '.$aPayload['ret_transtime']
        )) : (null);
        if (null === $iTransactionTimestamp) {
            $iTransactionTimestamp = time();
        }
        $sErrorCode = (isset($aPayload['ret_errorcode'])) ? ($aPayload['ret_errorcode']) : (0);
        $sContextDetail = '';
        if (0 != $sErrorCode) {
            $sContextDetail = 'IPayment Fail error code: '.$sErrorCode;
        }
        $dPrice = $sErrorCode = (isset($aPayload['trx_amount'])) ? ($aPayload['trx_amount'] / 100) : (0);

        $oTransactionDetails = new TPkgShopPaymentIPN_TransactionDetails(
            $dPrice,
            $sTransactionType,
            $sContextDetail,
            $sSequenceNumber,
            $iTransactionTimestamp
        );

        return $oTransactionDetails;
    }

    private function getLogger(): LoggerInterface
    {
        return ServiceLocator::get('monolog.logger.order');
    }
}
