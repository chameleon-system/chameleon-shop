<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSCronJob_CleanWishlist extends TCMSCronJob
{
    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        $Select = 'SELECT * FROM `pkg_shop_wishlist_article` WHERE 1=1';
        $res = MySqlLegacySupport::getInstance()->query($Select);
        while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($res)) {
            if (empty($aRow['shop_article_id'])) {
                $sDelete = "DELETE FROM `pkg_shop_wishlist_article` WHERE `pkg_shop_wishlist_article`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aRow['id'])."'";
                MySqlLegacySupport::getInstance()->query($sDelete);
            } else {
                $Select = "SELECT * FROM `shop_article` WHERE `shop_article`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aRow['shop_article_id'])."'";
                $ArticleRes = MySqlLegacySupport::getInstance()->query($Select);
                if (MySqlLegacySupport::getInstance()->num_rows($ArticleRes) < 1) {
                    $sDelete = "DELETE FROM `pkg_shop_wishlist_article` WHERE `pkg_shop_wishlist_article`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aRow['id'])."'";
                    MySqlLegacySupport::getInstance()->query($sDelete);
                }
            }
        }
    }
}
