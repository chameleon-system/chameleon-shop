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
 * @property TdbDataExtranetGroup $oTable
 */
class TCMSShopTableEditor_DataExtranetGroup extends TCMSTableEditor
{
    /**
     * gets called after save if all posted data was valid.
     *
     * @param TIterator  $oFields    holds an iterator of all field classes from DB table with the posted values or default if no post data is present
     * @param TCMSRecord $oPostTable holds the record object of all posted data
     *
     * @return void
     */
    protected function PostSaveHook(&$oFields, &$oPostTable)
    {
        parent::PostSaveHook($oFields, $oPostTable);
        if ($this->oTable->fieldAutoAssignActive) {
            // has to from/to changed? if so -
            $bStartChanged = ($this->oTablePreChangeData->sqlData['auto_assign_order_value_start'] != $this->oTable->sqlData['auto_assign_order_value_start']);
            $bEndChanged = ($this->oTablePreChangeData->sqlData['auto_assign_order_value_end'] != $this->oTable->sqlData['auto_assign_order_value_end']);
            $bActivationChanged = ($this->oTablePreChangeData->sqlData['auto_assign_active'] != $this->oTable->sqlData['auto_assign_active']);
            if ($bStartChanged || $bEndChanged || $bActivationChanged) {
                $this->oTable->UpdateAutoAssignAllUsers();
            }
        }
    }
}
