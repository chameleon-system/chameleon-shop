<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerEasyCash extends TdbShopPaymentHandler
{
    const EASY_CASH_API_SERVER = 'https://txms.gzs.de:51384';

    /**
     * return an array with all parameters required by easy cash for the payment execution.
     *
     * @param TdbShopOrder $oOrder
     * @param null         $dPaymentAmount
     *
     * @return array
     */
    protected function GetPaymentPayload($oOrder, $dPaymentAmount = null)
    {
        $oCurrency = null;
        if (method_exists($oOrder, 'GetFieldPkgShopCurrency')) {
            $oCurrency = $oOrder->GetFieldPkgShopCurrency();
        }
        if (null === $dPaymentAmount) {
            $dPaymentAmount = $oOrder->fieldValueTotal;
        }
        $sCurrency = $this->GetCurrencyIdentifier($oCurrency);
        $aPayload = array('MerchantID' => $this->GetConfigParameter('MerchantID'), 'TransID' => $this->GetTransactionIdForEasyCash($oOrder), 'Amount' => round($dPaymentAmount * 100), 'Currency' => $sCurrency, 'OrderDesc' => $this->GetOrderDescriptionTextForEasyCash($oOrder), 'Capture' => 'AUTO',
        );

        return $aPayload;
    }

    /**
     * returns an array of the form ('header'=>..,'response'=>...) on success, a string an error.
     *
     * @param string $sTargetPath
     * @param array  $aData
     *
     * @return array|string
     */
    protected function SendRequestToEasyCash($sTargetPath, $aData)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::EASY_CASH_API_SERVER.$sTargetPath);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Set user and pwd
        curl_setopt($ch, CURLOPT_USERPWD, $this->GetConfigParameter('sPaymasterUser').':'.$this->GetConfigParameter('sPaymasterPassword'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $sData = str_replace('&amp;', '&', TTools::GetArrayAsURL($aData));
        // build request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sData);
        $response = curl_exec($ch);

        $aFullResponse = null;
        if (curl_errno($ch)) {
            TTools::WriteLogEntry('Payment EasyCash Error: '.curl_error($ch), 1, __FILE__, __LINE__);
            $aFullResponse = curl_errno($ch).': '.curl_error($ch);
        } else {
            $aFullResponse = array('header' => substr($response, 0, (strpos($response, "\r\n\r\n"))), 'response' => substr($response, (strpos($response, "\r\n\r\n") + 4)));
        }
        curl_close($ch);

        return $aFullResponse;
    }

    /**
     * @param TdbShopOrder $oOrder
     *
     * @return string
     */
    protected function GetTransactionIdForEasyCash($oOrder)
    {
        return $oOrder->fieldOrdernumber.'_'.date('Y-m-d H:i:s', time());
    }

    /**
     * @param TdbShopOrder $oOrder
     *
     * @return string
     */
    protected function GetOrderDescriptionTextForEasyCash($oOrder)
    {
        $aName = array();
        $oShop = $oOrder->GetFieldShop();
        if ($oShop) {
            $aName[] = $oShop->fieldName;
        }
        $aName[] = $oOrder->fieldOrdernumber;

        return implode(' ', $aName);
    }
}
