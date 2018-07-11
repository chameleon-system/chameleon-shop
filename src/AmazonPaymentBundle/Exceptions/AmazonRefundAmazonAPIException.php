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

class AmazonRefundAmazonAPIException extends \TPkgCmsException_LogAndMessage
{
    private $successfulTransactionList = array();

    /**
     * @param string $successfulTransactionList
     * @param array  $aAdditionalData
     * @param string $message
     * @param array  $aContextData
     * @param int    $iLogLevel
     * @param string $sLogFilePath
     */
    public function __construct(
        $successfulTransactionList,
        $aAdditionalData = array(),
        $message = '',
        $aContextData = array(), // any data you want showing up in the log message to help you debug the exception
        $iLogLevel = 1,
        $sLogFilePath = self::LOG_FILE
    ) {
        parent::__construct(
            AmazonPayment::ERROR_CODE_API_ERROR,
            $aAdditionalData,
            $message,
            $aContextData,
            $iLogLevel,
            $sLogFilePath
        );
        $this->successfulTransactionList = $successfulTransactionList;
    }

    public function __toString()
    {
        $sString = parent::__toString();
        $sString .= "\n".'with '.count(
                $this->getSuccessfulTransactionList()
            ).' successful transactions before the refund request that was declined';

        return $sString;
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
}
