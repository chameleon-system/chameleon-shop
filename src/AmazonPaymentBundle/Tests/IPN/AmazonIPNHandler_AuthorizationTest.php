<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\IPN;

use ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractIPNHandler;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractIPNHandler.php';

class AmazonIPNHandler_AuthorizationTest extends AbstractIPNHandler
{
    /**
     * expect the shop owner to be notified so he can take corrective action once the problem has been fixed (or when it is not fixed).
     */
    public function test_Declined_with_InvalidPaymentMethod()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Declined-InvalidPaymentMethod.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'InvalidPaymentMethod', 'There were problems with the payment method. You should contact your buyer and have them update their payment method using the Amazon Payments web site.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * we could retry if the object is still open - but won't.
     * we expect the shop owner to be notified so he can take corrective actions.
     */
    public function test_Declined_with_AmazonRejected()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Declined-AmazonRejected.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'AmazonRejected', 'Amazon has rejected the authorization. You should only retry the authorization if the order reference is in the Open state.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * the request should be called again - we currently don't have the necessary data, so inform the shop owner instead.
     */
    public function test_Declined_with_ProcessingFailure()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Declined-ProcessingFailure.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'ProcessingFailure', 'Amazon could not process the transaction due to an internal processing error. You should only retry the authorization if the order reference is in the Open state.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * inform shop owner.
     */
    public function test_Declined_with_TransactionTimedOut()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Declined-TransactionTimedOut.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'TransactionTimedOut', 'foobar');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * inform seller.
     */
    public function test_Closed_with_ExpiredUnused()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Closed-ExpiredUnused.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Closed', 'ExpiredUnused', 'The authorization has been in the Open state for 30 days (two days for Sandbox) and you did not submit any captures against it.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * inform seller.
     */
    public function test_Closed_with_AmazonClosed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Closed-AmazonClosed.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Closed', 'AmazonClosed', 'Amazon has closed the authorization object due to problems with your account.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * do nothing.
     */
    public function test_Closed_with_OrderReferenceCanceled()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Closed-OrderReferenceCanceled.xml'), $this->idReferenceManager);

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * do nothing.
     */
    public function test_Closed_with_SellerClosed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Closed-SellerClosed.xml'), $this->idReferenceManager);

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * since we have the billing data for the user only after an auth has moved to the open state, we need to add this data to the order.
     * note: the address is specific for every authorization - something that is not supported by our shop. if the shop is using
     * payment on shipping, then we will generate the authorization after the invoice is created - so we may not have any use for the data at all
     * however, that will be wawi specific.
     *
     * we expect the following
     *  * update the orders billing address with the data
     *  *
     */
    public function test_open()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Open.xml'), $this->idReferenceManager);

        /** @var $orderRefObject AmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $orderRefObject = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->disableOriginalConstructor()->getMock();
        $getAuthorizationDetails = AmazonPaymentFixturesFactory::getAuthorizationDetails('success.xml');
        $orderRefObject->expects($this->once())->method('getAuthorizationDetails')->will(
            $this->returnValue(
                $getAuthorizationDetails->getGetAuthorizationDetailsResult()->getAuthorizationDetails()
            )
        );

        $this->getConfig()->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($orderRefObject));

        $this->ipnHandler->setAmazonConfig($this->getConfig());

        $this->ipnHandler->expects($this->once())->method('updateShopOrderBillingAddress')->with(
            $this->equalTo($this->ipnRequest->getOrder()),
            $this->equalTo(
                array(
                    'adr_billing_company' => 'ESONO AG',
                    'adr_billing_salutation_id' => '',
                    'adr_billing_firstname' => '',
                    'adr_billing_lastname' => 'Mr. Dev',
                    'adr_billing_street' => 'Grünwälderstr. 10-14',
                    'adr_billing_streetnr' => '',
                    'adr_billing_city' => 'Freiburg',
                    'adr_billing_postalcode' => '79098',
                    'adr_billing_country_id' => '1',
                    'adr_billing_telefon' => '',
                    'adr_billing_fax' => '',
                    'adr_billing_additional_info' => '',
                )
            ));
        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * expect billing address to be updated.
     */
    public function test_Closed_with_MaxCapturesProcessed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Closed-MaxCapturesProcessed.xml'), $this->idReferenceManager);

        /** @var $orderRefObject AmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $orderRefObject = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->disableOriginalConstructor()->getMock();
        $getAuthorizationDetails = AmazonPaymentFixturesFactory::getAuthorizationDetails('success.xml');
        $orderRefObject->expects($this->once())->method('getAuthorizationDetails')->will(
            $this->returnValue(
                $getAuthorizationDetails->getGetAuthorizationDetailsResult()->getAuthorizationDetails()
            )
        );

        $this->getConfig()->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($orderRefObject));
        $this->ipnHandler->setAmazonConfig($this->getConfig());

        $this->ipnHandler->expects($this->once())->method('updateShopOrderBillingAddress')->with(
            $this->equalTo($this->ipnRequest->getOrder()),
            $this->equalTo(
                array(
                    'adr_billing_company' => 'ESONO AG',
                    'adr_billing_salutation_id' => '',
                    'adr_billing_firstname' => '',
                    'adr_billing_lastname' => 'Mr. Dev',
                    'adr_billing_street' => 'Grünwälderstr. 10-14',
                    'adr_billing_streetnr' => '',
                    'adr_billing_city' => 'Freiburg',
                    'adr_billing_postalcode' => '79098',
                    'adr_billing_country_id' => '1',
                    'adr_billing_telefon' => '',
                    'adr_billing_fax' => '',
                    'adr_billing_additional_info' => '',
                )
            ));
        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * expect billing address to be updated
     * we also receive an IPN for the capture (even when called via capture now) so we do not need to change the transaction state here.
     */
    public function test_Closed_with_MaxCapturesProcessed_on_AuthWithCapture()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Closed-MaxCapturesProcessed-CaptureNow.xml'), $this->idReferenceManager);

        /** @var $orderRefObject AmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $orderRefObject = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->disableOriginalConstructor()->getMock();
        $getAuthorizationDetails = AmazonPaymentFixturesFactory::getAuthorizationDetails('success.xml');
        $orderRefObject->expects($this->once())->method('getAuthorizationDetails')->will(
            $this->returnValue(
                $getAuthorizationDetails->getGetAuthorizationDetailsResult()->getAuthorizationDetails()
            )
        );

        $this->getConfig()->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($orderRefObject));
        $this->ipnHandler->setAmazonConfig($this->getConfig());

        $this->ipnHandler->expects($this->once())->method('updateShopOrderBillingAddress')->with(
            $this->equalTo($this->ipnRequest->getOrder()),
            $this->equalTo(
                array(
                    'adr_billing_company' => 'ESONO AG',
                    'adr_billing_salutation_id' => '',
                    'adr_billing_firstname' => '',
                    'adr_billing_lastname' => 'Mr. Dev',
                    'adr_billing_street' => 'Grünwälderstr. 10-14',
                    'adr_billing_streetnr' => '',
                    'adr_billing_city' => 'Freiburg',
                    'adr_billing_postalcode' => '79098',
                    'adr_billing_country_id' => '1',
                    'adr_billing_telefon' => '',
                    'adr_billing_fax' => '',
                    'adr_billing_additional_info' => '',
                )
            ));
        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->ipnHandler->expects($this->never())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }
}
