<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\DataAccess;

use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopShippingTypeDataAccessInterface;
use Doctrine\DBAL\Connection;
use TShopBasket;

class ShopShippingTypeDataAccess implements ShopShippingTypeDataAccessInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleGroupIds($shippingTypeId)
    {
        return $this->getIdsFromMlt('shop_shipping_type_shop_article_group_mlt', $shippingTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedArticleIds($shippingTypeId)
    {
        return $this->getIdsFromMlt('shop_shipping_type_shop_article_mlt', $shippingTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedCategoryIds($shippingTypeId)
    {
        return $this->getIdsFromMlt('shop_shipping_type_shop_category_mlt', $shippingTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedPortalIds($shippingTypeId)
    {
        return $this->getIdsFromMlt('shop_shipping_type_cms_portal_mlt', $shippingTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedCountryIds($shippingTypeId)
    {
        return $this->getIdsFromMlt('shop_shipping_type_data_country_mlt', $shippingTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserGroupIds($shippingTypeId)
    {
        return $this->getIdsFromMlt('shop_shipping_type_data_extranet_group_mlt', $shippingTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermittedUserIds($shippingTypeId)
    {
        return $this->getIdsFromMlt('shop_shipping_type_data_extranet_user_mlt', $shippingTypeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableShippingTypes($shippingGroupId, $shippingCountryId, TShopBasket $basket)
    {
        $parameters = array(
            'iGroupId' => $shippingGroupId,
            'dNumberOfArticles' => $basket->dTotalNumberOfArticles,
            'dWeight' => $basket->dTotalWeight,
            'dBasketVolume' => $basket->dTotalVolume,
            'dBasketValue' => $basket->dCostArticlesTotalAfterDiscounts,
        );
        if ('' !== $shippingCountryId) {
            $parameters['sActiveShippingCountryId'] = $shippingCountryId;
        }

        $parameters['now'] = date('Y-m-d H:i:s');

        $isActiveSnippet = "(`shop_shipping_type`.`active` = '1' AND (`shop_shipping_type`.`active_from` <= :now AND (`shop_shipping_type`.`active_to` = '0000-00-00 00:00:00' OR `shop_shipping_type`.`active_to` >= :now)))";

        // if we have a shipping address
        $countryRestrictionSnippet = '';
        if (true === empty($shippingCountryId)) {
            // only select items that have NO country restriction
            $countryRestrictionSnippet = 'SELECT `shop_shipping_type`.`id`
                                           FROM `shop_shipping_type`
                                     INNER JOIN `shop_shipping_group_shop_shipping_type_mlt` ON `shop_shipping_type`.`id` = `shop_shipping_group_shop_shipping_type_mlt`.`target_id`
                                      LEFT JOIN `shop_shipping_type_data_country_mlt` ON `shop_shipping_type`.`id` = `shop_shipping_type_data_country_mlt`.`source_id`
                                          WHERE `shop_shipping_group_shop_shipping_type_mlt`.`source_id` = :iGroupId
                                            AND `shop_shipping_type_data_country_mlt`.`target_id` IS NULL
                                             ';
        } else {
            // only select items that have NO country OR my country
            $countryRestrictionSnippet = 'SELECT `shop_shipping_type`.`id`
                                           FROM `shop_shipping_type`
                                     INNER JOIN `shop_shipping_group_shop_shipping_type_mlt` ON `shop_shipping_type`.`id` = `shop_shipping_group_shop_shipping_type_mlt`.`target_id`
                                      LEFT JOIN `shop_shipping_type_data_country_mlt` ON `shop_shipping_type`.`id` = `shop_shipping_type_data_country_mlt`.`source_id`
                                          WHERE `shop_shipping_group_shop_shipping_type_mlt`.`source_id` = :iGroupId
                                            AND (`shop_shipping_type_data_country_mlt`.`target_id` IS NULL
                                                  OR
                                                  `shop_shipping_type_data_country_mlt`.`target_id` = :sActiveShippingCountryId
                                                )
                                         ';
        }

        // now restrict by weight
        $weightSnippet = '(`shop_shipping_type`.`restrict_to_weight_from` <= :dWeight AND (`shop_shipping_type`.`restrict_to_weight_to` = 0 OR `shop_shipping_type`.`restrict_to_weight_to` >= :dWeight))';

        $volumnSnippet = '(`shop_shipping_type`.`restrict_to_volume_from` <= :dBasketVolume AND (`shop_shipping_type`.`restrict_to_volume_to` = 0 OR `shop_shipping_type`.`restrict_to_volume_to` >= :dBasketVolume))';
        $basketValueSnippet = '(`shop_shipping_type`.`restrict_to_value_from` <= :dBasketValue AND (`shop_shipping_type`.`restrict_to_value_to` = 0 OR `shop_shipping_type`.`restrict_to_value_to` >= :dBasketValue))';

        $basketArticlesSnippet = '(`shop_shipping_type`.`restrict_to_articles_from` <= :dNumberOfArticles AND (`shop_shipping_type`.`restrict_to_articles_to` = 0 OR `shop_shipping_type`.`restrict_to_articles_to` >= :dNumberOfArticles))';

        $query = "SELECT `shop_shipping_type`.*
                  FROM `shop_shipping_type`
            INNER JOIN `shop_shipping_group_shop_shipping_type_mlt` ON `shop_shipping_type`.`id` = `shop_shipping_group_shop_shipping_type_mlt`.`target_id`
                 WHERE `shop_shipping_group_shop_shipping_type_mlt`.`source_id` = :iGroupId
                   AND {$isActiveSnippet}
                   AND (`shop_shipping_type`.`value_based_on_entire_basket` = '0'
                         OR (
                           {$basketValueSnippet}
                         )
                       )
                   AND (`shop_shipping_type`.`apply_to_all_products` = '0'
                         OR (
                           {$weightSnippet}
                             AND
                           {$volumnSnippet}
                             AND
                           {$basketArticlesSnippet}
                         )
                       )
                   AND `shop_shipping_type`.`id` IN ({$countryRestrictionSnippet})
              ORDER BY `shop_shipping_type`.`position`
               ";

        return $this->connection->fetchAllAssociative($query, $parameters);
    }

    /**
     * @param string $mltName
     * @param string $shippingTypeId
     *
     * @return array
     */
    private function getIdsFromMlt($mltName, $shippingTypeId)
    {
        $query = sprintf('SELECT %1$s.`target_id`
                    FROM %1$s
                   WHERE %1$s.`source_id` = :shippingTypeId
                    ', $this->connection->quoteIdentifier($mltName));
        $idRows = $this->connection->fetchAllAssociative($query, array('shippingTypeId' => $shippingTypeId));

        return array_map(
            function (array $row) {
                return $row['target_id'];
            },
            $idRows
        );
    }
}
