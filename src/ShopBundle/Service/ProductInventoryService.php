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

use ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface;
use Doctrine\DBAL\Connection;

class ProductInventoryService implements ProductInventoryServiceInterface
{
    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * {@inheritdoc}
     */
    public function getAvailableStock($shopArticleId)
    {
        /** @var int[]|false $stock */
        $stock = $this->databaseConnection->fetchNumeric(
            'SELECT `amount` FROM `shop_article_stock` WHERE `shop_article_id` = :id',
            array('id' => $shopArticleId)
        );
        if (is_array($stock) && isset($stock[0])) {
            return (int) $stock[0];
        }

        /** @var false $stock */

        return $stock;
    }

    /**
     * {@inheritdoc}
     */
    public function addStock($shopArticleId, $stock)
    {
        $query = 'INSERT INTO `shop_article_stock`
                          SET `id` = :id,
                              `amount` = :amount,
                              `shop_article_id` = :articleId
      ON DUPLICATE KEY UPDATE `amount` = `amount` + :amount
                              ';
        $this->databaseConnection->executeQuery(
            $query,
            array('id' => \TTools::GetUUID(), 'amount' => $stock, 'articleId' => $shopArticleId),
            array('amount' => \PDO::PARAM_INT)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setStock($shopArticleId, $stock)
    {
        $query = 'INSERT INTO `shop_article_stock`
                          SET `id` = :id,
                              `amount` = :amount,
                              `shop_article_id` = :articleId
      ON DUPLICATE KEY UPDATE `amount` = :amount
                              ';
        $this->databaseConnection->executeQuery(
            $query,
            array('id' => \TTools::GetUUID(), 'amount' => $stock, 'articleId' => $shopArticleId),
            array('amount' => \PDO::PARAM_INT)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateVariantParentStock($parentArticleId)
    {
        $query = 'SELECT
                         SUM(`shop_article_stock`.amount) AS amount
                    FROM `shop_article_stock`
              INNER JOIN `shop_article` ON `shop_article_stock`.`shop_article_id` = `shop_article`.`id`
                   WHERE `shop_article`.`variant_parent_id` = :articleId
        ';
        $result = $this->databaseConnection->fetchNumeric($query, array('articleId' => $parentArticleId));
        $amount = (is_array($result) && isset($result[0])) ? (int) $result[0] : 0;

        $updateData = array(
            'amount' => $amount,
            'articleId' => $parentArticleId,
        );
        $query = 'INSERT INTO `shop_article_stock`
                          SET `amount` = :amount,
                              `shop_article_id` = :articleId,
                              `id` = :id
      ON DUPLICATE KEY UPDATE `amount` = :amount
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
}
