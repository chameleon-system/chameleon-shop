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

class AmazonIPNHandler_CaptureTest extends AbstractIPNHandler
{
    /**
     * inform the shop owner.
     */
    public function test_Declined_with_AmazonRejected()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Declined-AmazonRejected.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'AmazonRejected', 'Amazon has rejected the capture. You should only retry the capture if the authorization is in the Open state.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * inform the shop owner.
     */
    public function test_Declined_with_ProcessingFailure()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Declined-ProcessingFailure.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Declined', 'ProcessingFailure', 'Amazon could not process the transaction due to an internal processing error. You should only retry the capture if the authorization is in the Open state. Otherwise, you should request a new authorization and then call Capture on it.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * inform the shop owner.
     */
    public function test_Closed_with_AmazonClosed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Closed-AmazonClosed.xml'), $this->idReferenceManager);
        $this->helperAddIPNMailObject('Closed', 'AmazonClosed', 'Amazon has closed the capture due to a problem with your account or with the buyer\'s account.');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * confirms a capture created individually- so we expect the transaction to be marked as completed.
     */
    public function test_Completed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Completed.xml'), $this->idReferenceManager);

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $transactionMock = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $this->ipnHandler->expects($this->once())->method('getTransactionFromRequest')->will($this->returnValue($transactionMock));
        $this->ipnHandler->expects($this->once())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * test a capture created via auth with CaptureNow in sync mode - so the transaction is already confirmed -nothing should happen.
     */
    public function test_Completed_TransactionAlreadyMarkedAsConfirmed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Completed.xml'), $this->idReferenceManager);

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
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Completed.xml'), $this->idReferenceManager);

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
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Completed.xml'), $this->idReferenceManager);

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
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Completed.xml'), $this->idReferenceManager);

        $exception = new AmazonIPNTransactionException(AmazonIPNTransactionException::ERROR_NO_TRANSACTION);
        $this->ipnHandler->expects($this->once())->method('getTransactionFromRequest')->will($this->throwException($exception));
        $this->helperAddIPNMailObject('Completed', $exception->getErrorCodeAsString(), 'unable to find transaction matching IPN - details have been logged. '.(string) $exception);
        $this->ipnHandler->expects($this->never())->method('confirmTransaction');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * no action needs to be taken.
     */
    public function test_Closed_with_MaxAmountRefunded()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Closed-MaxAmountRefunded.xml'), $this->idReferenceManager);

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }

    /**
     * no action needs to be taken.
     */
    public function test_Closed_with_MaxRefundsProcessed()
    {
        $this->helperAddIPNRequest(AmazonPaymentFixturesFactory::getIPNCaptureNotification('Closed-MaxRefundsProcessed.xml'), $this->idReferenceManager);

        $this->ipnHandler->expects($this->never())->method('getMailProfile');

        $this->ipnHandler->handleIPN($this->ipnRequest);
    }
}
