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
 * @extends TCMSInterfaceManagerBaseExportCSV<TdbShopOrderItem, TdbShopOrderItemList>
 */
class TShopInterfaceExportOrderItems extends TCMSInterfaceManagerBaseExportCSV
{
    /**
     * OVERWRITE THIS TO FETCH THE DATA. MUST RETURN A TCMSRecordList.
     *
     * @return TdbShopOrderItemList
     */
    protected function GetDataList()
    {
        return TdbShopOrderItemList::GetList();
    }

    /**
     * OVERWRITE THIS IF YOU NEED TO ADD ANY OTHER DATA TO THE ROW OBJECT.
     *
     * @param TdbShopOrderItem $oDataObjct
     *
     * @return array
     */
    protected function GetExportRowFromDataObject(&$oDataObjct)
    {
        $aRow = parent::GetExportRowFromDataObject($oDataObjct);

        $oOrder = &$oDataObjct->GetFieldShopOrder();

        $aRow['ordernumber'] = $oOrder->fieldOrdernumber;
        $aRow['customer_number'] = $oOrder->fieldCustomerNumber;
        $aRow['orderdate'] = $oOrder->fieldDatecreated;
        $aRow['affiliate_code'] = $oOrder->fieldAffiliateCode;

        $oLocale = &TCMSLocal::GetActive();
        $aRow['price'] = $oLocale->FormatNumber($aRow['price'], 2);
        $aRow['price_reference'] = $oLocale->FormatNumber($aRow['price_reference'], 2);
        $aRow['vat_percent'] = $oLocale->FormatNumber($aRow['vat_percent'], 2);

        $aRow['order_price_total'] = $oLocale->FormatNumber($aRow['order_price_total'], 2);
        $aRow['order_price_after_discounts'] = $oLocale->FormatNumber($aRow['order_price_after_discounts'], 2);
        $aRow['order_total_weight'] = $oLocale->FormatNumber($aRow['order_total_weight'], 2);
        $aRow['order_total_volume'] = $oLocale->FormatNumber($aRow['order_total_volume'], 2);
        $aRow['order_price'] = $oLocale->FormatNumber($aRow['order_price'], 2);
        $aRow['order_amount'] = $oLocale->FormatNumber($aRow['order_amount'], 2);

        return $aRow;
    }

    protected function GetFieldMapping()
    {
        $aFields = array(
            'orderdate' => 'DATETIME NOT NULL',
            'ordernumber' => 'VARCHAR( 255 ) NOT NULL',
            'customer_number' => 'VARCHAR( 255 ) NOT NULL',
            'affiliate_code' => 'VARCHAR( 255 ) NOT NULL',
            'name' => 'VARCHAR( 255 ) NOT NULL',
            'articlenumber' => 'VARCHAR( 255 ) NOT NULL',
            'price' => 'VARCHAR( 255 ) NOT NULL',
            'price_reference' => 'VARCHAR( 255 ) NOT NULL',
            'vat_percent' => 'VARCHAR( 255 ) NOT NULL',
            'stock' => 'VARCHAR( 255 ) NOT NULL',
            'virtual_article' => 'VARCHAR( 255 ) NOT NULL',
            'exclude_from_vouchers' => 'VARCHAR( 255 ) NOT NULL',
            'isbn_13' => 'VARCHAR( 255 ) NOT NULL',
            'isbn_10' => 'VARCHAR( 255 ) NOT NULL',
            'shop_binding_name' => 'VARCHAR( 255 ) NOT NULL',
            'is_new' => 'VARCHAR( 255 ) NOT NULL',
            'pages' => 'VARCHAR( 255 ) NOT NULL',
            'followerISBN' => 'VARCHAR( 255 ) NOT NULL',
            'order_amount' => 'VARCHAR( 255 ) NOT NULL',
            'order_price_total' => 'VARCHAR( 255 ) NOT NULL',
            'order_price_after_discounts' => 'VARCHAR( 255 ) NOT NULL',
            'order_total_weight' => 'VARCHAR( 255 ) NOT NULL',
            'order_total_volume' => 'VARCHAR( 255 ) NOT NULL',
            'order_price' => 'VARCHAR( 255 ) NOT NULL',
            'is_bundle' => 'VARCHAR( 255 ) NOT NULL',
        );

        return $aFields;
    }
}
