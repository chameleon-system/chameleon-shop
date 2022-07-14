<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShop_DataExtranetGroupList extends TShop_DataExtranetGroupListAutoParent
{
    /*
     * return a list of ids for the given order value
     * @return TdbDataExtranetGroupList
    */
    /**
     * @return TdbDataExtranetGroupList
     *
     * @param float $dOrderValue
     */
    public static function GetAutoGroupsForValue($dOrderValue)
    {
        $dEscaped = MySqlLegacySupport::getInstance()->real_escape_string($dOrderValue);
        $query = "SELECT *
                  FROM `data_extranet_group`
                 WHERE `auto_assign_active` = '1'
                   AND `auto_assign_order_value_start` <= {$dEscaped}
                   AND (`auto_assign_order_value_end` > {$dEscaped} OR `auto_assign_order_value_end` = 0)
              ORDER BY `auto_assign_order_value_start`
               ";

        return TdbDataExtranetGroupList::GetList($query);
    }
}
