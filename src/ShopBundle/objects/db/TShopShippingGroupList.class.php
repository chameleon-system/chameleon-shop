<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopShippingGroupList extends TShopShippingGroupListAutoParent
{
    /**
     * return all shipping groups available to the current user with the current basket.
     *
     * @return TdbShopShippingGroupList
     */
    public static function &GetAvailableShippingGroups()
    {
        $oList = &TdbShopShippingGroupList::GetList();
        $oList->bAllowItemCache = true;
        $oList->RemoveInvalidItems();
        $oList->GoToStart();

        return $oList;
    }

    /**
     * search for a shipping group that supports the given payment type
     * returns false if nothing is found.
     *
     * @param string $sPaymentInternalName
     *
     * @return bool
     */
    public static function &GetShippingGroupsThatAllowPaymentWith($sPaymentInternalName)
    {
        $oList = &TdbShopShippingGroupList::GetAvailableShippingGroups();
        $bFound = false;
        $oShippingGroup = null;
        while (!$bFound && ($oShippingGroup = &$oList->Next())) {
            $oPaymentMethods = &$oShippingGroup->GetValidPaymentMethods();
            $oPayPal = $oPaymentMethods->FindItemWithProperty('fieldNameInternal', $sPaymentInternalName);
            if ($oPayPal) {
                $bFound = true;
            }
        }

        if (!$bFound) {
            $oShippingGroup = false;
        }

        return $oShippingGroup;
    }

    /**
     * return all shipping groups that have no user restriction.
     *
     * @return TdbShopShippingGroupList
     */
    public static function &GetPublicShippingGroups()
    {
        $oList = &TdbShopShippingGroupList::GetList();
        $oList->bAllowItemCache = true;
        $oList->RemoveRestrictedItems();

        return $oList;
    }

    /**
     * remove list items that are not permited for the current user or basket.
     */
    public function RemoveInvalidItems()
    {
        // since this is a tcmsrecord list, we need to collect all valid ids, and the reload the list with them
        $oBasket = TShopBasket::GetInstance();
        $aValidIds = array();
        $this->GoToStart();
        $aValidShippingGroupItems = array();
        while ($oItem = &$this->Next()) {
            $oBasket->ResetAllShippingMarkers(); // we need to reset the shipping marker on every group call - since we want
            // to consider every single item in the basket

            if ($oItem->isAvailableIgnoreGroupRestriction()) {
                $aValidIds[] = MySqlLegacySupport::getInstance()->real_escape_string($oItem->id);
                $aValidShippingGroupItems['x'.$oItem->id] = $oItem;
            }
        }
        $oBasket->ResetAllShippingMarkers(); // once we are done, we want to clear the marker again

        // remove any shipping groups that are not allowed to be shown when other shipping groups are available
        $aRealValidIds = array();
        foreach ($aValidIds as $sShippingGroupId) {
            /** @var $oItem TdbShopShippingGroup */
            $oItem = $aValidShippingGroupItems['x'.$sShippingGroupId];
            if ($oItem->allowedForShippingGroupList($aValidIds)) {
                $aRealValidIds[] = $sShippingGroupId;
            }
        }
        $aValidIds = $aRealValidIds;
        unset($aRealValidIds);

        $query = 'SELECT `shop_shipping_group`.*
                  FROM `shop_shipping_group`
                 WHERE ';
        if (count($aValidIds) > 0) {
            $query .= " `shop_shipping_group`.`id` IN ('".implode("','", $aValidIds)."') ";
        } else {
            $query .= ' 1 = 0 ';
        }
        $query .= ' ORDER BY `shop_shipping_group`.`position`';
        $this->Load($query);
    }

    /**
     * remove list items that are restricted to some user or user group.
     */
    public function RemoveRestrictedItems()
    {
        // since this is a tcmsrecord list, we need to collect all valid ids, and the reload the list with them
        $aValidIds = array();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            if ($oItem->IsPublic()) {
                $aValidIds[] = MySqlLegacySupport::getInstance()->real_escape_string($oItem->id);
            }
        }

        $query = 'SELECT `shop_shipping_group`.*
                  FROM `shop_shipping_group`
                 WHERE ';
        if (count($aValidIds) > 0) {
            $query .= " `shop_shipping_group`.`id` IN ('".implode("','", $aValidIds)."') ";
        } else {
            $query .= ' 1 = 0 ';
        }
        $query .= ' ORDER BY `shop_shipping_group`.`position`';
        $this->Load($query);
    }
}
