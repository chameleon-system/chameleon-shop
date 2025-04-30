<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;

class TShopPaymentMethodList extends TShopPaymentMethodListAutoParent
{
    /**
     * @var float|null
     */
    protected $dPrice;

    /**
     * return list of shipping types that match the given group, the current basket,
     * the current portal and the current user.
     *
     * @param int $iGroupId
     *
     * @return TdbShopPaymentMethodList
     */
    public static function GetAvailableMethods($iGroupId)
    {
        $query = self::getAvailableMethodsQuery($iGroupId);
        $oList = TdbShopPaymentMethodList::GetList($query);
        $oList->bAllowItemCache = true;
        $oList->RemoveInvalidItems();

        return $oList;
    }

    /**
     * @param string $paymentGroupId
     *
     * @return string
     */
    protected static function getAvailableMethodsQuery($paymentGroupId)
    {
        $query = self::getBaseQuery();
        $activePortal = self::getPortalDomainService()->getActivePortal();
        if (null !== $activePortal) {
            $query .= self::getPortalQueryRestriction($activePortal->id);
            $query .= "\nAND ";
        } else {
            $query .= "\nWHERE ";
        }
        $query .= self::getPaymentGroupQueryRestriction($paymentGroupId);

        return $query;
    }

    /**
     * @return string
     */
    protected static function getBaseQuery()
    {
        return 'SELECT `shop_payment_method`.*
                  FROM `shop_payment_method`
            INNER JOIN `shop_shipping_group_shop_payment_method_mlt` ON `shop_payment_method`.`id` = `shop_shipping_group_shop_payment_method_mlt`.`target_id`';
    }

    /**
     * @param string $portalId
     *
     * @return string
     */
    protected static function getPortalQueryRestriction($portalId)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
        $quotedPortalId = $connection->quote($portalId);

        return "\nLEFT JOIN `shop_payment_method_cms_portal_mlt` ON `shop_payment_method`.`id` = `shop_payment_method_cms_portal_mlt`.`source_id`
            WHERE (`shop_payment_method_cms_portal_mlt`.`target_id` = {$quotedPortalId} OR `shop_payment_method_cms_portal_mlt`.`target_id` IS NULL)";
    }

    /**
     * @param string $paymentGroupId
     *
     * @return string
     */
    protected static function getPaymentGroupQueryRestriction($paymentGroupId)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
        $quotedGroupId = $connection->quote($paymentGroupId);

        return " `shop_shipping_group_shop_payment_method_mlt`.`source_id` = {$quotedGroupId}";
    }

    /**
     * return all public payment methods for a given shipping group.
     *
     * @param int $iGroupId
     *
     * @return TdbShopPaymentMethodList
     */
    public static function GetPublicPaymentMethods($iGroupId)
    {
        $query = self::getAvailableMethodsQuery($iGroupId);

        $oList = TdbShopPaymentMethodList::GetList($query);
        $oList->bAllowItemCache = true;
        $oList->RemoveRestrictedItems();

        return $oList;
    }

    /**
     * @return void
     */
    public function RemoveInvalidItems()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        // since this is a tcmsrecord list, we need to collect all valid ids, and then reload the list with them
        $aValidIds = [];
        $this->GoToStart();
        while ($oItem = $this->Next()) {
            if ($oItem->IsAvailable()) {
                $aValidIds[] = $connection->quote($oItem->id);
            }
        }

        $query = 'SELECT `shop_payment_method`.*
              FROM `shop_payment_method`
             WHERE ';

        if (count($aValidIds) > 0) {
            $query .= ' `shop_payment_method`.`id` IN ('.implode(',', $aValidIds).') ';
        } else {
            $query .= ' 1 = 0 ';
        }

        $query .= ' ORDER BY `shop_payment_method`.`position`';
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
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $aValidIds = [];
        $this->GoToStart();
        while ($oItem = $this->Next()) {
            if ($oItem->IsPublic()) {
                $aValidIds[] = $connection->quote($oItem->id);
            }
        }

        $query = 'SELECT `shop_payment_method`.*
              FROM `shop_payment_method`
             WHERE ';

        if (count($aValidIds) > 0) {
            $query .= ' `shop_payment_method`.`id` IN ('.implode(',', $aValidIds).') ';
        } else {
            $query .= ' 1 = 0 ';
        }

        $query .= ' ORDER BY `shop_payment_method`.`position`';
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
            $iPointer = $this->getItemPointer();
            $this->GoToStart();
            while ($oItem = $this->Next()) {
                $this->dPrice += $oItem->GetPrice();
            }
            $this->setItemPointer($iPointer);
        }

        return $this->dPrice;
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private static function getPortalDomainService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
