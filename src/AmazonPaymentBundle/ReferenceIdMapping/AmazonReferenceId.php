<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping;

use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;

class AmazonReferenceId implements IAmazonReferenceId
{
    private $localId = null;
    private $amazonId = null;
    private $value = 0;

    /**
     * @var int
     */
    private $type;
    /**
     * @var string
     */
    private $transactionId;

    private $captureNow = false;

    private $requestMode = null;

    public function getLocalId()
    {
        return $this->localId;
    }

    public function getAmazonId()
    {
        return $this->amazonId;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __construct($type, $localId, $value, $transactionId)
    {
        $this->localId = $localId;
        $this->value = $value;
        $this->type = $type;
        $this->transactionId = $transactionId;
        $this->captureNow = false;
        $this->requestMode = IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS;
    }

    public function setAmazonId($amazonId)
    {
        $this->amazonId = $amazonId;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * return the transaction id associated with the counter.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param bool $captureNow
     */
    public function setCaptureNow($captureNow)
    {
        $this->captureNow = $captureNow;
    }

    /**
     * @return bool
     */
    public function getCaptureNow()
    {
        return $this->captureNow;
    }

    /**
     * returns self::REQUEST_MODE_SYNCHRONOUS or self::REQUEST_MODE_ASYNCHRONOUS.
     *
     * @return int
     */
    public function getRequestMode()
    {
        return $this->requestMode;
    }

    /**
     * @param int $requestMode - one of self::REQUEST_MODE_ASYNCHRONOUS
     *
     * @throws \InvalidArgumentException
     */
    public function setRequestMode($requestMode)
    {
        if (null !== $requestMode && (self::REQUEST_MODE_ASYNCHRONOUS !== $requestMode && self::REQUEST_MODE_SYNCHRONOUS !== $requestMode)) {
            throw new \InvalidArgumentException("invalid mode [{$requestMode}], expecting one of self::REQUEST_MODE_*");
        }
        $this->requestMode = $requestMode;
    }

    public function setLocalId($localId)
    {
        $this->localId = $localId;
    }
}
