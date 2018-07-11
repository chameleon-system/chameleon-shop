<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle;

use Doctrine\DBAL\Connection;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonRefundAmazonAPIException;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonRefundDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use esono\pkgshoppaymenttransaction\PaymentTransactionHandlerInterface;

class AmazonPayment implements PaymentTransactionHandlerInterface
{
    const ERROR_CODE_INVALID_ADDRESS = 'AMAZON-PAYMENT-ERROR-INVALID-SHIPPING-ADDRESS-SELECTED';
    const ERROR_CODE_API_ERROR = 'AMAZON-PAYMENT-API-ERROR';
    const ERROR_CODE_NO_SHIPPING_ADDRESS = 'AMAZON-PAYMENT-ERROR-NO-SHIPPING-ADDRESS';
    const ERROR_CODE_NO_PAYMENT_PLAN_SET = 'AMAZON-PAYMENT-ERROR-NO-PAYMENT-PLAN-SET';
    const ERROR_CODE_NO_AMOUNT_SET = 'AMAZON-PAYMENT-ERROR-NO-AMOUNT-SET';
    const ERROR_CODE_UNKNOWN_CONSTRAINT = 'AMAZON-PAYMENT-ERROR-UNKNOWN-CONSTRAINT';

    const ERROR_AUTHORIZATION_DECLINED = 'AMAZON-PAYMENT-ERROR-AUTHORIZATION-DECLINED';
    const ERROR_CAPTURE_DECLINED = 'AMAZON-PAYMENT-ERROR-CAPTURE-DECLINED';
    const ERROR_REFUND_DECLINED = 'AMAZON-PAYMENT-ERROR-REFUND-DECLINED';

    const ERROR_NO_CAPTURE_FOUND_FOR_REFUND = 'AMAZON-PAYMENT-ERROR-NO-MATCHING-CAPTURE-FOR-REFUND'; // refundValue, amazonIdListChecked

    const MAIL_PROFILE_IPN_ERROR = 'AMAZON-IPN-ERROR';

    /**
     * auth mode.
     */
    const AUTHORIZATION_MODE_ASYNCHRONOUS = 1;
    const AUTHORIZATION_MODE_SYNCHRONOUS = 2;

    /**
     * @var AmazonPaymentGroupConfig
     */
    private $config = null;

    /**
     * @var AmazonDataConverter
     */
    private $converter = null;

    /**
     * @var Connection
     */
    private $db = null;

    /**
     * @param \IPkgShopOrderPaymentConfig $config
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\IPkgShopOrderPaymentConfig $config)
    {
        if (false === ($config instanceof AmazonPaymentGroupConfig)) {
            throw new \InvalidArgumentException('AmazonPayment expects an instance of AmazonPaymentConfig');
        }
        $this->config = $config;
        $this->converter = new AmazonDataConverter();
    }

    /**
     * @param \TShopBasket         $basket
     * @param \TdbDataExtranetUser $user   The user whose shipping and billing address is to be set. Note that the user object will be changed in memory, but not persistent.
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function updateWithSelectedShippingAddress(\TShopBasket $basket, \TdbDataExtranetUser $user)
    {
        $amazonOrderRef = $this->config->amazonOrderReferenceObjectFactory($basket->getAmazonOrderReferenceId());

        $details = $amazonOrderRef->getOrderReferenceDetails(
            array(
                AmazonOrderReferenceObject::CONSTRAINT_AMOUNT_NOT_SET,
                AmazonOrderReferenceObject::CONSTRAINT_PAYMENT_PLAN_NOT_SET,
                AmazonOrderReferenceObject::CONSTRAINT_UNKNOWN,
            )
        );

        /** @var \OffAmazonPaymentsService_Model_Destination $destination */
        $destination = $details->getDestination();
        /** @var \OffAmazonPaymentsService_Model_Address $physicalDestination */
        $physicalDestination = $destination->getPhysicalDestination();

        $countryId = null;
        $countryCode = $physicalDestination->getCountryCode();
        try {
            $countryId = $this->converter->getCountryIdFromAmazonCountryCode($countryCode, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);
        } catch (\InvalidArgumentException $e) {
            throw new \TPkgCmsException_LogAndMessage(self::ERROR_CODE_INVALID_ADDRESS, array('countryCode' => $countryCode),
                'User selected a shipping address in a country to which shipment is not supported', array(
                    'user' => $user,
                    'apiResponse' => $details,
                )
            );
        }
        $adrData = $this->converter->convertAddressFromAmazonObjectToLocal($physicalDestination, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);

        // we get this far, we have all the data we need to update the user
        $address = new \TdbDataExtranetUserAddress();
        $address->setIsAmazonShippingAddress(true);
        $address->LoadFromRow($adrData);

        $user->setAmazonShippingAddress($address);
    }

    /**
     * if capture on shipping is active, then it will
     *   * auth + capture all downloads plus create a transaction for this value
     *   * auth remaining value
     * if capture on shipping is disabled, then it will
     *   * auth + capture the order value + create a matching transaction.
     *
     * the order object is marked as canceled whenever there is an error and NO payment has been executed (no auth and no capture)
     * if there is an error after a successful auth or capture, then an exception will be thrown but the order is NOT marked as canceled
     * so make sure to use this to decided what to show the user (the user should see the thank you page, with the error message plus the shop
     * owner should be notified)
     *
     * note that auth+capture is always called first. so if that fails, then the order is canceled as well (since there are no open auth after on the order)
     *
     * should the amazon order object have benn confirmed and then we get an exception, then we will try to cancel the order object as well
     *
     * {@inheritdoc}
     */
    public function captureOrder(\TPkgShopPaymentTransactionManager $transactionManager, \TdbShopOrder &$order)
    {
        $transaction = null;

        $amazonOrderRef = $this->config->amazonOrderReferenceObjectFactory($order->getAmazonOrderReferenceId());
        $amazonOrderRef->setOrderReferenceDetails($order);
        $amazonOrderRef->confirmOrderReference();

        // add catch block so we can cancel the order with amazon if anything goes wrong after the confirm
        try {
            // update order shipping address
            $this->updateOrderWithAmazonDataBeforeAuthorize($amazonOrderRef->getOrderReferenceDetails(), $order);

            // auth / auth + capture

            $authWithCapture = array();
            $authNoCapture = array();
            $orderItems = $order->GetFieldShopOrderItemList();
            while ($item = $orderItems->Next()) {
                // if we are to capture on order completion, then every time is in the capture list
                if (false === $this->config->isCaptureOnShipment()) {
                    $authWithCapture[$item->id] = $item->fieldOrderAmount;
                    continue;
                }

                // always capture downloads
                if (true === $item->isDownload()) {
                    $authWithCapture[$item->id] = $item->fieldOrderAmount;
                    continue;
                }

                // in all other cases, we only auth with a capture
                $authNoCapture[$item->id] = $item->fieldOrderAmount;
            }

            $idManager = $this->getAmazonIdReferenceManager($order);

            $hasItemsForCaptureNow = count($authWithCapture) > 0;
            $hasItemsForCaptureLater = count($authNoCapture) > 0;
            if ($hasItemsForCaptureNow) {
                // get items affected by capture now. If there is nothing to capture later, we get all items of the order, otherwise only the items that are
                // in the $authWithCapture list
                $authWithCaptureData = $transactionManager->getTransactionDataFromOrder(
                    \TPkgShopPaymentTransactionData::TYPE_PAYMENT,
                    ($hasItemsForCaptureLater) ? $authWithCapture : null
                );

                $authWithCaptureData->setContext(
                    new \TPkgShopPaymentTransactionContext('amazon auth+capture on order completion (only downloads or pay on order completion)')
                );

                $transaction = $transactionManager->addTransaction($authWithCaptureData);

                $authIdRefObject = $idManager->createLocalAuthorizationReferenceIdWithCaptureNow(
                    IAmazonReferenceId::REQUEST_MODE_SYNCHRONOUS,
                    $authWithCaptureData->getTotalValue(),
                    $transaction->id
                );
                $idManager->persist($this->getDb());

                $response = $amazonOrderRef->authorizeAndCapture(
                    $order,
                    $authIdRefObject->getLocalId(),
                    $authWithCaptureData->getTotalValue(),
                    true
                );
                $authIdRefObject->setAmazonId($response->getAmazonAuthorizationId());

                $matchingLocalAuth = $idManager->findFromLocalReferenceId($authIdRefObject->getLocalId(), IAmazonReferenceId::TYPE_CAPTURE);
                $captureIds = $response->getIdList()->getmember();
                $matchingLocalAuth->setAmazonId($captureIds[0]);
                $idManager->persist($this->getDb());

                if ('Pending' !== $response->getAuthorizationStatus()->getState()) {
                    $transactionManager->confirmTransaction($transaction->fieldSequenceNumber, time());
                }
                if ($response->isSetAuthorizationBillingAddress()) {
                    $this->updateOrderWithAmazonDataAfterAuthorize($order, $response->getAuthorizationBillingAddress());
                }
                if (false === $hasItemsForCaptureLater) {
                    $amazonOrderRef->closeOrderReference(
                        'close order because complete amount was authorised and captured.'
                    );
                }
            }
        } catch (\TPkgCmsException_LogAndMessage $e) {
            // if we get an exception, then no auth was created for the order - so cancel order
            $this->cancelOrder($transactionManager, $order);

            throw $e;
        }

        if ($hasItemsForCaptureLater) {
            try {
                // get items affected by capture later. If there is nothing to capture now, we get all items of the order, otherwise only the items that are
                // in the $authNoCapture list
                $authNoCaptureData = $transactionManager->getTransactionDataFromOrder(
                    \TPkgShopPaymentTransactionData::TYPE_PAYMENT,
                    ($hasItemsForCaptureNow) ? $authNoCapture : null
                );

                $authIdRefObject = $idManager->createLocalAuthorizationReferenceId(
                    IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS,
                    $authNoCaptureData->getTotalValue()
                );
                $idManager->persist($this->getDb());

                $response = $amazonOrderRef->authorize(
                    $order,
                    $authIdRefObject->getLocalId(),
                    $authNoCaptureData->getTotalValue(),
                    false
                );
                $authIdRefObject->setAmazonId($response->getAmazonAuthorizationId());
                $idManager->persist($this->getDb());
            } catch (\TPkgCmsException_LogAndMessage $e) {
                // if there was no auth+capture, then the order can be canceled
                if (false === $hasItemsForCaptureNow) {
                    $this->cancelOrder($transactionManager, $order);
                }
                throw $e;
            }
        }

        return $transaction;
    }

    /**
     * @param \TdbShopOrder $order
     *
     * @return AmazonReferenceIdManager|null
     */
    protected function getAmazonIdReferenceManager(\TdbShopOrder $order)
    {
        $manager = null;
        try {
            $manager = $this->config->amazonReferenceIdManagerFactory(
                $this->getDb(),
                AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_SHOP_ORDER_ID,
                $order->id
            );
        } catch (\InvalidArgumentException $e) {
            $manager = new AmazonReferenceIdManager($order->getAmazonOrderReferenceId(), $order->id);
        }

        return $manager;
    }

    /**
     * {@inheritdoc}
     *
     * - get a free authorization id for the capture.
     *      * we expect there to be only one matching. if so, check if still valid [status]. if not valid, close auth and
     *      * or none at all
     * - create transaction + new local id [either a capture id, or an auth with capture if no authId was found]
     * - if there was an existing auth, then call $amazonOrderRef->captureExistingAuthorization, else call $amazonOrderRef->authorizeAndCapture
     * - check status. if pending we are done, if error, throw exception, if completed, then mark transaction as confirmed
     * - return transaction created
     */
    public function captureShipment(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder &$order,
        $value,
        $invoiceNumber = null,
        array $orderItemList = null
    ) {
        $idManager = $this->getAmazonIdReferenceManager($order);
        $amazonOrderRefObject = $this->config->amazonOrderReferenceObjectFactory(
            $idManager->getAmazonOrderReferenceId()
        );

        $authIdList = $idManager->getListOfAuthorizations();

        $transactionData = $transactionManager->getTransactionDataFromOrder(
            \TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_PAYMENT,
            $orderItemList
        );
        $transactionData->setContext(
            new \TPkgShopPaymentTransactionContext('captureShipment for invoice '.((null !== $invoiceNumber) ? $invoiceNumber : 'null'))
        );

        $transactionData->setTotalValue($value);

        if (null === $authIdList) {
            return $this->captureShipmentViaNewAuthCaptureNow(
                $transactionManager,
                $transactionData,
                $order,
                $value,
                $amazonOrderRefObject,
                $idManager,
                $invoiceNumber
            );
        }

        $authId = $authIdList->getLast();

        // now check if authorization is valid

        $authorizationDetails = $amazonOrderRefObject->getAuthorizationDetails($authId->getAmazonId());

        if (
            AmazonOrderReferenceObject::STATUS_AUTHORIZATION_OPEN !== $authorizationDetails->getAuthorizationStatus()->getState()
        ) {
            return $this->captureShipmentViaNewAuthCaptureNow(
                $transactionManager,
                $transactionData,
                $order,
                $value,
                $amazonOrderRefObject,
                $idManager,
                $invoiceNumber
            );
        }

        return $this->captureShipmentUsingExistingAuth(
            $transactionManager,
            $transactionData,
            $order,
            $value,
            $amazonOrderRefObject,
            $idManager,
            $authId,
            $invoiceNumber
        );
    }

    private function captureShipmentViaNewAuthCaptureNow(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TPkgShopPaymentTransactionData $transactionData,
        \TdbShopOrder &$order,
        $value,
        AmazonOrderReferenceObject $amazonOrderRefObject,
        AmazonReferenceIdManager $idManager,
        $invoiceNumber
    ) {
        $transaction = $transactionManager->addTransaction($transactionData);
        $authWithCaptureRefIdObject = $idManager->createLocalAuthorizationReferenceIdWithCaptureNow(
            IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS,
            $value,
            $transaction->id
        );
        $idManager->persist($this->getDb());
        $response = $amazonOrderRefObject->authorizeAndCapture(
            $order,
            $authWithCaptureRefIdObject->getLocalId(),
            $value,
            false,
            $invoiceNumber
        );
        $authWithCaptureRefIdObject->setAmazonId($response->getAmazonAuthorizationId());
        $matchingLocalAuth = $idManager->findFromLocalReferenceId($authWithCaptureRefIdObject->getLocalId(), IAmazonReferenceId::TYPE_CAPTURE);
        $captureIds = $response->getIdList()->getmember();
        $matchingLocalAuth->setAmazonId($captureIds[0]);

        $idManager->persist($this->getDb());

        return $transaction;
    }

    private function captureShipmentUsingExistingAuth(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TPkgShopPaymentTransactionData $transactionData,
        \TdbShopOrder &$order,
        $value,
        AmazonOrderReferenceObject $amazonOrderRefObject,
        AmazonReferenceIdManager $idManager,
        IAmazonReferenceId $authId,
        $invoiceNumber
    ) {
        $transaction = $transactionManager->addTransaction($transactionData);
        $captureReferenceIdObject = $idManager->createLocalCaptureReferenceId($value, $transaction->id);
        $idManager->persist($this->getDb());
        $response = $amazonOrderRefObject->captureExistingAuthorization(
            $order,
            $authId->getAmazonId(),
            $captureReferenceIdObject->getLocalId(),
            $value,
            $invoiceNumber
        );

        $captureReferenceIdObject->setAmazonId($response->getAmazonCaptureId());
        $idManager->persist($this->getDb());

        if (AmazonOrderReferenceObject::STATUS_CAPTURE_COMPLETED === $response->getCaptureStatus()->getState()) {
            // confirmed \o/
            $transaction = $transactionManager->confirmTransaction($transaction->fieldSequenceNumber, time());
            // also refresh order data
            $order->Load($order->id);
        }

        return $transaction;
    }

    /**
     * {@inheritdoc}
     *
     * will create a transaction and a matching refund request to amazon. should amazon decline the request (or throw an exception),
     * should amazon return a completed status, then the transaction will be marked as completed
     *
     * the method will try to refund based on the best-matching existing capture(s)
     *
     * @throws AmazonRefundDeclinedException
     * @throws AmazonRefundAmazonAPIException
     */
    public function refund(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder &$order,
        $value,
        $invoiceNumber = null,
        $sellerRefundNote = null,
        array $orderItemList = null
    ) {
        $idManager = $this->getAmazonIdReferenceManager($order);

        $amazonOrderReferenceObject = $this->config->amazonOrderReferenceObjectFactory(
            $order->getAmazonOrderReferenceId()
        );

        $refundableCaptures = $amazonOrderReferenceObject->findBestCaptureMatchForRefund(
            $idManager->getListOfCaptures(),
            $value
        );

        if (0 === count($refundableCaptures)) {
            $idListChecked = array();
            /** @var $captureIdChecked AmazonReferenceId */
            foreach ($idManager->getListOfCaptures() as $captureIdChecked) {
                $idListChecked[] = $captureIdChecked->getAmazonId();
            }

            throw new \TPkgCmsException_LogAndMessage(
                self::ERROR_NO_CAPTURE_FOUND_FOR_REFUND,
                array(
                    'refundValue' => $value,
                    'amazonIdListChecked' => $idListChecked,
                ),
                "amazon refund failed because the refund value requested could not be matched to any capture ids (order ref: {$order->getAmazonOrderReferenceId(
                )}, orderid: {$order->id})",
                array(
                    'order' => $order->id,
                    'value' => $value,
                    'invoiceNumber' => $invoiceNumber,
                    'sellerRefundNote' => $sellerRefundNote,
                    'orderItemList' => $orderItemList,
                )
            );
        }

        $transactionData = new \TPkgShopPaymentTransactionData($order, \TPkgShopPaymentTransactionData::TYPE_CREDIT);
        if (null !== $orderItemList) {
            $transactionData = $transactionManager->getTransactionDataFromOrder(
                \TPkgShopPaymentTransactionData::TYPE_CREDIT,
                $orderItemList
            );
        }
        $transactionData->setContext(
            new \TPkgShopPaymentTransactionContext('refund for invoice '.((null !== $invoiceNumber) ? $invoiceNumber : 'null').' with note '.((null !== $sellerRefundNote) ? $sellerRefundNote : 'null'))
        );

        $remainingValueToRefund = $value;
        $transactionsCreated = array();
        foreach ($refundableCaptures as $amazonCaptureId => $refundableValue) {
            if (round($remainingValueToRefund, 2) <= 0) {
                break;
            }
            $toRefund = round(min($refundableValue, $remainingValueToRefund), 2);
            $remainingValueToRefund = round($remainingValueToRefund - $toRefund, 2);
            $transactionData->setTotalValue($toRefund);
            try {
                $transaction = $transactionManager->addTransaction($transactionData);
                $localCaptureIdObject = $idManager->createLocalRefundReferenceId($toRefund, $transaction->id);
                $idManager->persist($this->getDb());
                $response = $amazonOrderReferenceObject->refund(
                    $order,
                    $amazonCaptureId,
                    $localCaptureIdObject->getLocalId(),
                    $toRefund,
                    $invoiceNumber,
                    $sellerRefundNote
                );
                $localCaptureIdObject->setAmazonId($response->getAmazonRefundId());
                $idManager->persist($this->getDb());

                if (AmazonOrderReferenceObject::STATUS_REFUND_COMPLETED === $response->getRefundStatus()->getState()) {
                    $transaction = $transactionManager->confirmTransaction($transaction->fieldSequenceNumber, time());
                }

                $transactionsCreated[] = $transaction;
            } catch (AmazonRefundDeclinedException $e) {
                $e->setSuccessfulTransactionList($transactionsCreated);
                throw $e;
            } catch (\TPkgCmsException_LogAndMessage $e) {
                if (self::ERROR_CODE_API_ERROR === $e->getMessageCode()) {
                    throw new AmazonRefundAmazonAPIException($transactionsCreated, $e->getAdditionalData(
                    ), $e->getMessage(), $e->getContextData());
                }
                throw $e;
            }
        }

        return $transactionsCreated;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrder(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder &$order,
        $cancellationReason = null
    ) {
        $amazonOrderReference = $this->config->amazonOrderReferenceObjectFactory($order->getAmazonOrderReferenceId());

        $amazonOrderReference->cancelOrderReference($cancellationReason);
    }

    /**
     * @param \ChameleonSystem\AmazonPaymentBundle\AmazonDataConverter $converter
     */
    public function setConverter($converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param \Doctrine\DBAL\Connection $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDb()
    {
        if (null === $this->db) {
            $this->db = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
        }

        return $this->db;
    }

    /**
     * @return \IPkgCmsCoreLog
     */
    protected function getLog()
    {
    }

    /**
     * @param \OffAmazonPaymentsService_Model_OrderReferenceDetails $orderReferenceDetails
     * @param \TdbShopOrder                                         $order
     */
    private function updateOrderWithAmazonDataBeforeAuthorize(
        \OffAmazonPaymentsService_Model_OrderReferenceDetails $orderReferenceDetails,
        \TdbShopOrder &$order
    ) {
        $converter = new AmazonDataConverter();

        $address = $orderReferenceDetails->getDestination()->getPhysicalDestination();
        $addressData = $converter->convertAddressFromAmazonObjectToLocal($address, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);
        $buyer = $orderReferenceDetails->getBuyer();
        $shippingAddress['user_email'] = $buyer->getEmail();

        $orderData = array(
            'adr_billing_lastname' => $buyer->getName(),
            'adr_billing_telefon' => $buyer->getPhone(),
            'adr_billing_street' => $addressData['street'],
            'adr_shipping_use_billing' => '0',
            'adr_shipping_salutation_id' => '',
            'adr_shipping_firstname' => '',
            'adr_shipping_lastname' => $addressData['lastname'],
            'adr_shipping_street' => $addressData['street'],
            'adr_shipping_streetnr' => '',
            'adr_shipping_company' => $addressData['company'],
            'adr_shipping_additional_info' => $addressData['address_additional_info'],
            'adr_shipping_city' => $addressData['city'],
            'adr_shipping_postalcode' => $addressData['postalcode'],
            'adr_shipping_telefon' => $addressData['telefon'],
            'adr_shipping_country_id' => $addressData['data_country_id'],
            'user_email' => $buyer->getEmail(),
        );
        $allowSave = $order->HasEditByAllPermission();
        $order->AllowEditByAll(true);
        $order->SaveFieldsFast($orderData);
        $order->AllowEditByAll($allowSave);
    }

    private function updateOrderWithAmazonDataAfterAuthorize(\TdbShopOrder &$order, $address)
    {
        $addressData = $this->converter->convertAddressFromAmazonObjectToLocal($address, AmazonDataConverter::ORDER_ADDRESS_TYPE_BILLING);
        $orderAddress = $this->converter->convertLocalToOrderAddress(AmazonDataConverter::ORDER_ADDRESS_TYPE_BILLING, $addressData);
        $allowSave = $order->HasEditByAllPermission();
        $order->AllowEditByAll(true);
        $order->SaveFieldsFast($orderAddress);
        $order->AllowEditByAll($allowSave);
    }
}
