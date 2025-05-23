<?php

namespace ChameleonSystem\ShopBundle\EventListener;

use ChameleonSystem\ShopBundle\Event\UpdateProductStockEvent;
use ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface;
use Doctrine\DBAL\Connection;

class UpdateProductStockListener
{
    public function __construct(
        private readonly Connection $databaseConnection,
        private readonly ProductInventoryServiceInterface $productInventoryService
    ) {
    }

    public function enableDisableProductBasedOnStock(UpdateProductStockEvent $event): void
    {
        $product = \TdbShopArticle::GetNewInstance($event->getProductId());
        $bActive = $product->CheckActivateOrDeactivate($event->getNewStock());
        $product->setIsActive(1 === $bActive);
    }

    public function updateBundleOwnerStock(UpdateProductStockEvent $event): void
    {
        // check if the article is part of a bundle... if it is, make sure the bundle article does not exceed the total number of single items
        $query = 'SELECT `shop_article`.`id`,
                     shop_bundle_article.amount AS ItemsPerBundle,
                     (shop_bundle_article.amount * shop_article_stock.amount) AS required_stock
                FROM shop_article
          INNER JOIN shop_bundle_article ON shop_article.id = shop_bundle_article.shop_article_id
           LEFT JOIN shop_article_stock ON shop_article.id = shop_article_stock.shop_article_id
               WHERE shop_bundle_article.bundle_article_id = :articleId
                 AND (shop_bundle_article.amount * shop_article_stock.amount) > :newStock
               ';
        $aBundleChangeList = $this->databaseConnection->fetchAllAssociative(
            $query,
            ['articleId' => $event->getProductId(), 'newStock' => $event->getNewStock()],
            ['articleId' => \PDO::PARAM_STR, 'newStock' => \PDO::PARAM_INT]
        );
        foreach ($aBundleChangeList as $aBundleChange) {
            $iAllowedStock = floor($event->getNewStock() / $aBundleChange['ItemsPerBundle']);
            $this->productInventoryService->setStock($aBundleChange['id'], $iAllowedStock);
        }
    }
}
