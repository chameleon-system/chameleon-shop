<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;

class TShop_DataExtranetGroup extends TShop_DataExtranetGroupAutoParent
{
    /*
     * update the group assignment for the user. return true if at least one group was changed
     * @return boolean
    */
    /**
     * @param string $sUserid
     * @param float $dOrderValue
     *
     * @return bool
     */
    public static function UpdateAutoAssignToUserQuick($sUserid, $dOrderValue)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedUserId = $connection->quote($sUserid);

        // get all assigned groups that are NOT auto assigned
        $query = "
        SELECT `data_extranet_user_data_extranet_group_mlt`.`target_id`
          FROM `data_extranet_user_data_extranet_group_mlt`
    INNER JOIN `data_extranet_group`
            ON `data_extranet_user_data_extranet_group_mlt`.`target_id` = `data_extranet_group`.`id`
         WHERE `data_extranet_user_data_extranet_group_mlt`.`source_id` = {$quotedUserId}
           AND `data_extranet_group`.`auto_assign_active` = '0'
    ";

        $aUserGroups = [];
        $statement = $connection->executeQuery($query);

        while ($aRow = $statement->fetchAssociative()) {
            $aUserGroups[] = $connection->quote($aRow['target_id']);
        }

        // now add groups set via auto assign
        $oGroups = TdbDataExtranetGroupList::GetAutoGroupsForValue($dOrderValue);

        while ($oGroup = $oGroups->Next()) {
            $aUserGroups[] = $connection->quote($oGroup->id);
        }

        // update user
        $deleteQuery = "
        DELETE FROM `data_extranet_user_data_extranet_group_mlt`
              WHERE `source_id` = {$quotedUserId}
    ";
        $deletedRows = $connection->executeStatement($deleteQuery);

        $insertedRows = 0;

        if (count($aUserGroups) > 0) {
            $aInsertParts = [];
            foreach ($aUserGroups as $quotedGroupId) {
                $aInsertParts[] = "({$quotedUserId}, {$quotedGroupId})";
            }

            $insertQuery = "
            INSERT INTO `data_extranet_user_data_extranet_group_mlt`
                (`source_id`, `target_id`)
             VALUES " . implode(', ', $aInsertParts);

            $insertedRows = $connection->executeStatement($insertQuery);
        }

        if (($deletedRows + $insertedRows) > 0) {
            ServiceLocator::get('chameleon_system_core.cache')->callTrigger('data_extranet_user', $sUserid);
        }

        return ($deletedRows + $insertedRows) > 0;
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
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedGroupId = $connection->quote($this->id);

        // delete old group assignments
        $deleteQuery = "
        DELETE FROM `data_extranet_user_data_extranet_group_mlt`
         WHERE `data_extranet_user_data_extranet_group_mlt`.`target_id` = {$quotedGroupId}
    ";
        $connection->executeStatement($deleteQuery);

        // build new assignment query
        $selectQuery = "
        SELECT `data_extranet_user`.`id` AS source_id,
               {$quotedGroupId} AS target_id
          FROM `data_extranet_user`
     LEFT JOIN `shop_order`
            ON `data_extranet_user`.`id` = `shop_order`.`data_extranet_user_id`
           AND (`shop_order`.`id` IS NULL OR `shop_order`.`canceled` = '0')
      GROUP BY `data_extranet_user`.`id`
     HAVING (SUM(`shop_order`.`value_total`) >= {$this->fieldAutoAssignOrderValueStart}
    ";

        if ($this->fieldAutoAssignOrderValueEnd > 0) {
            $selectQuery .= " AND SUM(`shop_order`.`value_total`) < {$this->fieldAutoAssignOrderValueEnd}";
        }

        $selectQuery .= ")";

        if (0.01 == $this->fieldAutoAssignOrderValueEnd) {
            $selectQuery .= ' OR SUM(`shop_order`.`value_total`) IS NULL';
        }

        $insertQuery = "
        INSERT INTO `data_extranet_user_data_extranet_group_mlt` (`source_id`, `target_id`)
        {$selectQuery}
    ";

        $insertedRows = $connection->executeStatement($insertQuery);

        return $insertedRows;
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
