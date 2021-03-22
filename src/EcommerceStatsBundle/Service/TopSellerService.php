<?php

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use ChameleonSystem\EcommerceStatsBundle\DataModel\ShopOrderItemDataModel;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\TopSellerServiceInterface;
use Doctrine\DBAL\Connection;

class TopSellerService implements TopSellerServiceInterface
{

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ShopOrderItemDataModel[]
     */
    public function getTopsellers(
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        string $portalId,
        int $limit = 50
    ): array {
        $query = '
            SELECT 
                SUM(`shop_order_item`.`order_amount`) AS totalordered,
                SUM(`shop_order_item`.`order_price_after_discounts`) AS totalorderedvalue,
                `shop_category`.`url_path` AS categorypath, 
                `shop_order_item`.*
            FROM `shop_order_item`
                LEFT JOIN `shop_order`
                    ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
                LEFT JOIN `shop_article_shop_category_mlt` 
                    ON `shop_order_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
                LEFT JOIN `shop_category` 
                    ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
               ';
        $baseConditionList = [];
        if (null !== $startDate) {
            $baseConditionList[] = '`shop_order`.`datecreated` >= '.$this->connection->quote($startDate->format('Y-m-d'));
        }
        if (null !== $endDate) {
            $baseConditionList[] = '`shop_order`.`datecreated` <= '.$this->connection->quote($endDate->format('Y-m-d').' 23:59:59');
        }
        if ('' !== $portalId) {
            $baseConditionList[] = '`shop_order`.`cms_portal_id` = '.$this->connection->quote($portalId);
        }

        if (count($baseConditionList) > 0) {
            $query .= ' WHERE ('.implode(') AND (', $baseConditionList).')';
        }

        $query .= ' GROUP BY `shop_category`.`id`, `shop_order_item`.`shop_article_id`';
        $query .= ' ORDER BY totalordered DESC ';
        $query .= ' LIMIT 0,'.$limit;

        $items = [];
        $list = \TdbShopOrderItemList::GetList($query);
        while ($item = $list->Next()) {
            $items[] = $this->tdbToDataModel($item);
        }

        return $items;
    }

    private function tdbToDataModel(\TdbShopOrderItem $tdb): ShopOrderItemDataModel
    {
        return new ShopOrderItemDataModel(
            $tdb->fieldArticlenumber,
            $tdb->fieldName,
            (int) $tdb->sqlData['totalordered'],
            (float) $tdb->sqlData['totalorderedvalue'],
            $tdb->sqlData['categorypath']
        );
    }

}
