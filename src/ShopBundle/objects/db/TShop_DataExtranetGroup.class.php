<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShop_DataExtranetGroup extends TShop_DataExtranetGroupAutoParent
{
    /*
     * update the group assignment for the user. return true if at least one group was changed
     * @return boolean
    */
    /**
     * @return bool
     *
     * @param string $sUserid
     * @param float $dOrderValue
     */
    public static function UpdateAutoAssignToUserQuick($sUserid, $dOrderValue)
    {
        $sEscapedUserid = MySqlLegacySupport::getInstance()->real_escape_string($sUserid);
        // get all assigned groups that are NOT auto assigned
        $query = "SELECT `data_extranet_user_data_extranet_group_mlt`.`target_id`
                  FROM `data_extranet_user_data_extranet_group_mlt`
            INNER JOIN `data_extranet_group` ON `data_extranet_user_data_extranet_group_mlt`.`target_id` = `data_extranet_group`.`id`
                 WHERE `data_extranet_user_data_extranet_group_mlt`.`source_id` = '{$sEscapedUserid}'
                   AND `data_extranet_group`.`auto_assign_active` = '0'
               ";
        $aUserGroups = array();
        $tRes = MySqlLegacySupport::getInstance()->query($query);
        while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
            $aUserGroups[] = MySqlLegacySupport::getInstance()->real_escape_string($aRow['target_id']);
        }
        // now add groups set via auto assign
        $oGroups = TdbDataExtranetGroupList::GetAutoGroupsForValue($dOrderValue);
        while ($oGroup = $oGroups->Next()) {
            $aUserGroups[] = MySqlLegacySupport::getInstance()->real_escape_string($oGroup->id);
        }

        // update user
        // note: we use a query here to prevent overhead
        $query = "DELETE FROM `data_extranet_user_data_extranet_group_mlt`
                      WHERE `source_id` = '{$sEscapedUserid}'";
        MySqlLegacySupport::getInstance()->query($query);
        $iRecordsChanged = MySqlLegacySupport::getInstance()->affected_rows();
        if (count($aUserGroups) > 0) {
            $query = 'INSERT INTO `data_extranet_user_data_extranet_group_mlt` (`source_id`,`target_id`) VALUES ';
            $aInsertParts = array();
            foreach ($aUserGroups as $sGroupId) {
                $aInsertParts[] = "('{$sEscapedUserid}','{$sGroupId}')";
            }
            $query .= implode(', ', $aInsertParts);
            MySqlLegacySupport::getInstance()->query($query);
            $iRecordsChanged = $iRecordsChanged + MySqlLegacySupport::getInstance()->affected_rows();
        }
        if ($iRecordsChanged > 0) {
            TCacheManager::PerformeTableChange('data_extranet_user', $sUserid);
        }

        return $iRecordsChanged > 0;
    }

    /*
     * update ALL users
     * @return int
    */
    /**
     * @return int
     */
    public function UpdateAutoAssignAllUsers()
    {
        // get all users that no longer apply
        $query = "DELETE FROM `data_extranet_user_data_extranet_group_mlt` WHERE `data_extranet_user_data_extranet_group_mlt`.`target_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'";
        MySqlLegacySupport::getInstance()->query($query);

        // now add group to every one with the corrseponding value
        $query = "SELECT `data_extranet_user`.`id` AS source_id, '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."' AS target_id
                  FROM `data_extranet_user`
             LEFT JOIN `shop_order` ON `data_extranet_user`.`id` = `shop_order`.`data_extranet_user_id`
                   AND (`shop_order`.`id` IS NULL OR `shop_order`.`canceled` = '0')
              GROUP BY `data_extranet_user` .`id`
                HAVING
                       (SUM(`shop_order`.`value_total`) >= {$this->fieldAutoAssignOrderValueStart}
               ";
        if ($this->fieldAutoAssignOrderValueEnd > 0) {
            $query .= "AND SUM(`shop_order`.`value_total`) < {$this->fieldAutoAssignOrderValueEnd}";
        }
        $query .= ')';
        if (0.01 == $this->fieldAutoAssignOrderValueEnd) {
            $query .= ' OR SUM(  `shop_order`.`value_total` ) IS NULL';
        } // allow users that have no orders
        $query = 'INSERT INTO `data_extranet_user_data_extranet_group_mlt` (`source_id`,`target_id`) '.$query;
        MySqlLegacySupport::getInstance()->query($query);

        return MySqlLegacySupport::getInstance()->affected_rows();
    }

    /*
     * update the group assignment for the user. return true if at least one group was changed
     * @return boolean
    */
    /**
     * @param TShopDataExtranetUser $oUser
     *
     * @return bool
     */
    public static function UpdateAutoAssignToUser($oUser)
    {
        $dOrderValue = $oUser->GetTotalOrderValue();
        $bResult = TdbDataExtranetGroup::UpdateAutoAssignToUserQuick($oUser->id, $dOrderValue);

        return $bResult;
    }
}
