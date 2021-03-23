<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStatsBundle\Service;

use ChameleonSystem\EcommerceStatsBundle\DataModel\ShopOrderItemDataModel;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\TopSellerServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

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

        $query = sprintf('
            SELECT 
                SUM(`shop_order_item`.`order_amount`) AS totalordered,
                SUM(`shop_order_item`.`order_price_after_discounts`) AS totalorderedvalue,
                `shop_category`.`url_path` AS categorypath, 
                `shop_order_item`.`articlenumber`,
                `shop_order_item`.`name`
            FROM `shop_order_item`
                LEFT JOIN `shop_order`
                    ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
                LEFT JOIN `shop_article_shop_category_mlt` 
                    ON `shop_order_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
                LEFT JOIN `shop_category` 
                    ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
            %1$s
            GROUP BY `shop_category`.`id`, `shop_order_item`.`shop_article_id`
            ORDER BY `totalordered` DESC
            LIMIT 0,%2$d
               ',
            $this->getWhereQueryPart($startDate, $endDate, $portalId),
            $limit
        );

        $items = [];
        foreach ($this->executeQuery($query) as $row) {
            $items[] = new ShopOrderItemDataModel(
                (string) $row['articlenumber'],
                (string) $row['name'],
                (int) $row['totalordered'],
                (float) $row['totalorderedvalue'],
                (string) $row['categorypath']
            );
        }

        return $items;
    }

    /**
     * Returns the `WHERE` query part including the `WHERE` keyword if
     * conditions apply or and empty string if none apply.
     *
     * The returned string is safe to use in a query.
     */
    private function getWhereQueryPart(?\DateTime $startDate, ?\DateTime $endDate, string $portalId): string
    {
        $conditions = [];
        if (null !== $startDate) {
            $conditions[] = '`shop_order`.`datecreated` >= '.$this->connection->quote($startDate->format('Y-m-d H:i:s'));
        }
        if (null !== $endDate) {
            $conditions[] = '`shop_order`.`datecreated` <= '.$this->connection->quote($endDate->format('Y-m-d H:i:s'));
        }
        if ('' !== $portalId) {
            $conditions[] = '`shop_order`.`cms_portal_id` = '.$this->connection->quote($portalId);
        }

        if (0 === count($conditions)) {
            return '';
        }

        return 'WHERE ('.implode(') AND (', $conditions).')';
    }

    private function executeQuery(string $query): \Generator
    {
        $results = $this->connection->executeQuery($query);
        while ($row = $results->fetch(FetchMode::ASSOCIATIVE)) {
            yield $row;
        }
    }
}
