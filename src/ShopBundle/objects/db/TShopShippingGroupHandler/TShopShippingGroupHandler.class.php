<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopShippingGroupHandler extends TAdbShopShippingGroupHandler
{
    /**
     * return an instance of the correct class type for the filter identified by $id.
     *
     * @param int $id
     *
     * @return TdbShopShippingGroupHandler|null
     */
    public static function GetInstance($id)
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $oInstance = null;
        $quotedId = $connection->quote($id);

        $query = "SELECT * FROM `shop_shipping_group_handler` WHERE `id` = {$quotedId}";
        $row = $connection->fetchAssociative($query);

        if ($row) {
            $sClassName = $row['class'];
            $oInstance = new $sClassName();
            /** @var $oInstance TdbShopShippingGroupHandler */
            $oInstance->LoadFromRow($row);
        }

        return $oInstance;
    }
}
