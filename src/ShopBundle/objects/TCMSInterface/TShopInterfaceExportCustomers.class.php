<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopInterfaceExportCustomers extends TCMSInterfaceManagerBaseExportCSV
{
    /**
     * OVERWRITE THIS TO FETCH THE DATA. MUST RETURN A TCMSRecordList.
     *
     * @return TCMSRecordList
     */
    protected function GetDataList()
    {
        $query = 'SELECT `data_extranet_user`.`id` AS UserId,
                       `data_extranet_user`.`name` AS EMail,
                       `data_extranet_user`.`customer_number` AS KundenNr,
                       `data_extranet_user`.`datecreated`,
                       `pkg_newsletter_user`.`signup_date` AS NewsletterAnmeldedatum,

                        AdrB.`company` AS RechAdr_Firma,
                        AdrBSalutation.`name` AS RechAdr_Anrede,
                        AdrB.`firstname` AS RechAdr_Vorname,
                        AdrB.`lastname` AS RechAdr_Nachname,
                        AdrB.`street` AS RechAdr_Strasse,
                        AdrB.`streetnr` AS RechAdr_Hausnummer,
                        AdrB.`city` AS RechAdr_Stadt,
                        AdrB.`postalcode` AS RechAdr_PLZ,
                        AdrB.`telefon` AS RechAdr_Telefon,
                        AdrB.`fax` AS RechAdr_Fax,
                        AdrBCountry.`name` AS RechAdr_Land,

                        AdrS.`company` AS LiefAdr_Firma,
                        AdrSSalutation.`name` AS LiefAdr_Anrede,
                        AdrS.`firstname` AS LiefAdr_Vorname,
                        AdrS.`lastname` AS LiefAdr_Nachname,
                        AdrS.`street` AS LiefAdr_Strasse,
                        AdrS.`streetnr` AS LiefAdr_Hausnummer,
                        AdrS.`city` AS LiefAdr_Stadt,
                        AdrS.`postalcode` AS LiefAdr_PLZ,
                        AdrS.`telefon` AS LiefAdr_Telefon,
                        AdrS.`fax` AS LiefAdr_Fax,
                        AdrSCountry.`name` AS LiefAdr_Land

                   FROM `data_extranet_user`
              LEFT JOIN `data_extranet_user_address` AS AdrB ON (`data_extranet_user`.`default_billing_address_id` = AdrB.`id`)
              LEFT JOIN `data_extranet_salutation` AS AdrBSalutation ON AdrB.`data_extranet_salutation_id` = AdrBSalutation.`id`
              LEFT JOIN `data_country` AS AdrBCountry ON AdrB.`data_country_id` = AdrBCountry.`id`

              LEFT JOIN `data_extranet_user_address` AS AdrS ON (`data_extranet_user`.`default_shipping_address_id` = AdrS.`id`)
              LEFT JOIN `data_extranet_salutation` AS AdrSSalutation ON AdrB.`data_extranet_salutation_id` = AdrSSalutation.`id`
              LEFT JOIN `data_country` AS AdrSCountry ON AdrS.`data_country_id` = AdrSCountry.`id`

              LEFT JOIN `pkg_newsletter_user` ON (`data_extranet_user`.`id` = `pkg_newsletter_user`.`data_extranet_user_id`)
               ';
        $oTCMSRecordList = new TCMSRecordList();
        /** @var $oTCMSRecordList TCMSRecordList */
        $oTCMSRecordList->sTableName = 'data_extranet_user';
        $oTCMSRecordList->Load($query);

        return $oTCMSRecordList;
    }

    /**
     * OVERWRITE THIS IF YOU NEED TO ADD ANY OTHER DATA TO THE ROW OBJECT.
     *
     * @param TCMSRecord $oDataObjct
     *
     * @return array
     */
    protected function GetExportRowFromDataObject(&$oDataObjct)
    {
        $aRow = parent::GetExportRowFromDataObject($oDataObjct);

        return $aRow;
    }

    protected function GetFieldMapping()
    {
        $aFields = array('UserId' => 'int(11) NOT NULL', 'EMail' => 'VARCHAR( 255 ) NOT NULL', 'KundenNr' => 'int(11) NOT NULL', 'datecreated' => 'DATETIME NOT NULL', 'NewsletterAnmeldedatum' => 'DATETIME NOT NULL',
            'RechAdr_Firma' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Anrede' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Vorname' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Nachname' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Strasse' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Hausnummer' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Stadt' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_PLZ' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Telefon' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Fax' => 'VARCHAR( 255 ) NOT NULL', 'RechAdr_Land' => 'VARCHAR( 255 ) NOT NULL',
            'LiefAdr_Firma' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Anrede' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Vorname' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Nachname' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Strasse' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Hausnummer' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Stadt' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_PLZ' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Telefon' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Fax' => 'VARCHAR( 255 ) NOT NULL', 'LiefAdr_Land' => 'VARCHAR( 255 ) NOT NULL', );

        return $aFields;
    }
}
