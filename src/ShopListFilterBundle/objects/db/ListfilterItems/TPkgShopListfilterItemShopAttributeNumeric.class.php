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
     * @return float
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
     * @return float
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
            $sEscapedTargetTable = MySqlLegacySupport::getInstance()->real_escape_string($this->sItemTableName);
            $sEscapedTargetMLTTable = MySqlLegacySupport::getInstance()->real_escape_string('shop_article_'.$this->sItemTableName.'_mlt');
            $oShopAttribute = $this->GetFieldShopAttribute();

            $sQuery = "SELECT `{$sEscapedTargetMLTTable}`.*
                     FROM `{$sEscapedTargetTable}`
               INNER JOIN `{$sEscapedTargetMLTTable}` ON `{$sEscapedTargetTable}`.`id` = `{$sEscapedTargetMLTTable}`.`target_id`
                    WHERE ".$this->GetTargetTableNameField().' >= '.MySqlLegacySupport::getInstance()->real_escape_string($dStartValue).'
                      AND '.$this->GetTargetTableNameField().' <= '.MySqlLegacySupport::getInstance()->real_escape_string($dEndValue)."
                      AND `{$sEscapedTargetTable}`.`shop_attribute_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oShopAttribute->id)."'";
        }

        return $sQuery;
    }
}
