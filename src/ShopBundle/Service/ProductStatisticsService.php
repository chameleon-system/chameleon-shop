<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Service;

use ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsInterface;
use ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsServiceInterface;
use ChameleonSystem\ShopBundle\ProductStatistics\ProductStatistics;
use Doctrine\DBAL\Connection;

class ProductStatisticsService implements ProductStatisticsServiceInterface
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @param string $articleId
     *
     * @return ProductStatisticsInterface
     */
    public function getStats($articleId)
    {
        $query = 'SELECT * FROM `shop_article_stats` WHERE `shop_article_id` = :articleId';
        $data = $this->databaseConnection->fetchAssoc($query, array('articleId' => $articleId));
        if (is_array($data)) {
            $stats = $this->createStatsObject($data);
        } else {
            $stats = new ProductStatistics();
        }

        return $stats;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return ProductStatistics
     */
    private function createStatsObject($row)
    {
        $stats = new ProductStatistics();
        $stats->setSales($row['stats_sales']);
        $stats->setDetailViews($row['stats_detail_views']);
        $stats->setReviewAverage($row['stats_review_average']);
        $stats->setReviews($row['stats_review_count']);

        return $stats;
    }

    /**
     * @param string $articleId
     * @param int $type
     * @param float $amount
     * @psalm-param self::TYPE_* $type
     * @return void
     */
    public function add($articleId, $type, $amount)
    {
        $field = $this->getTargetField($type);
        $query = "INSERT INTO `shop_article_stats`
                          SET `id` = :id,
                              `{$field}` = :amount,
                              `shop_article_id` = :articleId
      ON DUPLICATE KEY UPDATE `{$field}` = `{$field}` + :amount
                              ";
        $this->databaseConnection->executeQuery(
            $query,
            array('id' => \TTools::GetUUID(), 'amount' => $amount, 'articleId' => $articleId),
            array('amount' => \PDO::PARAM_INT)
        );
    }

    /**
     * @param string $articleId
     * @param int $type
     * @param float $amount
     * @psalm-param self::TYPE_* $type
     * @return void
     */
    public function set($articleId, $type, $amount)
    {
        $field = $this->getTargetField($type);
        $query = "INSERT INTO `shop_article_stats`
                          SET `id` = :id,
                              `{$field}` = :amount,
                              `shop_article_id` = :articleId
      ON DUPLICATE KEY UPDATE `{$field}` = :amount
                              ";
        $this->databaseConnection->executeQuery(
            $query,
            array('id' => \TTools::GetUUID(), 'amount' => $amount, 'articleId' => $articleId),
            array('amount' => \PDO::PARAM_INT)
        );
    }

    /**
     * @param string $parentArticleId
     * @return void
     */
    public function updateAllBasedOnVariants($parentArticleId)
    {
        $query = 'SELECT
                         SUM(`shop_article_stats`.stats_sales) AS stats_sales,
                         SUM(`shop_article_stats`.`stats_detail_views`) AS stats_detail_views,
                         (SUM(`shop_article_stats`.`stats_review_average`)/COUNT(`shop_article`.`id`)) AS stats_review_average,
                         SUM(`shop_article_stats`.`stats_review_count`) AS stats_review_count
                    FROM `shop_article_stats`
              INNER JOIN `shop_article` ON `shop_article_stats`.`shop_article_id` = `shop_article`.`id`
                   WHERE `shop_article`.`variant_parent_id` = :articleId
        ';
        $result = $this->databaseConnection->fetchAssoc($query, array('articleId' => $parentArticleId));
        if (is_array($result)) {
            $stats = $this->createStatsObject($result);
        } else {
            $stats = new ProductStatistics();
        }

        $updateData = array(
            'stats_sales' => $stats->getSales(),
            'stats_detail_views' => $stats->getDetailViews(),
            'articleId' => $parentArticleId,
        );
        $query = 'INSERT INTO `shop_article_stats`
                          SET `stats_sales` = :stats_sales,
                              `stats_detail_views` = :stats_detail_views,
                              `shop_article_id` = :articleId,
                              `id` = :id
      ON DUPLICATE KEY UPDATE `stats_sales` = :stats_sales,
                              `stats_detail_views` = :stats_detail_views
                              ';
        $updateData['id'] = \TTools::GetUUID();
        $this->databaseConnection->executeQuery(
            $query,
            $updateData,
            array('amount' => \PDO::PARAM_INT)
        );
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->databaseConnection = $connection;
    }

    /**
     * @return null|string
     *
     * @param int $type
     */
    private function getTargetField($type)
    {
        switch ($type) {
            case self::TYPE_SALES:
                return 'stats_sales';
                break;
            case self::TYPE_DETAIL_VIEWS:
                return 'stats_detail_views';
                break;
            case self::TYPE_REVIEW_AVERAGE:
                return 'stats_review_average';
                break;
            case self::TYPE_REVIEW_COUNT:
                return 'stats_review_count';
                break;
        }

        return null;
    }
}
