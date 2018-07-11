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

require_once __DIR__.'/../abstracts/AbstractIPNHandler.php';

use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractIPNHandler;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

class AmazonIPNHandler_OrderReferenceNotificationTest extends AbstractIPNHandler
{
    /**
     * no action needs to be taken on open. So make sure, that no mail is sent.
     */
    public function test_open()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Open.xml'),
            $this->idReferenceManager
        );

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    /**
     * no notification.
     */
    public function test_Canceled_SellerCanceled()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Canceled-SellerCanceled.xml'),
            $this->idReferenceManager
        );

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    /**
     * no notification.
     */
    public function test_Closed_SellerClosed()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Closed-SellerClosed.xml'),
            $this->idReferenceManager
        );

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    /**
     * the order was marked as suspended via a decline from an auth IPN. when we receive an IPN, that it has reopened,
     * we need to inform the shop owner, that he must take action.
     */
    public function test_Suspended_InvalidPaymentMethod()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Suspended-InvalidPaymentMethod.xml'),
            $this->idReferenceManager
        );

        $this->helperAddIPNMailObject('Suspended', 'InvalidPaymentMethod', 'There were problems with the payment method.');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    public function test_Canceled_Stale()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Canceled-Stale.xml'),
            $this->idReferenceManager
        );

        $this->helperAddIPNMailObject('Canceled', 'Stale', 'You did not confirm the order reference by calling the ConfirmOrderReference operation within the allowed period of 3 hours.');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    public function test_Canceled_AmazonCanceled()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Canceled-AmazonCanceled.xml'),
            $this->idReferenceManager
        );

        $this->helperAddIPNMailObject('Canceled', 'AmazonCanceled', 'Amazon has canceled the order reference.');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    public function test_Closed_Expired()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Closed-Expired.xml'),
            $this->idReferenceManager
        );

        $this->helperAddIPNMailObject('Closed', 'Expired', 'You can only authorize funds on the buyer’s payment instrument up to 180 days after the order reference is created. After this, Amazon will mark the order reference as closed and new authorizations will not be allowed.');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    public function test_Closed_MaxAmountCharged()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Closed-MaxAmountCharged.xml'),
            $this->idReferenceManager
        );

        $this->helperAddIPNMailObject('Closed', 'MaxAmountCharged', 'You are allowed to capture the following amounts before the order reference will be closed by Amazon');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    public function test_Closed_MaxAuthorizationsCaptured()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Closed-MaxAuthorizationsCaptured.xml'),
            $this->idReferenceManager
        );

        $this->helperAddIPNMailObject('Closed', 'MaxAuthorizationsCaptured', 'You have fully or partially captured 10 authorizations. After this, the order reference will be closed by Amazon.');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }

    public function test_Closed_AmazonClosed()
    {
        $this->helperAddIPNRequest(
            AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Closed-AmazonClosed.xml'),
            $this->idReferenceManager
        );

        $this->helperAddIPNMailObject('Closed', 'AmazonClosed', 'Amazon has closed the order reference due to a failed internal validation or due to an A-to-z claim being decided against you.');

        $this->assertTrue($this->ipnHandler->handleIPN($this->ipnRequest));
    }
}
