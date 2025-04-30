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
    /** @var float */
    private $amount;

    /**
     * @var string
     *
     * @psalm-var TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_*
     */
    private $transactionType;

    /** @var string */
    private $context;

    /** @var string */
    private $sequenceNumber;

    /** @var int */
    private $transactionTimestamp;

    /** @var float|null */
    private $resultingBalance;

    /** @var array<string, mixed> */
    private $additionalData = [];

    /**
     * @param float $amount
     * @param string $transactionType - must be a valid type (one of TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_*)
     * @param string $context - a string explaining what caused the transaction
     * @param string $sequenceNumber
     * @param int $iTransactionTimestamp
     * @param float $dBalance - if the IPN sends you a balance (amount remaining after transaction) then you can pass it here
     *
     * @psalm-param TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_* $transactionType
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
     *
     * @psalm-return TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_*
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * @return string
     */
    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * @return int
     */
    public function getTransactionTimestamp()
    {
        return $this->transactionTimestamp;
    }

    /**
     * @return float|null
     */
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
     *
     * @return void
     */
    public function setAdditionalData($key, $value)
    {
        $this->additionalData[$key] = $value;
    }

    /**
     * here you can get additional data like the transaction id from the transaction details,
     * If key not exists return null.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function getAdditionalData($key)
    {
        if (true === isset($this->additionalData[$key])) {
            return $this->additionalData[$key];
        }

        return null;
    }
}
