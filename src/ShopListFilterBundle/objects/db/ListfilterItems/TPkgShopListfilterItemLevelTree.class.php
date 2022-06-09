<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemLevelTree extends TPkgShopListfilterItemMultiselectMLT
{
    /**
     * you need to set this to the table name of the connected table.
     *
     * @var string
     */
    protected $sItemTableName = 'shop_category';

    /**
     * you need to set this to the field name in the article table (note: the field is not derived from
     * the table name since this may differ).
     *
     * @var string
     */
    protected $sItemFieldName = 'shop_category_mlt';

    /**
     * Get the complete rendered filter as html.
     *
     * @return string
     */
    public function GetRenderedCategoryTree()
    {
        $aFilterCategories = $this->GetOptions();
        $oTree = TShopCategoryTree::GetCategoryTree();
        $oTree->ResetCounter();
        foreach ($aFilterCategories as $sCategoryId => $sCategoryCount) {
            $oTree->AddItemCount($sCategoryId, $sCategoryCount);
        }
        if (is_array($this->aActiveFilterData) && count($this->aActiveFilterData) > 0) {
            $oTree->MarkActiveCategories($this->aActiveFilterData);
        }
        $sRenderedCategoryTree = $oTree->Render($this->id, true, true);

        return $sRenderedCategoryTree;
    }

    /**
     * builds the sql query for the GetQueryRestrictionForActiveFilter method
     * we only want to show results that are values of the selected shop attribute in the filter item.
     *
     * Add id for all child categories to the filtered value to get all articles from child categories
     *
     * @return string
     */
    protected function GetSQLQueryForQueryRestrictionForActiveFilter()
    {
        $databaseConnection = $this->getDatabaseConnection();
        $values = $this->aActiveFilterData;
        if (is_array($values) && count($values) > 0) {
            $values = array_merge($values, $this->GetChildCategoryIdList($values[0]));
        }
        $quotedValues = implode(',', array_map(array($databaseConnection, 'quote'), $values));
        $quotedTargetTable = $databaseConnection->quoteIdentifier($this->sItemTableName);
        $quotedTargetMltTable = $databaseConnection->quoteIdentifier('shop_article_'.$this->sItemTableName.'_mlt');
        $quotedTargetTableNameField = $databaseConnection->quoteIdentifier($this->GetTargetTableNameField());

        $sItemListQuery = "SELECT $quotedTargetMltTable.*
                           FROM $quotedTargetTable
                           INNER JOIN $quotedTargetMltTable 
                           ON $quotedTargetTable.`id` = $quotedTargetMltTable.`target_id`
                           WHERE $quotedTargetTableNameField IN ($quotedValues)";

        return $sItemListQuery;
    }

    /**
     * Get all child category id from given master category.
     *
     * @param string $sMasterCategoryId
     *
     * @return array
     */
    protected function GetChildCategoryIdList($sMasterCategoryId)
    {
        return TdbShopCategory::GetNewInstance($sMasterCategoryId)->GetAllChildrenIds();
    }

    /**
     * returns the name field of the target table
     * Returns the id field because filter use id instead names.
     *
     * @return string
     */
    protected function GetTargetTableNameField()
    {
        return 'id';
    }
}
