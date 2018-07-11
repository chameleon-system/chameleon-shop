<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopAffiliate_TradeDoubler extends TdbPkgShopAffiliate
{
    /**
     * add custom vars to the view.
     *
     * @param TdbShopOrder $oOrder
     * @param array        $aParameter
     */
    protected function GetAdditionalViewVariables(&$oOrder, &$aParameter)
    {
        parent::GetAdditionalViewVariables($oOrder, $aParameter);

        $aParameter['currency'] = $this->GetValueFromArray($aParameter, 'currency');
        $aParameter['organization'] = $this->GetValueFromArray($aParameter, 'organization');
        $aParameter['checksumCode'] = $this->GetValueFromArray($aParameter, 'checksumCode');
        $aParameter['event'] = $this->GetValueFromArray($aParameter, 'event');
        $isSale = $this->GetValueFromArray($aParameter, 'isSale');
        $isSecure = $this->GetValueFromArray($aParameter, 'isSecure');
        $aParameter['shop_order__ordernumber'] = rawurlencode($aParameter['shop_order__ordernumber']);
        $aParameter['orderNumber'] = $aParameter['shop_order__ordernumber'];
        $aParameter['orderValue'] = $aParameter['dNetProductValue'];

        $aParameter['reportInfo'] = '';

        $oItems = $oOrder->GetFieldShopOrderItemList();
        /** @var TdbShopOrderItemList $oItems */
        while ($oItem = $oItems->Next()) {
            /** @var TdbShopOrderItem $oItem */
            $aParameter['reportInfo'] .= 'f1='.$oItem->fieldArticlenumber.'&amp;f2='.$oItem->GetName().'&amp;f3='.$oItem->fieldPrice.'&amp;f4='.$oItem->fieldOrderAmount.'|';
        }

        $aParameter['reportInfo'] = urlencode($aParameter['reportInfo']);

        $aParameter['tduid'] = '';
        if (!empty($_SESSION['TdbPkgShopAffiliate-data']['sCode'])) {
            $aParameter['tduid'] = $_SESSION['TdbPkgShopAffiliate-data']['sCode'];
        }
        //if (!empty($_COOKIE["tduid"]))  $aParameter['tduid'] = $_COOKIE["TdbPkgShopAffiliate-data"]["sCode"];

        if ($isSale) {
            $aParameter['domain'] = 'tbs.tradedoubler.com';
            $aParameter['checkNumberName'] = 'orderNumber';
        } else {
            $aParameter['domain'] = 'tbl.tradedoubler.com';
            $aParameter['checkNumberName'] = 'leadNumber';
            $aParameter['orderValue'] = '1';
        }

        $aParameter['checksum'] = '';
        $aParameter['checksum'] = 'v04'.md5($aParameter['checksumCode'].$aParameter['orderNumber'].$aParameter['orderValue']);

        if ($isSecure) {
            $aParameter['scheme'] = 'https';
        } else {
            $aParameter['scheme'] = 'http';
        }
        /*if ($isSale) {
          $trackBackUrl
          .= "&amp;orderValue=" . $orderValue
          . "&amp;currency=" . $currency;
        }*/
    }

    protected function GetValueFromArray($aData, $sKey)
    {
        if (array_key_exists($sKey, $aData)) {
            return $aData[$sKey];
        } else {
            return '';
        }
    }
}
