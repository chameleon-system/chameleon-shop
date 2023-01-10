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
 * @extends TCMSInterfaceManagerBaseExportCSV<TdbShopOrder>
 */
class TShopInterfaceExportOrders extends TCMSInterfaceManagerBaseExportCSV
{
    /**
     * OVERWRITE THIS TO FETCH THE DATA. MUST RETURN A TCMSRecordList.
     *
     * @return TdbShopOrderList
     */
    protected function GetDataList()
    {
        return TdbShopOrderList::GetList();
    }

    /**
     * OVERWRITE THIS IF YOU NEED TO ADD ANY OTHER DATA TO THE ROW OBJECT.
     *
     * @param TdbShopOrder $oDataObjct
     *
     * @return array
     */
    protected function GetExportRowFromDataObject($oDataObjct)
    {
        $aRow = parent::GetExportRowFromDataObject($oDataObjct);
        $aRow['adr_billing_country_name'] = '';
        $aRow['adr_billing_salutation_name'] = '';
        $aRow['adr_shipping_country_name'] = '';
        $aRow['adr_shipping_salutation_name'] = '';

        $oCountry = $oDataObjct->GetFieldAdrBillingCountry();
        if (!is_null($oCountry)) {
            $aRow['adr_billing_country_name'] = $oCountry->fieldName;
        }
        $oCountry = $oDataObjct->GetFieldAdrShippingCountry();
        if (!is_null($oCountry)) {
            $aRow['adr_shipping_country_name'] = $oCountry->fieldName;
        }
        $oSal = $oDataObjct->GetFieldAdrBillingSalutation();
        if (!is_null($oSal)) {
            $aRow['adr_billing_salutation_name'] = $oSal->fieldName;
        }
        $oSal = $oDataObjct->GetFieldAdrShippingSalutation();
        if (!is_null($oSal)) {
            $aRow['adr_shipping_salutation_name'] = $oSal->fieldName;
        }

        $oLocale = TCMSLocal::GetActive();
        $aRow['value_vat_total'] = $oLocale->FormatNumber($aRow['value_vat_total'], 2);
        $aRow['count_unique_articles'] = $oLocale->FormatNumber($aRow['count_unique_articles'], 2);

        $aRow['value_article'] = $oLocale->FormatNumber($aRow['value_article'], 2);
        $aRow['value_total'] = $oLocale->FormatNumber($aRow['value_total'], 2);
        $aRow['totalweight'] = $oLocale->FormatNumber($aRow['totalweight'], 2);
        $aRow['totalvolume'] = $oLocale->FormatNumber($aRow['totalvolume'], 2);
        $aRow['count_articles'] = $oLocale->FormatNumber($aRow['count_articles'], 2);

        $aRow['shop_shipping_group_price'] = $oLocale->FormatNumber($aRow['shop_shipping_group_price'], 2);
        $aRow['shop_shipping_group_vat_percent'] = $oLocale->FormatNumber($aRow['shop_shipping_group_vat_percent'], 2);
        $aRow['shop_payment_method_price'] = $oLocale->FormatNumber($aRow['shop_payment_method_price'], 2);
        $aRow['shop_payment_method_vat_percent'] = $oLocale->FormatNumber($aRow['shop_payment_method_vat_percent'], 2);

        return $aRow;
    }

    protected function GetFieldMapping()
    {
        $aFields = array('datecreated' => 'DATETIME NOT NULL', 'ordernumber' => 'VARCHAR( 255 ) NOT NULL', 'customer_number' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_company' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_salutation_name' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_firstname' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_lastname' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_street' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_streetnr' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_city' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_postalcode' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_country_name' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_telefon' => 'VARCHAR( 255 ) NOT NULL', 'adr_billing_fax' => 'VARCHAR( 255 ) NOT NULL',
            'adr_shipping_use_billing' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_salutation_name' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_company' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_firstname' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_lastname' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_street' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_streetnr' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_city' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_postalcode' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_country_name' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_telefon' => 'VARCHAR( 255 ) NOT NULL', 'adr_shipping_fax' => 'VARCHAR( 255 ) NOT NULL',

            'shop_shipping_group_name' => 'VARCHAR( 255 ) NOT NULL', 'shop_shipping_group_price' => 'VARCHAR( 255 ) NOT NULL', 'shop_shipping_group_vat_percent' => 'VARCHAR( 255 ) NOT NULL',

            'shop_payment_method_name' => 'VARCHAR( 255 ) NOT NULL', 'shop_payment_method_price' => 'VARCHAR( 255 ) NOT NULL', 'shop_payment_method_vat_percent' => 'VARCHAR( 255 ) NOT NULL',
            'shop_order_vat' => 'VARCHAR( 255 ) NOT NULL', 'value_article' => 'VARCHAR( 255 ) NOT NULL', 'value_total' => 'VARCHAR( 255 ) NOT NULL', 'user_email' => 'VARCHAR( 255 ) NOT NULL', 'value_vat_total' => 'VARCHAR( 255 ) NOT NULL', 'count_articles' => 'VARCHAR( 255 ) NOT NULL', 'count_unique_articles' => 'VARCHAR( 255 ) NOT NULL', 'totalweight' => 'VARCHAR( 255 ) NOT NULL', 'totalvolume' => 'VARCHAR( 255 ) NOT NULL', 'affiliate_code' => 'VARCHAR( 255 ) NOT NULL', );

        return $aFields;
    }
}
