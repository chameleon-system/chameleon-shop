<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\Exceptions;

use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;

class AmazonRefundDeclinedException extends \TPkgCmsException_LogAndMessage
{
    const REASON_CODE_AMAZON_REJECTED = 'AmazonRejected';
    const REASON_CODE_PROCESSING_FAILURE = 'ProcessingFailure';

    private $reasonCode = null;
    private $successfulTransactionList = array();

    /**
     * @param string $reasonCode      - one of self::REASON_CODE_*
     * @param array  $aAdditionalData
     * @param string $message
     * @param array  $aContextData
     * @param int    $iLogLevel
     * @param string $sLogFilePath
     */
    public function __construct(
        $reasonCode,
        $aAdditionalData = array(),
        $message = '',
        $aContextData = array(), // any data you want showing up in the log message to help you debug the exception
        $iLogLevel = 1,
        $sLogFilePath = self::LOG_FILE
    ) {
        parent::__construct(
            AmazonPayment::ERROR_REFUND_DECLINED,
            $aAdditionalData,
            $message,
            $aContextData,
            $iLogLevel,
            $sLogFilePath
        );
        $this->reasonCode = $reasonCode;
    }

    public function __toString(): string
    {
        $sString = parent::__toString();
        $sString .= "\n".'reasonCode: '.$this->getReasonCode();
        $sString .= "\n".'with '.count(
                $this->getSuccessfulTransactionList()
            ).' successful transactions before the refund request that was declined';

        return $sString;
    }

    /**
     * @return string|null
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * array of \TdbPkgShopPaymentTransaction that where successful before the one that was declined.
     *
     * @return array
     */
    public function getSuccessfulTransactionList()
    {
        return $this->successfulTransactionList;
    }

    /**
     * @param array $successfulTransactionList
     */
    public function setSuccessfulTransactionList($successfulTransactionList)
    {
        $this->successfulTransactionList = $successfulTransactionList;
    }
}
