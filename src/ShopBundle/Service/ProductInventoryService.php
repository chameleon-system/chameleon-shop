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
use Psr\Log\LoggerInterface;

class ProductInventoryService implements ProductInventoryServiceInterface
{
    public function __construct(
        private readonly Connection $databaseConnection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableStock($shopArticleId)
    {
        /** @var int[]|false $stock */
        try {
            $stock = $this->databaseConnection->fetchOne(
                'SELECT SUM(`amount`) AS total_amount FROM `shop_article_stock` WHERE `shop_article_id` = :id GROUP BY `shop_article_id`',
                ['id' => $shopArticleId]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Unable to getAvailableStock - database error: %s', $e->getMessage()),
                ['productId' => $shopArticleId, 'exception' => $e]
            );

            return 0;
        }

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
        try {
            $affectedRows = $this->databaseConnection->executeStatement(
                $query,
                ['id' => \TTools::GetUUID(), 'amount' => $stock, 'articleId' => $shopArticleId],
                ['amount' => \PDO::PARAM_INT]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Unable to addStock - database error: %s', $e->getMessage()),
                ['productId' => $shopArticleId, 'stock' => $stock, 'exception' => $e]
            );

            return false;
        }

        if (0 === $affectedRows) {
            return false;
        }

        $this->dispatchUpdateStockEvent($shopArticleId, ($preChangeStock + $stock), $preChangeStock);

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
        try {
            $affectedRows = $this->databaseConnection->executeStatement(
                $query,
                ['id' => \TTools::GetUUID(), 'amount' => $stock, 'articleId' => $shopArticleId],
                ['amount' => \PDO::PARAM_INT]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Unable to setStock - database error: %s', $e->getMessage()),
                ['productId' => $shopArticleId, 'stock' => $stock, 'exception' => $e]
            );

            return false;
        }

        if (0 === $affectedRows) {
            return false;
        }

        $this->dispatchUpdateStockEvent($shopArticleId, $this->getAvailableStock($shopArticleId), $preChangeStock);

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
        try {
            $result = $this->databaseConnection->fetchAssociative($query, ['articleId' => $parentArticleId]);
            $amount = (is_array($result) && isset($result['amount'])) ? (int) $result['amount'] : 0;
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Unable to updateVariantParentStock - database error: %s', $e->getMessage()),
                ['parentArticleId' => $parentArticleId, 'exception' => $e]
            );
            $amount = 0;
        }

        return $this->setStock($parentArticleId, $amount);
    }

    private function dispatchUpdateStockEvent(string $shopArticleId, int $newTotalStock, int $oldTotalStock): void
    {
        $this->eventDispatcher->dispatch(
            new UpdateProductStockEvent($shopArticleId, $newTotalStock, $oldTotalStock),
            ShopEvents::UPDATE_PRODUCT_STOCK
        );
    }
}
