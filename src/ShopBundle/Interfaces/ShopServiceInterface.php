<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Interfaces;

interface ShopServiceInterface
{
    /**
     * @return \TdbShop
     *
     * @throws \ErrorException
     */
    public function getActiveShop();

    /**
     * @param bool $useRedirect
     *
     * @return string
     *
     * @throws \ErrorException
     */
    public function getBasketLink($useRedirect = true);

    /**
     * @param bool $useRedirect
     *
     * @return string
     *
     * @throws \ErrorException
     */
    public function getCheckoutLink($useRedirect = true);

    /**
     * @param string $cmsPortalId
     *
     * @return \TdbShop
     *
     * @throws \ErrorException
     */
    public function getShopForPortalId($cmsPortalId);

    /**
     * @return string
     *
     * @throws \ErrorException
     */
    public function getId();

    /**
     * @return array
     */
    public function getConfiguration();

    /**
     * @return \TdbShopArticle|null
     */
    public function getActiveProduct();

    /**
     * @return \TdbShopCategory|null
     */
    public function getActiveCategory();

    public function getActiveRootCategory(): ?\TdbShopCategory;

    /**
     * @return \TShopBasket|null
     */
    public function getActiveBasket();

    /**
     * @return void
     */
    public function resetBasket();

    /**
     * @return array - key = id, value = name
     */
    public function getAllShops(): array;

    public function getProductCountForShop(
        string $shopId,
        bool $onlyActive = false,
        bool $onlyMainProducts = false,
        bool $onlyVariants = false,
        bool $isVirtualProduct = false,
        bool $isNew = false,
        bool $isSearchable = false
    ): int;

    public function getCategoryCountForShop(
        string $shopId,
    ): int;
}
