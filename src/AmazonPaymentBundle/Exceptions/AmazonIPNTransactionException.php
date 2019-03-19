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

use Monolog\Logger;

class AmazonIPNTransactionException extends \TPkgCmsException_Log
{
    const ERROR_TRANSACTION_NOT_FOUND = 1;
    const ERROR_TRANSACTION_DOES_NOT_MATCH_ORDER = 2;
    const ERROR_NO_TRANSACTION = 3;

    private $errorCode = null;

    public function __construct(
        $errorCode,
        $message = '',
        $aContextData = array(), // any data you want showing up in the log message to help you debug the exception
        $iLogLevel = Logger::ERROR,
        $sLogFilePath = self::LOG_FILE
    ) {
        parent::__construct($message, $aContextData, $iLogLevel, $sLogFilePath);
        $this->errorCode = $errorCode;
    }

    public function __toString()
    {
        $sString = parent::__toString();

        $sString .= "\nError: ".$this->getErrorCodeAsString();

        return $sString;
    }

    public function getErrorCodeAsString()
    {
        switch ($this->errorCode) {
            case self::ERROR_NO_TRANSACTION:
                return 'no transaction connected to local ref id passed via IPN';
                break;
            case self::ERROR_TRANSACTION_DOES_NOT_MATCH_ORDER:
                return 'transaction ID found via local ref passed via IPN does not match order connected to IPN';
                break;
            case self::ERROR_TRANSACTION_NOT_FOUND:
                return 'transaction id was passed but was not found in the database';
                break;
            default:
                return 'unknown error code';
                break;
        }
    }

    /**
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
