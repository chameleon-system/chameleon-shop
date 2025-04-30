<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopShippingTypeDataAccessInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Doctrine\DBAL\Connection;

class TShopShippingTypeList extends TShopShippingTypeListAutoParent
{
    /**
     * @var float|null
     */
    protected $dPrice = null;

    /**
     * @return ShopShippingTypeDataAccessInterface
     */
    private static function getShippingTypeDataAccess()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_shipping_type_data_access');
    }

    /**
     * @return ShopServiceInterface
     */
    private static function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private static function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    /**
     * return list of shipping types that match the given group, the current basket,
     * and the current user.
     *
     * @param int $iGroupId
     *
     * @return TdbShopShippingTypeList
     */
    public static function GetAvailableTypes($iGroupId)
    {
        $shippingTypeDataAccess = self::getShippingTypeDataAccess();
        $shopService = self::getShopService();

        $oUser = self::getExtranetUserProvider()->getActiveUser();
        $oShippingAddress = $oUser->GetShippingAddress();
        $sActiveShippingCountryId = '';
        if ($oShippingAddress) {
            $sActiveShippingCountryId = $oShippingAddress->fieldDataCountryId;
        }
        if (true === empty($sActiveShippingCountryId)) {
            // use default country
            $oShop = $shopService->getActiveShop();
            if ($oShop) {
                $sActiveShippingCountryId = $oShop->fieldDataCountryId;
            }
        }
        $rows = $shippingTypeDataAccess->getAvailableShippingTypes($iGroupId, $sActiveShippingCountryId, $shopService->getActiveBasket());

        $idList = array();
        $oBasket = TShopBasket::GetInstance();
        $oBasket->ResetAllShippingMarkers(); // once we are done, we want to clear the marker again
        foreach ($rows as $row) {
            $item = TdbShopShippingType::GetNewInstance($row);
            if ($item->IsAvailable()) {
                $idList[] = $item->id;
            }
        }

        $query = 'SELECT * FROM `shop_shipping_type` WHERE `id` IN (:idList) ORDER BY `position`';
        $oList = new TdbShopShippingTypeList();
        $oList->Load($query, array('idList' => $idList), array('idList' => Connection::PARAM_STR_ARRAY));
        $oList->bAllowItemCache = true;

        $oList->RemoveInvalidItems();

        return $oList;
    }

    /**
     * return all public shipping types for a given shipping group.
     *
     * @param int $iGroupId
     *
     * @return TdbShopShippingTypeList
     */
    public static function GetPublicShippingTypes($iGroupId)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedGroupId = $connection->quote($iGroupId);
        $query = "
        SELECT `shop_shipping_type`.*
          FROM `shop_shipping_type`
    INNER JOIN `shop_shipping_group_shop_shipping_type_mlt`
            ON `shop_shipping_type`.`id` = `shop_shipping_group_shop_shipping_type_mlt`.`target_id`
         WHERE `shop_shipping_group_shop_shipping_type_mlt`.`source_id` = {$quotedGroupId}
    ";

        $oList = TdbShopShippingTypeList::GetList($query);
        $oList->RemoveRestrictedItems();

        return $oList;
    }

    /**
     * @deprecated since 6.1.4 - method was only called by GetAvailableTypes. this is no longer the case.
     *
     * @return void
     */
    public function RemoveInvalidItems()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        // since this is a tcmsrecord list, we need to collect all valid ids, and the reload the list with them
        $allIds = [];
        $validIds = [];
        $this->GoToStart();

        while ($oItem = $this->Next()) {
            $allIds[] = $oItem->id;
            if ($oItem->IsAvailable()) {
                $validIds[] = $connection->quote($oItem->id);
            }
        }

        if (count($allIds) === count($validIds)) {
            $this->GoToStart();
            return;
        }

        $query = 'SELECT `shop_shipping_type`.*
              FROM `shop_shipping_type`
             WHERE ';

        if (count($validIds) > 0) {
            $query .= ' `shop_shipping_type`.`id` IN (' . implode(',', $validIds) . ') ';
        } else {
            $query .= ' 1 = 0 ';
        }

        $query .= ' ORDER BY `shop_shipping_type`.`position`';
        $this->Load($query);
    }

    /**
     * remove list items that are restricted to some user or user group.
     *
     * @return void
     */
    public function RemoveRestrictedItems()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        // since this is a tcmsrecord list, we need to collect all valid ids, and the reload the list with them
        $validIds = [];
        $this->GoToStart();
        while ($oItem = $this->Next()) {
            if ($oItem->IsPublic()) {
                $validIds[] = $connection->quote($oItem->id);
            }
        }

        $query = 'SELECT `shop_shipping_type`.*
              FROM `shop_shipping_type`
             WHERE ';

        if (count($validIds) > 0) {
            $query .= ' `shop_shipping_type`.`id` IN (' . implode(',', $validIds) . ') ';
        } else {
            $query .= ' 1 = 0 ';
        }

        $query .= ' ORDER BY `shop_shipping_type`.`position`';
        $this->Load($query);
    }

    /**
     * return the total costs of all shipping types in the list.
     *
     * @return float
     */
    public function GetTotalPrice()
    {
        if (is_null($this->dPrice)) {
            $this->dPrice = 0;

            // we first need to check if there is one shipping type that is supposed to affect the
            // complete basket. if that is the case, we need to make sure we move that to the front
            // and therby ONLY use it (it will automatically affect ALL items)
            $oItemForCompleteBasket = $this->FindItemWithProperty('fieldApplyToAllProducts', true);
            if ($oItemForCompleteBasket) {
                $this->dPrice = $oItemForCompleteBasket->GetPrice();
            } else {
                $iPointer = $this->getItemPointer();
                $this->GoToStart();
                while ($oItem = $this->Next()) {
                    $this->dPrice += $oItem->GetPrice();
                    if (true === $oItem->endShippingTypeChain()) {
                        break;
                    }
                }
                $this->setItemPointer($iPointer);
            }
        }

        return $this->dPrice;
    }
}
