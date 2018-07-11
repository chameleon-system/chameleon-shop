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

use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonIPNTransactionException;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractIPNHandler;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractIPNHandler.php';

class AmazonIPNHandler_RefundTest extends AbstractIPNHandler
{
    /**
     * inform the shop owner.
     */
    public function test_Declined_with_AmazonRejected()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNRefundNotification('Declined-AmazonRejected.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'AmazonRejected', 'Amazon has rejected the refund. You should issue a refund to the buyer in an alternate manner (for example, a gift card or store credit).');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * inform the shop owner.
     */
    public function test_Declined_with_ProcessingFailure()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNRefundNotification('Declined-ProcessingFailure.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'ProcessingFailure', 'Amazon could not process the transaction due to an internal processing error or because the buyer has already received a refund from an A-to-z claim or a charge back. You should only retry the refund if the Capture object is in the Completed state. Otherwise, you should refund the buyer in an alternative way (for example, a store credit or a check).');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * we expect the transaction to be marked as completed.
     */
    public function test_Completed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNRefundNotification('Completed.xml'), $this->idReferenceManager);

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $transactionMock = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $this->ipnHandler->expects($this->once())->method('getTransactionFromRequest')->will($this->returnValue($transactionMock));
        $this->ipnHandler->expects($this->once())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * we expect the transaction to be marked as completed.
     */
    public function test_Completed_TransactionAlreadyMarkedAsConfirmed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNRefundNotification('Completed.xml'), $this->idReferenceManager);

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        /** @var $transactionMock \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $transactionMock = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $transactionMock->fieldConfirmed = true;
        $transactionMock->sqlData['confirmed'] = '1';
        $this->ipnHandler->expects($this->once())->method('getTransactionFromRequest')->will($this->returnValue($transactionMock));
        $this->ipnHandler->expects($this->never())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * the IPN returned an orderIdManager that refers to a transaction Id that can not be found - so throw an error.
     */
    public function test_Completed_TransactionNotFoundError()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNRefundNotification('Completed.xml'), $this->idReferenceManager);

        $exception = new AmazonIPNTransactionException(AmazonIPNTransactionException::ERROR_TRANSACTION_NOT_FOUND);
        $this->ipnHandler->expects($this->once())->method('getTransactionFromRequest')->will($this->throwException($exception));
        $this->helperAddIPNMailObject('Completed', $exception->getErrorCodeAsString(), 'unable to find transaction matching IPN - details have been logged. '.(string) $exception);
        $this->ipnHandler->expects($this->never())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * the IPN returned an orderIdManager that refers to a transaction Id does not match the order.
     */
    public function test_Completed_TransactionInvalid()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNRefundNotification('Completed.xml'), $this->idReferenceManager);

        $exception = new AmazonIPNTransactionException(AmazonIPNTransactionException::ERROR_TRANSACTION_DOES_NOT_MATCH_ORDER);
        $this->ipnHandler->expects($this->once())->method('getTransactionFromRequest')->will($this->throwException($exception));
        $this->helperAddIPNMailObject('Completed', $exception->getErrorCodeAsString(), 'unable to find transaction matching IPN - details have been logged. '.(string) $exception);
        $this->ipnHandler->expects($this->never())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * the IPN does not match any transaction (or did not pass one).
     */
    public function test_Completed_NoTransaction()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNRefundNotification('Completed.xml'), $this->idReferenceManager);

        $exception = new AmazonIPNTransactionException(AmazonIPNTransactionException::ERROR_NO_TRANSACTION);
        $this->ipnHandler->expects($this->once())->method('getTransactionFromRequest')->will($this->throwException($exception));
        $this->helperAddIPNMailObject('Completed', $exception->getErrorCodeAsString(), 'unable to find transaction matching IPN - details have been logged. '.(string) $exception);
        $this->ipnHandler->expects($this->never())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }
}
