<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Interfaces\ShopRouteArticleFactoryInterface;

class ShopRouteArticleFactory implements ShopRouteArticleFactoryInterface
{
    /**
     * @var string
     */
    private $sqlTableFieldName;

    public function __construct()
    {
        if (defined('PKG_SHOP_PRODUCT_URL_KEY_FIELD')) {
            /**
             * should inject the constant at some point - since this whould change the interface we can not do so in a patch version.
             */
            $this->sqlTableFieldName = PKG_SHOP_PRODUCT_URL_KEY_FIELD;
        } else {
            $this->sqlTableFieldName = 'cmsident';
        }
    }

    /**
     * @param string $identificationToken
     *
     * @return \TdbShopArticle
     */
    public function createArticleFromIdentificationToken($identificationToken)
    {
        $article = TdbShopArticle::GetNewInstance();
        if (false === $article->LoadFromField($this->sqlTableFieldName, $identificationToken)) {
            return null;
        }

        return $article;
    }
}
