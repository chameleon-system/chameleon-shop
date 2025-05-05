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
     * @param float $dOrderValue
     *
     * @return TdbDataExtranetGroupList
     */
    public static function GetAutoGroupsForValue($dOrderValue)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedOrderValue = $connection->quote($dOrderValue);

        $query = "
        SELECT *
          FROM `data_extranet_group`
         WHERE `auto_assign_active` = '1'
           AND `auto_assign_order_value_start` <= {$quotedOrderValue}
           AND (`auto_assign_order_value_end` > {$quotedOrderValue} OR `auto_assign_order_value_end` = 0)
      ORDER BY `auto_assign_order_value_start`
    ";

        return TdbDataExtranetGroupList::GetList($query);
    }
}
