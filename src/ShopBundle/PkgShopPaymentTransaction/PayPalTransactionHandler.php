<?php

namespace ChameleonSystem\ShopBundle\PkgShopPaymentTransaction;

use esono\pkgshoppaymenttransaction\PaymentTransactionHandlerInterface;

class PayPalTransactionHandler implements PaymentTransactionHandlerInterface
{
    private \IPkgShopOrderPaymentConfig $config;

    public function __construct(\IPkgShopOrderPaymentConfig $config)
    {
        $this->config = $config;
    }

    public function captureOrder(\TPkgShopPaymentTransactionManager $transactionManager, \TdbShopOrder &$order)
    {
        // we don't have access to the PAYERID here, so we capture the payment in the order.
        throw new \Exception('method not implemented (currently still in the payment handler');
    }

    public function captureShipment(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder &$order,
        $value,
        $invoiceNumber = null,
        array $orderItemList = null
    ) {
        throw new \Exception('paypal payment on shipment is currently not implemented');
    }

    public function refund(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder &$order,
        $value,
        $invoiceNumber = null,
        $sellerRefundNote = null,
        array $orderItemList = null
    ) {
        $paymentHandler = $order->GetPaymentHandler();
        if (null === $paymentHandler) {
            throw new \TPkgCmsException(
                sprintf('Unable to execute paypal refund for order %s - order has no payment handler', $order->id)
            );
        }

        $currency = $this->getCurrencyFromOrder($order);
        $transactionId = $paymentHandler->GetUserPaymentDataItem('PAYMENTINFO_0_TRANSACTIONID');
        if (null === $transactionId) {
            throw new \TPkgCmsException(
                sprintf('Unable to execute paypal refund for order %s - order has no transaction id', $order->id)
            );
        }
        $isSandbox = 'sandbox' === $this->config->getEnvironment();
        $refundType = 'Partial';
        if ((int)round($value * 100, 0) === (int)round($order->fieldValueTotal * 100, 2)) {
            $refundType = 'Full';
        }
        $payload = [
            'USER' => $this->config->getValue($isSandbox ? 'apiUserNameSandbox' : 'apiUserName'),
            'PWD' => $this->config->getValue($isSandbox ? 'apiPasswordSandbox' : 'apiPassword'),
            'SIGNATURE' => $this->config->getValue($isSandbox ? 'apiSignaturSandbox' : 'apiSignatur'),
            'METHOD' => 'RefundTransaction',
            'VERSION' => \TShopPaymentHandlerPayPal::PAYPAL_API_VERSION,
            'TRANSACTIONID' => $transactionId,
            'REFUNDTYPE' => $refundType,
        ];
        if ('Partial' === $refundType) {
            $payload['AMT'] = $value;
            $payload['CURRENCYCODE'] = $currency;
        }
        $apiEndpoint = $this->config->getValue($isSandbox ? 'urlApiEndpointSandbox' : 'urlApiEndpoint');
        $queryString = http_build_query($payload);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        $response = curl_exec($ch);

        $curlErrno = curl_errno($ch);
        $curlErrorText = curl_error($ch);
        curl_close($ch);

        if ($curlErrno) {
            throw new \TPkgCmsException(
                sprintf(
                    'Unable to execute paypal refund for order %s. Error %d: %s',
                    $order->id,
                    $curlErrno,
                    $curlErrorText
                )
            );
        }

        $responseData = [];
        parse_str($response, $responseData);

        if ($responseData['ACK'] !== 'Success') {
            throw new \TPkgCmsException(
                sprintf(
                    'Refund request rejected %s. Error %d: %s',
                    $order->id,
                    $responseData['L_ERRORCODE0'],
                    $responseData['L_LONGMESSAGE0']
                )
            );
        }

        $transactionData = $transactionManager->getTransactionDataFromOrder(
            \TPkgShopPaymentTransactionData::TYPE_CREDIT,
            $orderItemList
        );
        $transactionData->setTotalValue($responseData['TOTALREFUNDEDAMOUNT'] ?? $value);
        $transactionData->setConfirmed(true);
        $transactionData->setConfirmedTimestamp(time());
        $transactionData->setContext(
            new \TPkgShopPaymentTransactionContext(
                sprintf(
                    ($sellerRefundNote === null ? '[%s] refund' : '[%s] refund with note '.$sellerRefundNote),
                    $responseData['REFUNDTRANSACTIONID'] ?? '?'
                )
            )
        );
        $transaction = $transactionManager->addTransaction($transactionData);


        return array($transaction);

    }

    public function cancelOrder(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder &$order,
        $cancellationReason = null
    ) {
        // currently we do not pre-authorize, so there is nothing to do. if we ever do, then we need to use to void the authorization using 'METHOD': 'DoVoid','AUTHORIZATIONID': auth_id,
    }

    /**
     * @param \TdbShopOrder $order
     * @return string|null
     */
    public function getCurrencyFromOrder(\TdbShopOrder $order): ?string
    {
        $currency = 'EUR';
        if (true === method_exists($order, 'getCurrency')) {
            $cur = $order->GetFieldPkgShopCurrency();
            if (null !== $cur) {
                $currency = $cur->fieldIso4217;
            }
        }

        return $currency;
    }


}