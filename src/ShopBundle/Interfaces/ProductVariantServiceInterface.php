<?php

namespace ChameleonSystem\ShopBundle\Interfaces;

interface ProductVariantServiceInterface
{
    /**
     * @param \TdbShopArticle $shopArticle
     * @param array           $typeSelection - key => value pairs identifying a specific variant type selection (shop_variant_type => shop_variant_type_value)
     * @return \TdbShopArticle
     */
    public function getProductBasedOnSelection(\TdbShopArticle $shopArticle, array $typeSelection): \TdbShopArticle;
}
