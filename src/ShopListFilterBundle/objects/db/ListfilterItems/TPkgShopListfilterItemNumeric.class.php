<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemNumeric extends TdbPkgShopListfilterItem
{
    public const URL_PARAMETER_FILTER_START_VALUE = 'dStartValue';

    public const URL_PARAMETER_FILTER_END_VALUE = 'dEndValue';

    /**
     * return active start value.
     *
     * @return float|false
     */
    public function GetActiveStartValue()
    {
        $sStartValue = false;
        if (is_array($this->aActiveFilterData) && array_key_exists(
            self::URL_PARAMETER_FILTER_START_VALUE,
            $this->aActiveFilterData
        )
        ) {
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
        if (is_array($this->aActiveFilterData) && array_key_exists(
            self::URL_PARAMETER_FILTER_END_VALUE,
            $this->aActiveFilterData
        )
        ) {
            $sEndValue = $this->aActiveFilterData[self::URL_PARAMETER_FILTER_END_VALUE];
        }

        return $sEndValue;
    }

    /**
     * return setting of element as hidden input fields.
     *
     * @return string
     */
    public function GetActiveSettingAsHiddenInputField()
    {
        $sInput = '';
        if (false !== $this->GetActiveStartValue()) {
            $sInput .= '<input type="hidden" name="'.TGlobal::OutHTML(
                $this->GetURLInputName().'['.self::URL_PARAMETER_FILTER_START_VALUE.']'
            ).'" value="'.TGlobal::OutHTML($this->GetActiveStartValue()).'" />';
        }
        if (false !== $this->GetActiveEndValue()) {
            $sInput .= '<input type="hidden" name="'.TGlobal::OutHTML(
                $this->GetURLInputName().'['.self::URL_PARAMETER_FILTER_END_VALUE.']'
            ).'" value="'.TGlobal::OutHTML($this->GetActiveEndValue()).'" />';
        }

        return $sInput;
    }

    /**
     * return the query restriction for active filter. returns false if there
     * is no active restriction for this item.
     *
     * @return string
     */
    public function GetQueryRestrictionForActiveFilter()
    {
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sQuery = '';
        $dStartValue = $this->GetActiveStartValue();
        $dEndValue = $this->GetActiveEndValue();

        if ($dStartValue > 0 && $dEndValue > 0) {
            $sQuery = "`shop_article`.`{$this->sItemFieldName}` >= ".$connection->quote($dStartValue)." AND `shop_article`.`{$this->sItemFieldName}` <= ".$connection->quote($dEndValue);
        } elseif ($dStartValue > 0) {
            $sQuery = "`shop_article`.`{$this->sItemFieldName}` >= ".$connection->quote($dStartValue);
        } elseif ($dEndValue > 0) {
            $sQuery = "`shop_article`.`{$this->sItemFieldName}` <= ".$connection->quote($dEndValue);
        }

        return $sQuery;
    }
}
