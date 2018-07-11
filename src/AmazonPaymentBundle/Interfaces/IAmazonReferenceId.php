<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\Interfaces;

interface IAmazonReferenceId
{
    const TYPE_AUTHORIZE = 1;
    const TYPE_CAPTURE = 2;
    const TYPE_REFUND = 3;
    const REQUEST_MODE_ASYNCHRONOUS = 1;
    const REQUEST_MODE_SYNCHRONOUS = 2;

    public function setLocalId($localId);

    /**
     * @return string
     */
    public function getLocalId();

    /**
     * @return string
     */
    public function getAmazonId();

    /**
     * @return float
     */
    public function getValue();

    /**
     * return the transaction id associated with the counter.
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * @param int    $type          - must be one of self::TYPE_AUTHORIZE
     * @param string $localId
     * @param float  $value
     * @param string $transactionId
     */
    public function __construct($type, $localId, $value, $transactionId);

    /**
     * @param string $amazonId
     */
    public function setAmazonId($amazonId);

    public function getType();

    /**
     * @param bool $captureNow
     */
    public function setCaptureNow($captureNow);

    /**
     * @return bool
     */
    public function getCaptureNow();

    /**
     * returns self::REQUEST_MODE_SYNCHRONOUS or self::REQUEST_MODE_ASYNCHRONOUS.
     *
     * @return int
     */
    public function getRequestMode();

    /**
     * @param int $requestMode - one of self::REQUEST_MODE_ASYNCHRONOUS
     *
     * @throws \InvalidArgumentException
     */
    public function setRequestMode($requestMode);
}
