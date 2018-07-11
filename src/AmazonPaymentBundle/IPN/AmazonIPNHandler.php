<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\IPN;

use ChameleonSystem\AmazonPaymentBundle\AmazonDataConverter;
use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonIPNTransactionException;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use TPkgShopPaymentIPNRequest;

class AmazonIPNHandler implements \IPkgShopPaymentIPNHandler
{
    private $amazonConfig = null;

    private $transactionManager = null;

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
        // need to answer with OK
        $this->sentOKHeader();

        $aRequestData = $oRequest->getRequestPayload();
        /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Notification */
        $amazonNotificationObject = $aRequestData['amazonNotificationObject'];
        $amazonReferenceIdManager = $aRequestData['amazonReferenceIdManager'];

        switch ($amazonNotificationObject->getNotificationType()) {
            case 'OrderReferenceNotification':
                /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_OrderReferenceNotification */
                $details = $amazonNotificationObject->getOrderReference();
                $this->handleIPNOrderReferenceNotification($oRequest, $details);

                break;
            case 'AuthorizationNotification':
                /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_AuthorizationNotification */
                $details = $amazonNotificationObject->getAuthorizationDetails();
                $this->handleIPNAuthorizationNotification($oRequest, $details, $amazonReferenceIdManager);
                break;
            case 'CaptureNotification':
                /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_CaptureNotification */
                $details = $amazonNotificationObject->getCaptureDetails();
                $this->handleIPNCaptureNotification($oRequest, $details);
                break;
            case 'RefundNotification':
                /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_RefundNotification */
                $details = $amazonNotificationObject->getRefundDetails();
                $this->handleIPNRefundNotification($oRequest, $details);
                break;
        }

        // if auth is confirmed, then update billing address

        // if capture or refund is confirmed, the transaction handling will take care of things

        // however, we do need to handle the error cases

        return true;
    }

    /**
     * @param TPkgShopPaymentIPNRequest $oRequest
     *
     * @return null|\TdbPkgShopPaymentTransaction
     *
     * @throws \TPkgCmsException_Log
     */
    protected function getTransactionFromRequest(TPkgShopPaymentIPNRequest $oRequest)
    {
        $payload = $oRequest->getRequestPayload();

        /** @var $localIdObject IAmazonReferenceId */
        $localIdObject = $payload['amazonLocalReferenceId'];

        $transactionId = $localIdObject->getTransactionId();

        if (null === $transactionId) {
            throw new AmazonIPNTransactionException(AmazonIPNTransactionException::ERROR_NO_TRANSACTION,
                'there was no transaction connected to the IPN even thought that was expected', array('request' => $oRequest));
        }

        $transaction = \TdbPkgShopPaymentTransaction::GetNewInstance();
        if (false === $transaction->Load($transactionId)) {
            throw new AmazonIPNTransactionException(AmazonIPNTransactionException::ERROR_TRANSACTION_NOT_FOUND,
                'transaction id connected to the IPN is not in the database!', array(
                    'request' => $oRequest,
                    'transactionId' => $transactionId,
                ));
        }

        if ($transaction->fieldShopOrderId !== $oRequest->getOrder()->id) {
            throw new AmazonIPNTransactionException(AmazonIPNTransactionException::ERROR_TRANSACTION_DOES_NOT_MATCH_ORDER,
                'transaction found for the IPN is connected to another order!', array(
                    'request' => $oRequest,
                    'transaction' => $transaction,
                ));
        }

        return $transaction;
    }

    protected function confirmTransaction(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbPkgShopPaymentTransaction $transaction
    ) {
        $transactionManager->confirmTransaction($transaction->fieldSequenceNumber, time());
    }

    /**
     * {@inheritdoc}
     *
     * note: if we return an existing transaction, then it will be marked as completed thus changing the value of the order. If the
     * so we should only return the transaction if the IPN indicates, that all is well. since that will become complicated quickly, we
     * return null in this method in every case, and expect our IPN Handling itself to confirm the transaction
     */
    public function getIPNTransactionDetails(TPkgShopPaymentIPNRequest $oRequest)
    {
        return null;
    }

    private function handleIPNOrderReferenceNotification(
        TPkgShopPaymentIPNRequest $oRequest,
        \OffAmazonPaymentsNotifications_Model_OrderReference $details
    ) {
        $status = $details->getOrderReferenceStatus();
        $bSendInfoToShopOwner = false;
        switch ($status->getState()) {
            case 'Suspended':
                // the buyer must select a another valid payment method for further auth to be called
                $bSendInfoToShopOwner = true;
                break;
            case 'Canceled':
                // ignore sellerCanceled and stale. but do send info in all other cases
                if ('SellerCanceled' !== $status->getReasonCode()) {
                    $bSendInfoToShopOwner = true;
                }
                break;
            case 'Closed':
                // no info on SellerClosed
                if ('SellerClosed' !== $status->getReasonCode()) {
                    $bSendInfoToShopOwner = true;
                }
                break;
        }
        if ($bSendInfoToShopOwner) {
            // for some reason the order reference notification as a different status object then the other notifications - even though it contains the same content
            // as the status object used for the other notifications. To avoid code duplication we create a standard state object using the one returned for the order reference notification
            $normalizedStatus = new \OffAmazonPaymentsNotifications_Model_Status();
            $normalizedStatus->setLastUpdateTimestamp($status->getLastUpdateTimestamp());
            $normalizedStatus->setReasonCode($status->getReasonCode());
            $normalizedStatus->setReasonDescription($status->getReasonDescription());
            $normalizedStatus->setState($status->getState());
            $this->sendShopOwnerStatusChangeInfo($oRequest, $normalizedStatus);
        }
    }

    private function handleIPNAuthorizationNotification(
        TPkgShopPaymentIPNRequest $oRequest,
        \OffAmazonPaymentsNotifications_Model_AuthorizationDetails $authorizationDetails,
        AmazonReferenceIdManager $amazonReferenceIdManager
    ) {
        $status = $authorizationDetails->getAuthorizationStatus();
        $bSendInfoToShopOwner = false;
        switch ($status->getState()) {
            case 'Declined':
                $bSendInfoToShopOwner = true;
                break;
            case 'Closed':
                if (in_array($status->getReasonCode(), array('ExpiredUnused', 'AmazonClosed'))) {
                    $bSendInfoToShopOwner = true;
                }
                break;
        }
        if ($bSendInfoToShopOwner) {
            $this->sendShopOwnerStatusChangeInfo($oRequest, $status);

            return;
        }

        $bHasBillingData = 'Open' === $status->getState();
        $bHasBillingData = $bHasBillingData || ('Closed' === $status->getState() && (in_array(
                    $status->getReasonCode(),
                    array('MaxCapturesProcessed')
                )));
        if ($bHasBillingData) {
            $billingAdrData = $this->getBillingAddressFromAuthorization(
                $oRequest->getOrder()->fieldCmsPortalId,
                $amazonReferenceIdManager->getAmazonOrderReferenceId(),
                $authorizationDetails->getAmazonAuthorizationId()
            );
            $this->updateShopOrderBillingAddress($oRequest->getOrder(), $billingAdrData);
        }
    }

    protected function getBillingAddressFromAuthorization($portalId, $orderReferenceId, $amazonAuthorizationId)
    {
        $orderRefObject = $this->getAmazonConfig($portalId)->amazonOrderReferenceObjectFactory($orderReferenceId);
        $authDetails = $orderRefObject->getAuthorizationDetails($amazonAuthorizationId);
        /** @var $address \OffAmazonPaymentsService_Model_Address */
        $address = $authDetails->getAuthorizationBillingAddress();
        $converter = $this->getAmazonDataConverter();
        $addressData = $converter->convertAddressFromAmazonObjectToLocal($address, AmazonDataConverter::ORDER_ADDRESS_TYPE_BILLING);

        return $converter->convertLocalToOrderAddress(AmazonDataConverter::ORDER_ADDRESS_TYPE_BILLING, $addressData);
    }

    /**
     * @return AmazonDataConverter
     */
    protected function getAmazonDataConverter()
    {
        return new AmazonDataConverter();
    }

    private function handleIPNCaptureNotification(
        TPkgShopPaymentIPNRequest $oRequest,
        \OffAmazonPaymentsNotifications_Model_CaptureDetails $captureDetails
    ) {
        $status = $captureDetails->getCaptureStatus();
        $bSendInfoToShopOwner = false;
        switch ($status->getState()) {
            case 'Declined':
                $bSendInfoToShopOwner = true;
                break;
            case 'Closed':
                if (in_array($status->getReasonCode(), array('AmazonClosed'))) {
                    $bSendInfoToShopOwner = true;
                }
                break;
        }
        if ($bSendInfoToShopOwner) {
            $this->sendShopOwnerStatusChangeInfo($oRequest, $status);

            return;
        }

        // handle success -
        if ('Completed' === $status->getState()) {
            try {
                $transaction = $this->getTransactionFromRequest($oRequest);
                if (false === $transaction->fieldConfirmed) {
                    $this->confirmTransaction($this->getTransactionManager($oRequest->getOrder()), $transaction);
                }
            } catch (AmazonIPNTransactionException $e) {
                $status = new \OffAmazonPaymentsNotifications_Model_Status();
                $status->setState('Completed');
                $status->setReasonCode($e->getErrorCodeAsString());
                $status->setReasonDescription(
                    'unable to find transaction matching IPN - details have been logged. '.(string) $e
                );
                $this->sendShopOwnerStatusChangeInfo($oRequest, $status);
            }
        }
    }

    private function handleIPNRefundNotification(
        TPkgShopPaymentIPNRequest $oRequest,
        \OffAmazonPaymentsNotifications_Model_RefundDetails $refundDetails
    ) {
        $status = $refundDetails->getRefundStatus();
        $bSendInfoToShopOwner = false;
        switch ($status->getState()) {
            case 'Declined':
                $bSendInfoToShopOwner = true;
                break;
        }
        if ($bSendInfoToShopOwner) {
            $this->sendShopOwnerStatusChangeInfo($oRequest, $status);

            return;
        }

        // handle success
        if ('Completed' === $status->getState()) {
            try {
                $transaction = $this->getTransactionFromRequest($oRequest);
                if (false === $transaction->fieldConfirmed) {
                    $this->confirmTransaction($this->getTransactionManager($oRequest->getOrder()), $transaction);
                }
            } catch (AmazonIPNTransactionException $e) {
                $status = new \OffAmazonPaymentsNotifications_Model_Status();
                $status->setState('Completed');
                $status->setReasonCode($e->getErrorCodeAsString());
                $status->setReasonDescription(
                    'unable to find transaction matching IPN - details have been logged. '.(string) $e
                );
                $this->sendShopOwnerStatusChangeInfo($oRequest, $status);
            }
        }
    }

    private function sendShopOwnerStatusChangeInfo(
        TPkgShopPaymentIPNRequest $oRequest,
        \OffAmazonPaymentsNotifications_Model_Status $status
    ) {
        $mailProfile = $this->getMailProfile(AmazonPayment::MAIL_PROFILE_IPN_ERROR);

        $mailProfile->AddDataArray($oRequest->getOrder()->GetSQLWithTablePrefix());

        $status = array(
            'state' => $status->getState(),
            'reasonCode' => $status->getReasonCode(),
            'reasonDescription' => $status->getReasonDescription(),
        );

        $mailProfile->AddData('status', $status);

        $mailProfile->SendUsingObjectView('emails', 'Customer');
    }

    /**
     * @param string $mailProfileCode
     *
     * @return \TDataMailProfile
     */
    protected function getMailProfile($mailProfileCode)
    {
        return \TdbDataMailProfile::GetProfile($mailProfileCode);
    }

    protected function sentOKHeader()
    {
        header('HTTP/1.1 200 OK');
        flush();
        $iMaxDepth = 50;
        do {
            $bFlushSuccess = @ob_end_flush();
        } while ($iMaxDepth > 0 && true === $bFlushSuccess);
    }

    /**
     * @return AmazonPaymentGroupConfig
     */
    protected function getAmazonConfig($portalId)
    {
        if (null !== $this->amazonConfig) {
            return $this->amazonConfig;
        }

        return AmazonPaymentConfigFactory::createConfig($portalId);
    }

    /**
     * @param AmazonPaymentGroupConfig $amazonConfig
     */
    public function setAmazonConfig(AmazonPaymentGroupConfig $amazonConfig)
    {
        $this->amazonConfig = $amazonConfig;
    }

    protected function updateShopOrderBillingAddress(\TdbShopOrder $order, array $billingAddress)
    {
        $allowEdit = $order->AllowEdit();
        $order->AllowEditByAll(true);
        $order->SaveFieldsFast($billingAddress);
        $order->AllowEditByAll($allowEdit);
    }

    /**
     * @param \TdbShopOrder $order
     *
     * @return null|\TPkgShopPaymentTransactionManager
     */
    protected function getTransactionManager(\TdbShopOrder $order)
    {
        if (null !== $this->transactionManager) {
            return $this->transactionManager;
        }

        return new \TPkgShopPaymentTransactionManager($order);
    }

    /**
     * @param \TPkgShopPaymentTransactionManager $transactionManager
     */
    public function setTransactionManager($transactionManager)
    {
        $this->transactionManager = $transactionManager;
    }
}
