<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopInterfaceExportSearchWords extends TCMSInterfaceManagerBaseExportCSV
{
    /**
     * OVERWRITE THIS TO FETCH THE DATA. MUST RETURN A TCMSRecordList.
     *
     * @return TCMSRecordList
     */
    protected function GetDataList()
    {
        $query = 'SELECT `shop_search_log`.*,
                      `data_extranet_user`.`name` AS login,
                      `data_extranet_user`.`firstname`,
                      `data_extranet_user`.`lastname`
                 FROM `shop_search_log`
            LEFT JOIN `data_extranet_user` ON `shop_search_log`.`data_extranet_user_id` = `data_extranet_user`.`id`
             ORDER BY `shop_search_log`.`search_date` DESC
      ';

        return TdbShopSearchLogList::GetList();
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
        $aFields = array('search_date' => 'DATETIME NOT NULL', 'number_of_results' => 'INT( 11 ) NOT NULL', 'name' => 'VARCHAR( 255 ) NOT NULL', 'login' => 'VARCHAR( 255 ) NOT NULL', 'firstname' => 'VARCHAR( 255 ) NOT NULL', 'lastname' => 'VARCHAR( 255 ) NOT NULL');

        return $aFields;
    }
}
