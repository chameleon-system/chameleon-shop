<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\Interfaces;

use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonAuthorizationDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonCaptureDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonRefundDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdList;

interface IAmazonOrderReferenceObject
{
    const CONSTRAINT_SHIPPING_ADDRESS_NOT_SET = 'ShippingAddressNotSet';
    const CONSTRAINT_PAYMENT_PLAN_NOT_SET = 'PaymentPlanNotSet';
    const CONSTRAINT_AMOUNT_NOT_SET = 'AmountNotSet';
    const CONSTRAINT_UNKNOWN = 'unknown';

    /**
     * @param AmazonPaymentGroupConfig $config
     * @param string                   $amazonOrderReferenceId
     * @param \IPkgCmsCoreLog          $logger
     */
    public function __construct(AmazonPaymentGroupConfig $config, $amazonOrderReferenceId, \IPkgCmsCoreLog $logger);

    /**
     * @param \TdbShopOrder $order
     *
     * @return \OffAmazonPaymentsService_Model_OrderReferenceDetails
     *
     * @throws \TPkgCmsException_Log
     */
    public function setOrderReferenceDetails(\TdbShopOrder $order);

    /**
     * set the order reference value to the value passed.
     *
     * @param float $orderValue
     *
     * @return \OffAmazonPaymentsService_Model_OrderReferenceDetails
     */
    public function setOrderReferenceOrderValue($orderValue);

    /**
     * the method assumes that the order is free of constraints (successful call to setOrderReferenceDetails)
     * \TPkgCmsException_LogAndMessage.
     */
    public function confirmOrderReference();

    /**
     * performs an auth without capture.
     *
     * @param \TdbShopOrder $order
     * @param string        $localAuthorizationReferenceId
     * @param float         $amount
     * @param bool          $synchronous
     *
     * @return \OffAmazonPaymentsService_Model_AuthorizationDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     * @throws AmazonAuthorizationDeclinedException
     */
    public function authorize(\TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous);

    /**
     * performs an auth with capture.
     *
     * @param \TdbShopOrder $order
     * @param string        $localAuthorizationReferenceId
     * @param float         $amount
     * @param bool          $synchronous
     * @param string        $invoiceNumber                 - 16 char id shown on the payment statement of the buy (usually the order number or the bill number). will be passed to AmazonPaymentGroupConfig::getSellerAuthorizationNote
     *
     * @return \OffAmazonPaymentsService_Model_AuthorizationDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     * @throws AmazonAuthorizationDeclinedException
     */
    public function authorizeAndCapture(
        \TdbShopOrder $order,
        $localAuthorizationReferenceId,
        $amount,
        $synchronous,
        $invoiceNumber = null
    );

    /**
     * @param \TdbShopOrder $order
     * @param string        $amazonAuthorizationId   - the auth id which is to be captured
     * @param string        $localCaptureReferenceId
     * @param float         $amount
     * @param string        $invoiceNumber           - 16 char id shown on the payment statement of the buy (usually the order number or the bill number)
     *
     * @return \OffAmazonPaymentsService_Model_CaptureDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     * @throws AmazonCaptureDeclinedException
     */
    public function captureExistingAuthorization(
        \TdbShopOrder $order,
        $amazonAuthorizationId,
        $localCaptureReferenceId,
        $amount,
        $invoiceNumber = null
    );

    /**
     * @param \TdbShopOrder $order
     * @param string        $amazonCaptureId
     * @param string        $localRefundReferenceId
     * @param float         $amount
     * @param null          $invoiceNumber          - 16 char id shown on the payment statement of the buy (usually the order number or the bill number)
     * @param string        $sellerRefundNote       - 255 char note shown on the refund mail sent by amazon
     *
     * @return \OffAmazonPaymentsService_Model_RefundDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     * @throws AmazonRefundDeclinedException
     */
    public function refund(
        \TdbShopOrder $order,
        $amazonCaptureId,
        $localRefundReferenceId,
        $amount,
        $invoiceNumber = null,
        $sellerRefundNote = null
    );

    /**
     * @param array $constraintsThatShouldBeIgnored - list of constraints (self::CONSTRAINT_*,
     *
     * @return \OffAmazonPaymentsService_Model_OrderReferenceDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     * @throws \InvalidArgumentException
     */
    public function getOrderReferenceDetails(array $constraintsThatShouldBeIgnored = null);

    /**
     * @param $amazonAuthorizationId
     *
     * @return \OffAmazonPaymentsService_Model_AuthorizationDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function getAuthorizationDetails($amazonAuthorizationId);

    /**
     * @param string $amazonCaptureId
     *
     * @return \OffAmazonPaymentsService_Model_CaptureDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function getCaptureDetails($amazonCaptureId);

    /**
     * @param string $amazonRefundId
     *
     * @return \OffAmazonPaymentsService_Model_RefundDetails
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function getRefundDetails($amazonRefundId);

    /**
     * @param string $cancellationReason - Describes the reason for the cancellation. (max 1024 chars - everything above 1024 chars will be truncated)
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function cancelOrderReference($cancellationReason = null);

    /**
     * @param string $closureReason Describes the reason for closing the order reference. (max 1024 chars - everything above 1024 chars will be truncated)
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function closeOrderReference($closureReason = null);

    /**
     * @param string $amazonAuthorizationId
     * @param string $closureReason         Describes the reason for closing the authorization  (max 1024 chars - everything above 1024 chars will be truncated)
     */
    public function closeAuthorization($amazonAuthorizationId, $closureReason = null);

    /**
     * returns the amazon capture id form the captureIdList passed which has a remaining refundable value that most closely matches
     * the requested refund value. If there there is no capture that covers the refund value completely, then a list of matching IDs
     * will be returned so as that the refundable sum of the items most closely matches the requested refund.
     *
     * @param AmazonReferenceIdList $captureIdList
     * @param $refundValue
     *
     * @return array [amazonCaptureId => refundableValue]
     */
    public function findBestCaptureMatchForRefund(AmazonReferenceIdList $captureIdList, $refundValue);
}
