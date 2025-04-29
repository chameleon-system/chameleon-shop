<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemShopAttributeNumeric extends TPkgShopListfilterItemMultiselectMLT
{
    const URL_PARAMETER_FILTER_START_VALUE = 'dStartValue';

    const URL_PARAMETER_FILTER_END_VALUE = 'dEndValue';

    /**
     * you need to set this to the table name of the connected table.
     *
     * @var string
     */
    protected $sItemTableName = 'shop_attribute_value';

    /**
     * you need to set this to the field name in the article table (note: the field is not derived from
     * the table name since this may differ).
     *
     * @var string
     */
    protected $sItemFieldName = 'shop_attribute_value_mlt';

    /**
     * return active start value.
     *
     * @return float|false
     */
    public function GetActiveStartValue()
    {
        $sStartValue = false;
        if (is_array($this->aActiveFilterData) && array_key_exists(self::URL_PARAMETER_FILTER_START_VALUE, $this->aActiveFilterData)) {
            $sStartValue = $this->aActiveFilterData[self::URL_PARAMETER_FILTER_START_VALUE];
        }

        return $sStartValue;
    }

    /**
     * return active end value.
     *
     * @return float|false
     */
    public function GetActiveEndValue()
    {
        $sEndValue = false;
        if (is_array($this->aActiveFilterData) && array_key_exists(self::URL_PARAMETER_FILTER_END_VALUE, $this->aActiveFilterData)) {
            $sEndValue = $this->aActiveFilterData[self::URL_PARAMETER_FILTER_END_VALUE];
        }

        return $sEndValue;
    }

    /**
     * @return int
     */
    public function GetMinValue()
    {
        return $this->fieldMinValue;
    }

    /**
     * @return int
     */
    public function GetMaxValue()
    {
        return $this->fieldMaxValue;
    }

    /**
     * builds the sql query for the GetItemName method that is usual used as callback in the GetOptions method
     * we only want to show results that are values of the selected shop attribute in the filter item.
     *
     * @return string
     */
    protected function GetSQLQueryForQueryRestrictionForActiveFilter()
    {
        $dStartValue = $this->GetActiveStartValue();
        $dEndValue = $this->GetActiveEndValue();
        $sQuery = '';

        if (false !== $dStartValue && false !== $dEndValue) {
            $connection = $this->getDatabaseConnection();
            $quotedTargetTable = $connection->quoteIdentifier($this->sItemTableName);
            $quotedMLTTable = $connection->quoteIdentifier('shop_article_' . $this->sItemTableName . '_mlt');
            $quotedStart = $connection->quote($dStartValue);
            $quotedEnd = $connection->quote($dEndValue);
            $quotedAttributeId = $connection->quote($this->GetFieldShopAttribute()->id);
            $fieldName = $this->GetTargetTableNameField();

            $sQuery = "SELECT {$quotedMLTTable}.*
                   FROM {$quotedTargetTable}
             INNER JOIN {$quotedMLTTable} ON {$quotedTargetTable}.`id` = {$quotedMLTTable}.`target_id`
                  WHERE {$fieldName} >= {$quotedStart}
                    AND {$fieldName} <= {$quotedEnd}
                    AND {$quotedTargetTable}.`shop_attribute_id` = {$quotedAttributeId}";
        }

        return $sQuery;
    }
}
