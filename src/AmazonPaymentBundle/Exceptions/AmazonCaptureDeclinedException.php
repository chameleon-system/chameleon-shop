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

class AmazonCaptureDeclinedException extends \TPkgCmsException_LogAndMessage
{
    const REASON_CODE_AMAZON_REJECTED = 'AmazonRejected';
    const REASON_CODE_PROCESSING_FAILURE = 'ProcessingFailure';

    private $reasonCode = null;

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
            AmazonPayment::ERROR_CAPTURE_DECLINED,
            $aAdditionalData,
            $message,
            $aContextData,
            $iLogLevel,
            $sLogFilePath
        );
        $this->reasonCode = $reasonCode;
    }

    public function __toString()
    {
        $sString = parent::__toString();
        $sString .= "\n".'reasonCode: '.$this->getReasonCode();

        return $sString;
    }

    /**
     * @return string|null
     */
    public function getReasonCode()
    {
        return $this->reasonCode;
    }
}
