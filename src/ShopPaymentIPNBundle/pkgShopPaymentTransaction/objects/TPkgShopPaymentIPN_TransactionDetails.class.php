<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * the class is used to transfer data from an IPN to a pkgShopPaymentTransaction
 * Class TPkgShopPaymentIPN_TransactionDetails.
 */
class TPkgShopPaymentIPN_TransactionDetails
{
    private $amount = null;
    private $transactionType = null;
    private $context = null;
    private $sequenceNumber = null;
    private $transactionTimestamp = null;
    private $resultingBalance = null;
    private $additionalData = array();

    /**
     * @param $amount
     * @param $transactionType - must be a valid type (one of TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_*)
     * @param string $context - a string explaining what caused the transaction
     * @param $sequenceNumber
     * @param $iTransactionTimestamp
     * @param null $dBalance - if the IPN sends you a balance (amount remaining after transaction) then you can pass it here
     */
    public function __construct($amount, $transactionType, $context, $sequenceNumber, $iTransactionTimestamp, $dBalance = null)
    {
        $this->amount = $amount;
        $this->transactionType = $transactionType;
        $this->context = $context;
        $this->sequenceNumber = $sequenceNumber;
        $this->transactionTimestamp = $iTransactionTimestamp;
        $this->resultingBalance = $dBalance;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    public function getTransactionTimestamp()
    {
        return $this->transactionTimestamp;
    }

    public function getResultingBalance()
    {
        return $this->resultingBalance;
    }

    /**
     * if the IPN also transfers a balance, then set it here.
     *
     * @param float $resultingBalance
     *
     * @return $this
     */
    public function setResultingBalance($resultingBalance)
    {
        $this->resultingBalance = $resultingBalance;

        return $this;
    }

    /**
     * here you can set additional data like the transaction id to the transaction details.
     *
     * @param string $key
     * @param $value
     */
    public function setAdditionalData($key, $value)
    {
        $this->additionalData[$key] = $value;
    }

    /**
     * here you can get additional data like the transaction id from the transaction details,
     * If key not exists return null.
     *
     * @param $key
     *
     * @return null|mixed
     */
    public function getAdditionalData($key)
    {
        if (true === isset($this->additionalData[$key])) {
            return $this->additionalData[$key];
        }

        return null;
    }
}
