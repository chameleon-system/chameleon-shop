<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\ProductVariant;

interface ProductVariantNameGeneratorInterface
{
    const VARIANT_NAME_TYPE_DEFAULT = 0;
    const VARIANT_NAME_TYPE_URL = 1;

    /**
     * Returns a variant name in the same language as the passed $product.
     *
     * @param \TdbShopArticle $product
     * @param int             $variantNameType
     *
     * @return string|null a variant name, or null if the product is not a variant
     */
    public function generateName(\TdbShopArticle $product, $variantNameType = self::VARIANT_NAME_TYPE_DEFAULT);

    /**
     * Returns a list of variant names, one for every language that is registered for field-based translation in cms_config.
     *
     * @param \TdbShopArticle $product
     * @param int             $variantNameType
     *
     * @return array<string, string>|null a list of variant names (key = language, value = variant name), or null if the product is not a variant
     */
    public function generateNamesForAllLanguages(\TdbShopArticle $product, $variantNameType = self::VARIANT_NAME_TYPE_DEFAULT);
}
