<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPrimaryNaviList extends TPkgShopPrimaryNaviListAutoParent
{
    /**
     * return default query for the table.
     * if not in CMS mode return only active navigation trees.
     *
     * @param int $iLanguageId - language used for query
     * @param bool|string $sFilterString - any filter conditions to add to the query
     */
    public static function GetDefaultQuery($iLanguageId, $sFilterString = false): string
    {
        $sDefaultQuery = 'SELECT `pkg_shop_primary_navi`.*
                          FROM `pkg_shop_primary_navi`
                         WHERE [{sFilterConditions}]
                      ORDER BY `pkg_shop_primary_navi`.`position` ASC';
        $sActivePrimaryNavigationQueryRestriction = '';
        if (!TGlobal::IsCMSMode()) {
            $sActivePrimaryNavigationQueryRestriction = TdbPkgShopPrimaryNaviList::GetActivePrimaryNavigationQueryRestriction();
        }
        if (strlen($sActivePrimaryNavigationQueryRestriction) > 0) {
            if (false === $sFilterString) {
                $sFilterString = " $sActivePrimaryNavigationQueryRestriction ";
            } else {
                $sFilterString = ' ( '.$sFilterString.' ) AND ('.$sActivePrimaryNavigationQueryRestriction.')';
            }
        } else {
            if (false === $sFilterString) {
                $sFilterString = ' 1 = 1 ';
            }
        }
        $sDefaultQuery = str_replace('[{sFilterConditions}]', $sFilterString, $sDefaultQuery);

        return $sDefaultQuery;
    }

    /**
     * returns a subquery that can be used to reduce a query set to only active navigation trees.
     *
     * @return string
     */
    public static function GetActivePrimaryNavigationQueryRestriction()
    {
        $sQuery = "`pkg_shop_primary_navi`.`active` = '1' ";

        return $sQuery;
    }
}
