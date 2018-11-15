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

use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonAuthorizationDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonCaptureDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonRefundDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdList;
use Psr\Log\LoggerInterface;
use TPkgCmsException_LogAndMessage;

/**
 * Class AmazonOrderReference.
 */
class AmazonOrderReferenceObject implements IAmazonOrderReferenceObject
{
    const STATUS_AUTHORIZATION_PENDING = 'Pending';
    const STATUS_AUTHORIZATION_OPEN = 'Open';
    const STATUS_AUTHORIZATION_DECLINED = 'Declined';
    const STATUS_AUTHORIZATION_CLOSED = 'Closed';

    const STATUS_CAPTURE_PENDING = 'Pending';
    const STATUS_CAPTURE_DECLINED = 'Declined';
    const STATUS_CAPTURE_COMPLETED = 'Completed';
    const STATUS_CAPTURE_CLOSED = 'Closed';

    const STATUS_REFUND_PENDING = 'Pending';
    const STATUS_REFUND_DECLINED = 'Declined';
    const STATUS_REFUND_COMPLETED = 'Completed';

    /**
     * @var AmazonPaymentGroupConfig
     */
    private $config;
    /**
     * @var string
     */
    private $amazonOrderReferenceId;

    /**
     * @var AmazonDataConverter|null
     */
    private $converter = null;
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        AmazonPaymentGroupConfig $config,
        $amazonOrderReferenceId,
        LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->amazonOrderReferenceId = $amazonOrderReferenceId;
        $this->converter = new AmazonDataConverter();
        $this->logger = $logger;
    }

    /**
     * @param float $orderValue
     *
     * @return null|\OffAmazonPaymentsService_Model_OrderReferenceDetails
     *
     * @throws TPkgCmsException_LogAndMessage
     */
    public function setOrderReferenceOrderValue($orderValue)
    {
        $request = new \OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);

        $request->setOrderReferenceAttributes(new \OffAmazonPaymentsService_Model_OrderReferenceAttributes());
        $request->getOrderReferenceAttributes()->setPlatformId($this->config->getPlatformId());
        $request->getOrderReferenceAttributes()->setOrderTotal(new \OffAmazonPaymentsService_Model_OrderTotal());

        $request->getOrderReferenceAttributes()->getOrderTotal()->setCurrencyCode('EUR');

        $request->getOrderReferenceAttributes()->getOrderTotal()->setAmount($orderValue);

        $orderReferenceDetails = null;

        // set order reference details
        try {
            $response = $this->config->getAmazonAPI()->setOrderReferenceDetails($request);
            /** @var \OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResult $result */
            $result = $response->getSetOrderReferenceDetailsResult();
            /** @var \OffAmazonPaymentsService_Model_OrderReferenceDetails $orderReferenceDetails */
            $orderReferenceDetails = $result->getOrderReferenceDetails();
            $this->logApiCall($request, $response);
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'setOrderReferenceOrderValue');
        }

        return $orderReferenceDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderReferenceDetails(\TdbShopOrder $order)
    {
        if ($order->getAmazonOrderReferenceId() !== $this->amazonOrderReferenceId) {
            throw new \TPkgCmsException_Log("amazonOrderReferenceId passed via order ({$order->getAmazonOrderReferenceId(
                )}) does not match order ReferenceId passed to constructor ({$this->amazonOrderReferenceId})",
                array(
                    'order' => $order->sqlData,
                    'amazonOrderReferenceId' => $this->amazonOrderReferenceId,
                )
            );
        }

        $shop = $order->GetFieldShop();

        $request = new \OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);

        $request->setOrderReferenceAttributes(new \OffAmazonPaymentsService_Model_OrderReferenceAttributes());
        $request->getOrderReferenceAttributes()->setPlatformId($this->config->getPlatformId());
        $request->getOrderReferenceAttributes()->setOrderTotal(new \OffAmazonPaymentsService_Model_OrderTotal());

        if (method_exists($order, 'GetFieldPkgShopCurrency')) {
            $currency = $order->GetFieldPkgShopCurrency();
            $request->getOrderReferenceAttributes()->getOrderTotal()->setCurrencyCode($currency->fieldIso4217);
        } else {
            $request->getOrderReferenceAttributes()->getOrderTotal()->setCurrencyCode('EUR');
        }

        $request->getOrderReferenceAttributes()->getOrderTotal()->setAmount($order->fieldValueTotal);
        $request->getOrderReferenceAttributes()->setSellerNote($this->config->getSellerOrderNote($order));

        $request->getOrderReferenceAttributes()->setSellerOrderAttributes(
            new \OffAmazonPaymentsService_Model_SellerOrderAttributes()
        );
        $request->getOrderReferenceAttributes()->getSellerOrderAttributes()->setSellerOrderId($order->fieldOrdernumber);
        $request->getOrderReferenceAttributes()->getSellerOrderAttributes()->setStoreName($shop->fieldName);
        $request->getOrderReferenceAttributes()->getSellerOrderAttributes()->setCustomInformation(
            serialize(array('order_id' => $order->id))
        );

        $orderReferenceDetails = null;

        // set order reference details
        try {
            $response = $this->config->getAmazonAPI()->setOrderReferenceDetails($request);
            /** @var \OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResult $result */
            $result = $response->getSetOrderReferenceDetailsResult();
            /** @var \OffAmazonPaymentsService_Model_OrderReferenceDetails $orderReferenceDetails */
            $orderReferenceDetails = $result->getOrderReferenceDetails();
            // check if there are constraints
            if (true === $orderReferenceDetails->isSetConstraints()) {
                $this->logApiError($request, $response);
                throw $this->getConstraintException($orderReferenceDetails->getConstraints());
            }

            $countryCode = $orderReferenceDetails->getDestination()->getPhysicalDestination()->getCountryCode();
            try {
                $this->converter->getCountryIdFromAmazonCountryCode($countryCode, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);
            } catch (\InvalidArgumentException $e) {
                $this->logApiError($request, $response);
                throw new TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_INVALID_ADDRESS, array('countryCode' => $countryCode),
                    'User selected a shipping address in a country to which shipment is not supported', array(
                        'order' => $order->sqlData,
                        'apiResponse' => $orderReferenceDetails,
                    )
                );
            }
            $this->logApiCall($request, $response);
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'setOrderReferenceDetails');
        }

        return $orderReferenceDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function confirmOrderReference()
    {
        $request = new \OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest();
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);
        $request->setSellerId($this->config->getMerchantId());

        try {
            $response = $this->config->getAmazonAPI()->confirmOrderReference($request);
            $this->logApiCall($request, $response);
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'confirmOrderReference');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function authorize(\TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous)
    {
        $request = new \OffAmazonPaymentsService_Model_AuthorizeRequest();
        $request->setAuthorizationReferenceId($localAuthorizationReferenceId);
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);
        $request->setAuthorizationAmount(new \OffAmazonPaymentsService_Model_Price());
        $request->getAuthorizationAmount()->setAmount($amount);

        if (method_exists($order, 'GetFieldPkgShopCurrency')) {
            $currency = $order->GetFieldPkgShopCurrency();
            $request->getAuthorizationAmount()->setCurrencyCode($currency->fieldIso4217);
        } else {
            $request->getAuthorizationAmount()->setCurrencyCode('EUR');
        }

        $request->setSellerAuthorizationNote($this->config->getSellerAuthorizationNote($order, $amount, false));
        $request->setSellerId($this->config->getMerchantId());
        $request->setCaptureNow(false);
        if (true === $synchronous) {
            $request->setTransactionTimeout(0);
        }

        try {
            $response = $this->config->getAmazonAPI()->authorize($request);
            $result = $response->getAuthorizeResult();
            $authDetails = $result->getAuthorizationDetails();

            $status = $authDetails->getAuthorizationStatus();
            if (self::STATUS_AUTHORIZATION_DECLINED === $status->getState()) {
                $this->logApiError($request, $response);
                throw new AmazonAuthorizationDeclinedException($status->getReasonCode(),
                    array(
                        'reasonCode' => $status->getReasonCode(),
                        'reasonDescription' => $status->getReasonDescription(),
                    ),
                    "authorize for order {$order->id} (ref {$this->amazonOrderReferenceId}) with amount {$amount} and local auth id {$localAuthorizationReferenceId} was declined: ({$status->getReasonCode(
                    )}) {$status->getReasonDescription()}",
                    array(
                        'request' => $request,
                        'response' => $response,
                        'orderId' => $order->id,
                        'localAuthorizationReferenceId' => $localAuthorizationReferenceId,
                        'amount' => $amount,
                        'synchronous' => $synchronous,
                    )
                );
            }
            $this->logApiCall($request, $response);

            return $authDetails;
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'authorize');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function authorizeAndCapture(
        \TdbShopOrder $order,
        $localAuthorizationReferenceId,
        $amount,
        $synchronous,
        $invoiceNumber = null
    ) {
        $request = new \OffAmazonPaymentsService_Model_AuthorizeRequest();
        $request->setAuthorizationReferenceId($localAuthorizationReferenceId);
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);
        $request->setAuthorizationAmount(new \OffAmazonPaymentsService_Model_Price());
        $request->getAuthorizationAmount()->setAmount($amount);

        if (method_exists($order, 'GetFieldPkgShopCurrency')) {
            $currency = $order->GetFieldPkgShopCurrency();
            $request->getAuthorizationAmount()->setCurrencyCode($currency->fieldIso4217);
        } else {
            $request->getAuthorizationAmount()->setCurrencyCode('EUR');
        }

        $request->setSellerAuthorizationNote($this->config->getSellerAuthorizationNote($order, $amount, false));
        $request->setSellerId($this->config->getMerchantId());
        $request->setCaptureNow(true);
        $request->setSoftDescriptor($this->config->getSoftDescriptor($order, $invoiceNumber));
        if (true === $synchronous) {
            $request->setTransactionTimeout(0);
        }

        try {
            $response = $this->config->getAmazonAPI()->authorize($request);
            $result = $response->getAuthorizeResult();
            $authDetails = $result->getAuthorizationDetails();

            $status = $authDetails->getAuthorizationStatus();
            if (self::STATUS_AUTHORIZATION_DECLINED === $status->getState()) {
                $this->logApiError($request, $response);
                throw new AmazonAuthorizationDeclinedException($status->getReasonCode(),
                    array(
                        'reasonCode' => $status->getReasonCode(),
                        'reasonDescription' => $status->getReasonDescription(),
                    ),
                    "authorize for order {$order->id} (ref {$this->amazonOrderReferenceId}) with amount {$amount} and local auth id {$localAuthorizationReferenceId} was declined: ({$status->getReasonCode(
                    )}) {$status->getReasonDescription()}",
                    array(
                        'request' => $request,
                        'response' => $response,
                        'orderId' => $order->id,
                        'localAuthorizationReferenceId' => $localAuthorizationReferenceId,
                        'amount' => $amount,
                        'synchronous' => $synchronous,
                    )
                );
            }
            $this->logApiCall($request, $response);

            return $authDetails;
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'authorize');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function captureExistingAuthorization(
        \TdbShopOrder $order,
        $amazonAuthorizationId,
        $localCaptureReferenceId,
        $amount,
        $invoiceNumber = null
    ) {
        $request = new \OffAmazonPaymentsService_Model_CaptureRequest();
        $request->setCaptureAmount(new \OffAmazonPaymentsService_Model_Price());
        $request->getCaptureAmount()->setAmount($amount);
        $request->getCaptureAmount()->setCurrencyCode('EUR');
        $request->setSoftDescriptor($this->config->getSoftDescriptor($order, $invoiceNumber));
        $request->setSellerCaptureNote($this->config->getSellerOrderNote($order));
        $request->setAmazonAuthorizationId($amazonAuthorizationId);
        $request->setCaptureReferenceId($localCaptureReferenceId);
        $request->setSellerId($this->config->getMerchantId());

        try {
            $response = $this->config->getAmazonAPI()->capture($request);
            $details = $response->getCaptureResult()->getCaptureDetails();
            // check state
            if (self::STATUS_CAPTURE_DECLINED === $details->getCaptureStatus()->getState()) {
                $this->logApiError($request, $response);
                throw new AmazonCaptureDeclinedException($details->getCaptureStatus()->getReasonCode(),
                    array(
                        'reasonCode' => $details->getCaptureStatus()->getReasonCode(),
                        'reasonDescription' => $details->getCaptureStatus()->getReasonDescription(),
                    ),
                    "capture for order {$order->id} (ref {$this->amazonOrderReferenceId}) with amount {$amount} and local capture id {$localCaptureReferenceId} was declined: ({$details->getCaptureStatus(
                    )->getReasonCode()}) {$details->getCaptureStatus()->getReasonDescription()}",
                    array(
                        'request' => $request,
                        'response' => $response,
                        'orderId' => $order->id,
                        'amazonAuthorizationId' => $amazonAuthorizationId,
                        'localAuthorizationReferenceId' => $localCaptureReferenceId,
                        'amount' => $amount,
                        'invoiceNumber' => $invoiceNumber,
                    )
                );
            }
            $this->logApiCall($request, $response);

            return $details;
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'authorize');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refund(
        \TdbShopOrder $order,
        $amazonCaptureId,
        $localRefundReferenceId,
        $amount,
        $invoiceNumber = null,
        $sellerRefundNote = null
    ) {
        $request = new \OffAmazonPaymentsService_Model_RefundRequest();
        $request->setSellerId($this->config->getMerchantId());

        $request->setAmazonCaptureId($amazonCaptureId);
        $request->setRefundReferenceId($localRefundReferenceId);

        $request->setRefundAmount(new \OffAmazonPaymentsService_Model_Price());
        $request->getRefundAmount()->setAmount($amount);
        $request->getRefundAmount()->setCurrencyCode('EUR');

        $request->setSoftDescriptor($this->config->getSoftDescriptor($order, $invoiceNumber));
        $request->setSellerRefundNote($sellerRefundNote);

        try {
            $response = $this->config->getAmazonAPI()->refund($request);
            $details = $response->getRefundResult()->getRefundDetails();
            if (self::STATUS_REFUND_DECLINED === $details->getRefundStatus()->getState()) {
                $this->logApiError($request, $response);
                throw new AmazonRefundDeclinedException($details->getRefundStatus()->getReasonCode(),
                    array(
                        'reasonCode' => $details->getRefundStatus()->getReasonCode(),
                        'reasonDescription' => $details->getRefundStatus()->getReasonDescription(),
                    ),
                    "refund for order {$order->id} (ref {$this->amazonOrderReferenceId}) with amount {$amount} and local refund id {$localRefundReferenceId} was declined: ({$details->getRefundStatus(
                    )->getReasonCode()}) {$details->getRefundStatus()->getReasonDescription()}",
                    array(
                        'request' => $request,
                        'response' => $response,
                        'orderId' => $order->id,
                        'amazonCaptureId' => $amazonCaptureId,
                        'localRefundReferenceId' => $localRefundReferenceId,
                        'amount' => $amount,
                        'invoiceNumber' => $invoiceNumber,
                        'sellerRefundNote' => $sellerRefundNote,
                    )
                );
            }
            $this->logApiCall($request, $response);

            return $details;
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'refund');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderReferenceDetails(array $constraintsThatShouldBeIgnored = null)
    {
        if (null !== $constraintsThatShouldBeIgnored) {
            $allowedConstraints = array(
                self::CONSTRAINT_AMOUNT_NOT_SET,
                self::CONSTRAINT_PAYMENT_PLAN_NOT_SET,
                self::CONSTRAINT_SHIPPING_ADDRESS_NOT_SET,
                self::CONSTRAINT_UNKNOWN,
            );
            foreach ($constraintsThatShouldBeIgnored as $ignoreConstraint) {
                if (false === in_array($ignoreConstraint, $allowedConstraints)) {
                    throw new \InvalidArgumentException("you are trying to ignore an unknown constraint [{$ignoreConstraint}].");
                }
            }
        }

        $request = new \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);

        try {
            $response = $this->config->getAmazonAPI()->getOrderReferenceDetails($request);
            $this->logApiCall($request, $response);
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'getOrderReferenceDetails');
        }

        $result = $response->getGetOrderReferenceDetailsResult();
        $orderReferenceDetails = $result->getOrderReferenceDetails();

        if (false === $orderReferenceDetails->isSetConstraints()) {
            return $orderReferenceDetails;
        }

        // handle constraints
        $newConstraintList = array();
        /** @var \OffAmazonPaymentsService_Model_Constraint $constraint */
        foreach ($orderReferenceDetails->getConstraints()->getConstraint() as $constraint) {
            if (null !== $constraintsThatShouldBeIgnored && true === in_array(
                    $constraint->getConstraintID(),
                    $constraintsThatShouldBeIgnored
                )
            ) {
                continue;
            }
            $newConstraintList[] = $constraint;
        }

        if (0 === count($newConstraintList)) {
            return $orderReferenceDetails;
        }

        $newConstraints = new \OffAmazonPaymentsService_Model_Constraints();
        $newConstraints->setConstraint($newConstraintList);

        throw $this->getConstraintException($newConstraints);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationDetails($amazonAuthorizationId)
    {
        $request = new \OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonAuthorizationId($amazonAuthorizationId);

        try {
            $response = $this->config->getAmazonAPI()->getAuthorizationDetails($request);
            $this->logApiCall($request, $response);

            return $response->getGetAuthorizationDetailsResult()->getAuthorizationDetails();
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            // api error
            throw $this->convertApiToLogMessageException($request, $e, 'getAuthorizationDetails');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCaptureDetails($amazonCaptureId)
    {
        $request = new \OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonCaptureId($amazonCaptureId);
        try {
            $response = $this->config->getAmazonAPI()->getCaptureDetails($request);
            $this->logApiCall($request, $response);

            return $response->getGetCaptureDetailsResult()->getCaptureDetails();
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            throw $this->convertApiToLogMessageException($request, $e, 'getCaptureDetails');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRefundDetails($amazonRefundId)
    {
        $request = new \OffAmazonPaymentsService_Model_GetRefundDetailsRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonRefundId($amazonRefundId);
        try {
            $response = $this->config->getAmazonAPI()->getRefundDetails($request);
            $this->logApiCall($request, $response);

            return $response->getGetRefundDetailsResult()->getRefundDetails();
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            throw $this->convertApiToLogMessageException($request, $e, 'getRefundDetails');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOrderReference($cancellationReason = null)
    {
        $request = new \OffAmazonPaymentsService_Model_CancelOrderReferenceRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);
        if (null !== $cancellationReason) {
            $cancellationReason = mb_substr($cancellationReason, 0, 1024);
            $request->setCancelationReason($cancellationReason);
        }

        try {
            $response = $this->config->getAmazonAPI()->cancelOrderReference($request);
            $this->logApiCall($request, $response);
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            throw $this->convertApiToLogMessageException($request, $e, 'cancelOrderReference');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function closeOrderReference($closureReason = null)
    {
        $request = new \OffAmazonPaymentsService_Model_CloseOrderReferenceRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonOrderReferenceId($this->amazonOrderReferenceId);
        if (null !== $closureReason) {
            $closureReason = mb_substr($closureReason, 0, 1024);
            $request->setClosureReason($closureReason);
        }
        try {
            $response = $this->config->getAmazonAPI()->closeOrderReference($request);
            $this->logApiCall($request, $response);
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            throw $this->convertApiToLogMessageException($request, $e, 'closeOrderReference');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function closeAuthorization($amazonAuthorizationId, $closureReason = null)
    {
        $request = new \OffAmazonPaymentsService_Model_CloseAuthorizationRequest();
        $request->setSellerId($this->config->getMerchantId());
        $request->setAmazonAuthorizationId($amazonAuthorizationId);
        if (null !== $closureReason) {
            $closureReason = mb_substr($closureReason, 0, 1024);
            $request->setClosureReason($closureReason);
        }
        try {
            $response = $this->config->getAmazonAPI()->closeAuthorization($request);
            $this->logApiCall($request, $response);
        } catch (\OffAmazonPaymentsService_Exception $e) {
            $this->logApiError($request, $e);
            throw $this->convertApiToLogMessageException($request, $e, 'closeAuthorization');
        }
    }

    /**
     * @param \OffAmazonPaymentsService_Model_Constraints $constraints
     *
     * @return TPkgCmsException_LogAndMessage
     */
    private function getConstraintException(\OffAmazonPaymentsService_Model_Constraints $constraints)
    {
        // although there can be multiple constraints, we want to throw only one exception - so we need to prioritize the constraints
        $constraintPrioList = array(
            'AmountNotSet' => AmazonPayment::ERROR_CODE_NO_AMOUNT_SET,
            'ShippingAddressNotSet' => AmazonPayment::ERROR_CODE_NO_SHIPPING_ADDRESS,
            'PaymentPlanNotSet' => AmazonPayment::ERROR_CODE_NO_PAYMENT_PLAN_SET,
            'PaymentMethodNotAllowed' => AmazonPayment::ERROR_CODE_NO_PAYMENT_PLAN_SET,
        );

        $constraintList = array();
        /** @var \OffAmazonPaymentsService_Model_Constraint $constraint */
        foreach ($constraints->getConstraint() as $constraint) {
            $constraintList[$constraint->getConstraintID()] = $constraint->getDescription();
        }

        foreach ($constraintPrioList as $constraintID => $messageCode) {
            if (isset($constraintList[$constraintID])) {
                return new TPkgCmsException_LogAndMessage($messageCode, array(
                        'constraintID' => $constraintID,
                        'description' => $constraintList[$constraintID],
                    ), 'there were one or more constraint restrictions on a request', array('constraints' => $constraints)
                );
            }
        }

        return new TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_UNKNOWN_CONSTRAINT, array(), 'there were one or more constraint restrictions on a request - but non of the constraints are known', array('constraints' => $constraints));
    }

    /**
     * @param $request
     * @param \OffAmazonPaymentsService_Exception $e
     * @param string                              $methodName
     *
     * @return TPkgCmsException_LogAndMessage
     */
    private function convertApiToLogMessageException($request, \OffAmazonPaymentsService_Exception $e, $methodName)
    {
        return new TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR, array(
            'message' => $e->getMessage(),
            'responseCode' => $e->getStatusCode(),
            'errorCode' => $e->getErrorCode(),
            'errorType' => $e->getErrorType(),
        ), "amazon api error when trying to {$methodName} on order ref {$this->amazonOrderReferenceId}", array(
            'api exception' => (string) $e,
            'request' => $request,
        ));
    }

    private function logApiCall($request, $response)
    {
        if (null === $this->logger) {
            return;
        }

        $requestLogData = (method_exists($request, 'toXML')) ? $request->toXML() : $request;
        $responseLogData = (method_exists($response, 'toXML')) ? $response->toXML() : $response;
        $this->logger->info(
            sprintf('Amazon Payment Request %s with response %s', get_class($request), get_class($response)),
            [
                'request' => $requestLogData,
                'response' => $responseLogData,
            ]
        );
    }

    private function logApiError($request, $response)
    {
        if (null === $this->logger) {
            return;
        }
        $requestLogData = (method_exists($request, 'toXML')) ? $request->toXML() : $request;
        $responseLogData = (method_exists($response, 'toXML')) ? $response->toXML() : $response;
        if ($responseLogData instanceof \Exception) {
            $responseLogData = (string) $responseLogData;
        }
        $this->logger->error(
            sprintf('Error on Amazon Payment Request %s with response %s',  get_class($request), get_class($response)),
            [
                'request' => $requestLogData,
                'response' => $responseLogData,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findBestCaptureMatchForRefund(AmazonReferenceIdList $captureIdList, $refundValue)
    {
        $captureCandidateList = array();

        $amazonCaptureIdListToCheck = array();
        /** @var $item IAmazonReferenceId */
        foreach ($captureIdList->getIterator() as $item) {
            $amazonCaptureIdListToCheck[] = $item->getAmazonId();
        }

        try {
            foreach ($amazonCaptureIdListToCheck as $amazonCaptureId) {
                $captureDetails = $this->getCaptureDetails($amazonCaptureId);
                if (self::STATUS_CAPTURE_COMPLETED !== $captureDetails->getCaptureStatus()->getState()) {
                    continue;
                }
                $refundable = $captureDetails->getCaptureAmount()->getAmount();
                if ($captureDetails->getRefundedAmount()) {
                    $refundable = $refundable - $captureDetails->getRefundedAmount()->getAmount();
                }
                $captureCandidateList[$amazonCaptureId] = $refundable;
            }
        } catch (TPkgCmsException_LogAndMessage $e) {
            throw new TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_NO_CAPTURE_FOUND_FOR_REFUND,
                array(
                    'refundValue' => $refundValue,
                    'amazonIdListChecked' => implode(', ', $amazonCaptureIdListToCheck),
                ),
                'amazon findBestCaptureMatchForRefund failed because the amazon api threw an exception wen geting the capture details',
                array('captureIdList' => $captureIdList, 'refundValue' => $refundValue, 'amazonCaptureIdListToCheck' => $amazonCaptureIdListToCheck, 'exception' => (string) $e)
            );
        }

        // first see if there is an exact match
        foreach ($captureCandidateList as $amazonCaptureId => $refundableValue) {
            if (round($refundableValue, 2) === round($refundValue, 2)) {
                $captureCandidateList = array($amazonCaptureId => $refundValue);
                break;
            }
        }

        reset($captureCandidateList);
        $reducedCaptureCandidateList = $this->reduceArrayToSmallestDistance($refundValue, $captureCandidateList);

        if (0 === count($reducedCaptureCandidateList)) {
            throw new TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_NO_CAPTURE_FOUND_FOR_REFUND,
                array(
                    'refundValue' => $refundValue,
                    'amazonIdListChecked' => implode(', ', $amazonCaptureIdListToCheck),
                ),
                'amazon findBestCaptureMatchForRefund failed',
                array('captureIdList' => $captureIdList, 'refundValue' => $refundValue, 'amazonCaptureIdListToCheck' => $amazonCaptureIdListToCheck, 'captureCandidateList' => $captureCandidateList)
            );
        }

        return $reducedCaptureCandidateList;
    }

    private function reduceArrayToSmallestDistance($cutOff, array $captureCandidateList)
    {
        $cutOff = round($cutOff, 2);

        $sum = round(
            array_reduce(
                $captureCandidateList,
                function ($carry, $refundValue) {
                    if (null === $carry) {
                        $carry = 0;
                    }
                    $carry += $refundValue;

                    return $carry;
                }
            ),
            2
        );

        if ($sum < $cutOff) {
            return null;
        }

        $bestMatch = $captureCandidateList;
        foreach ($captureCandidateList as $amazonCaptureId => $value) {
            $copy = $captureCandidateList;
            unset($copy[$amazonCaptureId]);
            $bestSubMatchList = $this->reduceArrayToSmallestDistance($cutOff, $copy);
            if (null === $bestSubMatchList) {
                continue;
            }
            $subSum = round(
                array_reduce(
                    $bestSubMatchList,
                    function ($carry, $refundValue) {
                        if (null === $carry) {
                            $carry = 0;
                        }
                        $carry += $refundValue;

                        return $carry;
                    }
                ),
                2
            );
            if ($subSum < $sum && $subSum >= $cutOff) {
                $sum = $subSum;
                $bestMatch = $bestSubMatchList;
            }
        }

        return $bestMatch;
    }
}
