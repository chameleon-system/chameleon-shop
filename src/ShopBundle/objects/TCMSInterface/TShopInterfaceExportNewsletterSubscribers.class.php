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
 * @extends TCMSInterfaceManagerBaseExportCSV<TCMSRecord>
 */
class TShopInterfaceExportNewsletterSubscribers extends TCMSInterfaceManagerBaseExportCSV
{
    /**
     * OVERWRITE THIS TO FETCH THE DATA. MUST RETURN A TCMSRecordList.
     *
     * @return TCMSRecordList<TCMSRecord>
     */
    protected function GetDataList()
    {
        $query = "SELECT Sal.`name` AS Anrede,
                       `pkg_newsletter_user`.`firstname` AS Vorname,
                       `pkg_newsletter_user`.`lastname` AS Nachname,
                       `pkg_newsletter_user`.`email` AS EMail,
                       `pkg_newsletter_user`.`signup_date` AS NewsletterAnmeldedatum,
                       Usr.`customer_number` AS KundenNr,
                       `pkg_newsletter_user`.`id` AS NewsletterId,
                       `pkg_newsletter_user`.`optincode` AS Code

                   FROM `pkg_newsletter_user`
              LEFT JOIN `data_extranet_salutation` AS Sal ON (`pkg_newsletter_user`.`data_extranet_salutation_id` = Sal.`id`)
              LEFT JOIN `data_extranet_user` AS Usr ON (`pkg_newsletter_user`.`data_extranet_user_id` = Usr.`id`)
                  WHERE `pkg_newsletter_user`.`optin` = '1';
               ";
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
    protected function GetExportRowFromDataObject($oDataObjct)
    {
        $aRow = parent::GetExportRowFromDataObject($oDataObjct);

        return $aRow;
    }

    protected function GetFieldMapping()
    {
        $aFields = array('NewsletterId' => 'int(11) NOT NULL', 'Anrede' => 'VARCHAR( 255 ) NOT NULL', 'EMail' => 'VARCHAR( 255 ) NOT NULL', 'Vorname' => 'VARCHAR( 255 ) NOT NULL', 'Nachname' => 'VARCHAR( 255 ) NOT NULL', 'KundenNr' => 'int(11) NOT NULL', 'NewsletterAnmeldedatum' => 'DATETIME NOT NULL', 'Code' => 'VARCHAR( 255 ) NOT NULL');

        return $aFields;
    }
}
