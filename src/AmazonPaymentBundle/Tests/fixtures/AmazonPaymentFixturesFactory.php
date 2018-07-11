<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\fixtures;

use Symfony\Component\HttpFoundation\Request;

class AmazonPaymentFixturesFactory
{
    /**
     * @param $file
     * @param $value
     *
     * @return \OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse
     */
    public static function setOrderReferenceDetailsResponse($file, $value = null)
    {
        $fixture = \OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse::fromXML(self::getFileContent('setOrderReferenceDetails', $file));

        if (null !== $value) {
            $fixture->getSetOrderReferenceDetailsResult()
                ->getOrderReferenceDetails()
                ->getOrderTotal()
                ->setAmount($value);
        }

        return $fixture;
    }

    /**
     * @param $name
     *
     * @return Request
     */
    public static function getIPNRequest($name)
    {
        $fileContent = self::getFileContent('IPN', $name);

        $header = mb_substr($fileContent, 0, mb_strpos($fileContent, "\n\n"));
        $body = mb_substr($fileContent, mb_strpos($fileContent, "\n\n") + 2);

        $headerList = explode("\n", $header);
        $requestType = substr($headerList[0], 0, strpos($headerList[0], ' '));
        unset($headerList[0]);
        $header = array();
        foreach ($headerList as $headerLine) {
            $name = mb_substr($headerLine, 0, mb_strpos($headerLine, ': '));
            $value = mb_substr($headerLine, mb_strpos($headerLine, ': ') + 2);
            $header[$name] = $value;
        }

        return Request::create('/_api_pkgshopipn_amazon', $requestType, array(), array(), array(), $header, $body);
    }

    /**
     * @param $file
     *
     * @return null|\OffAmazonPaymentsNotifications_Model_OrderReferenceNotification
     */
    public static function getIPNOrderReferenceNotification($file)
    {
        // \OffAmazonPaymentsNotifications_Model_OrderReferenceNotification::fromXML is broken, so we need to implement the code here
        $dom = new \DOMDocument();
        $dom->loadXML(self::getFileContent('IPN/orderReference', $file));
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('a', 'https://mws.amazonservices.com/ipn/OffAmazonPayments/2013-01-01');
        $response = $xpath->query('//a:OrderReferenceNotification');
        if (1 == $response->length) {
            $msg = <<<INPUT
{
            "Timestamp" : "1395067338",
            "Message" : "foobar",
            "MessageId" : "12124323443",
            "TopicArn" : "arnschmarn",
            "Type" : "OrderReferenceNotification"
}
INPUT;
            $meta = new \OffAmazonPaymentsNotifications_Model_SnsNotificationMetadata(new \Message($msg));

            return new \OffAmazonPaymentsNotifications_Model_OrderReferenceNotification($meta, $response->item(0));
        }

        return null;
    }

    /**
     * @param $file
     *
     * @return null|\OffAmazonPaymentsNotifications_Model_AuthorizationNotification
     */
    public static function getIPNAuthorizationNotification($file)
    {
        // \OffAmazonPaymentsNotifications_Model_OrderReferenceNotification::fromXML is broken, so we need to implement the code here
        $dom = new \DOMDocument();
        $dom->loadXML(self::getFileContent('IPN/authorization', $file));
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('a', 'https://mws.amazonservices.com/ipn/OffAmazonPayments/2013-01-01');
        $response = $xpath->query('//a:AuthorizationNotification');
        if (1 == $response->length) {
            $msg = <<<INPUT
{
            "Timestamp" : "1395067338",
            "Message" : "foobar",
            "MessageId" : "12124323443",
            "TopicArn" : "arnschmarn",
            "Type" : "AuthorizationNotification"
}
INPUT;
            $meta = new \OffAmazonPaymentsNotifications_Model_SnsNotificationMetadata(new \Message($msg));

            return new \OffAmazonPaymentsNotifications_Model_AuthorizationNotification($meta, $response->item(0));
        }

        return null;
    }

    /**
     * @param $file
     *
     * @return null|\OffAmazonPaymentsNotifications_Model_CaptureNotification
     */
    public static function getIPNCaptureNotification($file)
    {
        // \OffAmazonPaymentsNotifications_Model_OrderReferenceNotification::fromXML is broken, so we need to implement the code here
        $dom = new \DOMDocument();
        $dom->loadXML(self::getFileContent('IPN/capture', $file));
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('a', 'https://mws.amazonservices.com/ipn/OffAmazonPayments/2013-01-01');
        $response = $xpath->query('//a:CaptureNotification');
        if (1 == $response->length) {
            $msg = <<<INPUT
{
            "Timestamp" : "1395067338",
            "Message" : "foobar",
            "MessageId" : "12124323443",
            "TopicArn" : "arnschmarn",
            "Type" : "CaptureNotification"
}
INPUT;
            $meta = new \OffAmazonPaymentsNotifications_Model_SnsNotificationMetadata(new \Message($msg));

            return new \OffAmazonPaymentsNotifications_Model_CaptureNotification($meta, $response->item(0));
        }

        return null;
    }

    /**
     * @param $file
     *
     * @return null|\OffAmazonPaymentsNotifications_Model_RefundNotification
     */
    public static function getIPNRefundNotification($file)
    {
        // \OffAmazonPaymentsNotifications_Model_RefundNotification::fromXML is broken, so we need to implement the code here
        $dom = new \DOMDocument();
        $dom->loadXML(self::getFileContent('IPN/refund', $file));
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('a', 'https://mws.amazonservices.com/ipn/OffAmazonPayments/2013-01-01');
        $response = $xpath->query('//a:RefundNotification');
        if (1 == $response->length) {
            $msg = <<<INPUT
{
            "Timestamp" : "1395067338",
            "Message" : "foobar",
            "MessageId" : "12124323443",
            "TopicArn" : "arnschmarn",
            "Type" : "RefundNotification"
}
INPUT;
            $meta = new \OffAmazonPaymentsNotifications_Model_SnsNotificationMetadata(new \Message($msg));

            return new \OffAmazonPaymentsNotifications_Model_RefundNotification($meta, $response->item(0));
        }

        return null;
    }

    /**
     * @param $file
     * @param null $value
     *
     * @return \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse
     */
    public static function getOrderReferenceDetailsResponse($file, $value = null)
    {
        $fixture = \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse::fromXML(self::getFileContent('getOrderReferenceDetails', $file));

        if (null !== $value) {
            $fixture->getGetOrderReferenceDetailsResult()
                ->getOrderReferenceDetails()
                ->getOrderTotal()
                ->setAmount($value);
        }

        return $fixture;
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_AuthorizeResponse
     */
    public static function authorize($file)
    {
        return \OffAmazonPaymentsService_Model_AuthorizeResponse::fromXML(self::getFileContent('authorize', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_CancelOrderReferenceResponse
     */
    public static function cancelOrderReference($file)
    {
        return \OffAmazonPaymentsService_Model_CancelOrderReferenceResponse::fromXML(self::getFileContent('cancelOrderReference', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_CaptureResponse
     */
    public static function capture($file)
    {
        return \OffAmazonPaymentsService_Model_CaptureResponse::fromXML(self::getFileContent('capture', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_CloseAuthorizationResponse
     */
    public static function closeAuthorization($file)
    {
        return \OffAmazonPaymentsService_Model_CloseAuthorizationResponse::fromXML(self::getFileContent('closeAuthorization', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_CloseOrderReferenceResponse
     */
    public static function closeOrderReference($file)
    {
        return \OffAmazonPaymentsService_Model_CloseOrderReferenceResponse::fromXML(self::getFileContent('closeOrderReference', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse
     */
    public static function confirmOrderReference($file)
    {
        return \OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse::fromXML(self::getFileContent('confirmOrderReference', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse
     */
    public static function getAuthorizationDetails($file)
    {
        return \OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse::fromXML(self::getFileContent('getAuthorizationDetails', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_GetCaptureDetailsResponse
     */
    public static function getCaptureDetails($file)
    {
        return \OffAmazonPaymentsService_Model_GetCaptureDetailsResponse::fromXML(self::getFileContent('getCaptureDetails', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_GetRefundDetailsResponse
     */
    public static function getRefundDetails($file)
    {
        return \OffAmazonPaymentsService_Model_GetRefundDetailsResponse::fromXML(self::getFileContent('getRefundDetails', $file));
    }

    /**
     * @param $file
     *
     * @return \OffAmazonPaymentsService_Model_RefundResponse
     */
    public static function refund($file)
    {
        return \OffAmazonPaymentsService_Model_RefundResponse::fromXML(self::getFileContent('refund', $file));
    }

    private static function getFileContent($type, $file)
    {
        return file_get_contents(self::getFixturePath($type).'/'.$file);
    }

    /**
     * @param $type
     * @param $file
     *
     * @return string
     */
    public static function getFixturePath($type)
    {
        return __DIR__.'/'.$type;
    }
}
