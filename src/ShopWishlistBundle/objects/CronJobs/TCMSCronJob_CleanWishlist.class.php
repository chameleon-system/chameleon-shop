<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSCronJob_CleanWishlist extends TdbCmsCronjobs
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $Select = 'SELECT * FROM `pkg_shop_wishlist_article` WHERE 1=1';
        $res = $connection->executeQuery($Select);
        while ($aRow = $res->fetchAssociative()) {
            if (empty($aRow['shop_article_id'])) {
                $quotedId = $connection->quote($aRow['id']);
                $sDelete = "DELETE FROM `pkg_shop_wishlist_article` WHERE `pkg_shop_wishlist_article`.`id` = {$quotedId}";
                $connection->executeStatement($sDelete);
            } else {
                $quotedArticleId = $connection->quote($aRow['shop_article_id']);
                $SelectArticle = "SELECT * FROM `shop_article` WHERE `shop_article`.`id` = {$quotedArticleId}";
                $ArticleRes = $connection->executeQuery($SelectArticle);
                if (0 === $ArticleRes->rowCount()) {
                    $quotedId = $connection->quote($aRow['id']);
                    $sDelete = "DELETE FROM `pkg_shop_wishlist_article` WHERE `pkg_shop_wishlist_article`.`id` = {$quotedId}";
                    $connection->executeStatement($sDelete);
                }
            }
        }
    }
}
