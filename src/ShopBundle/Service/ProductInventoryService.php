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

use ChameleonSystem\ShopBundle\Event\UpdateProductStockEvent;
use ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface;
use ChameleonSystem\ShopBundle\ShopEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductInventoryService implements ProductInventoryServiceInterface
{
    public function __construct(
        private readonly Connection $databaseConnection,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableStock($shopArticleId)
    {
        /** @var int[]|false $stock */
        $stock = $this->databaseConnection->fetchOne(
            'SELECT SUM(`amount`) AS total_amount FROM `shop_article_stock` WHERE `shop_article_id` = :id GROUP BY `shop_article_id`',
            array('id' => $shopArticleId)
        );
        if (false === $stock) {
            return 0;
        }

        return (int) $stock;
    }

    /**
     * {@inheritdoc}
     */
    public function addStock($shopArticleId, $stock): bool
    {
        $preChangeStock = $this->getAvailableStock($shopArticleId);
        $query = 'INSERT INTO `shop_article_stock`
                          SET `id` = :id,
                              `amount` = :amount,
                              `shop_article_id` = :articleId
      ON DUPLICATE KEY UPDATE `amount` = `amount` + :amount
                              ';
        $affectedRows = $this->databaseConnection->executeStatement(
            $query,
            array('id' => \TTools::GetUUID(), 'amount' => $stock, 'articleId' => $shopArticleId),
            array('amount' => \PDO::PARAM_INT)
        );
        if (0 === $affectedRows) {
            return false;
        }

        $this->eventDispatcher->dispatch(
            new UpdateProductStockEvent($shopArticleId, $stock, $preChangeStock),
            ShopEvents::UPDATE_PRODUCT_STOCK
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setStock($shopArticleId, $stock): bool
    {
        $preChangeStock = $this->getAvailableStock($shopArticleId);
        $query = 'INSERT INTO `shop_article_stock`
                          SET `id` = :id,
                              `amount` = :amount,
                              `shop_article_id` = :articleId
      ON DUPLICATE KEY UPDATE `amount` = :amount
                              ';
        $affectedRows = $this->databaseConnection->executeStatement(
            $query,
            array('id' => \TTools::GetUUID(), 'amount' => $stock, 'articleId' => $shopArticleId),
            array('amount' => \PDO::PARAM_INT)
        );
        if (0 === $affectedRows) {
            return false;
        }

        $this->eventDispatcher->dispatch(
            new UpdateProductStockEvent($shopArticleId, $stock, $preChangeStock),
            ShopEvents::UPDATE_PRODUCT_STOCK
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateVariantParentStock($parentArticleId): bool
    {
        $query = 'SELECT
                         SUM(`shop_article_stock`.amount) AS amount
                    FROM `shop_article_stock`
              INNER JOIN `shop_article` ON `shop_article_stock`.`shop_article_id` = `shop_article`.`id`
                   WHERE `shop_article`.`variant_parent_id` = :articleId
        ';
        $result = $this->databaseConnection->fetchNumeric($query, array('articleId' => $parentArticleId));
        $amount = (is_array($result) && isset($result[0])) ? (int) $result[0] : 0;

        return $this->setStock($parentArticleId, $amount);
    }
}
