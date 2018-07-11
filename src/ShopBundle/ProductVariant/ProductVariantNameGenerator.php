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

class ProductVariantNameGenerator implements ProductVariantNameGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateName(\TdbShopArticle $product, $variantNameType = ProductVariantNameGeneratorInterface::VARIANT_NAME_TYPE_DEFAULT)
    {
        if (false === $product->IsVariant()) {
            return null;
        }

        return $this->generateNameForLanguage($product, $variantNameType, $product->GetLanguage());
    }

    /**
     * @param \TdbShopArticle $product
     * @param string          $variantNameType
     * @param string          $languageId
     *
     * @return string
     */
    private function generateNameForLanguage(\TdbShopArticle $product, $variantNameType, $languageId)
    {
        $variantValues = $product->GetFieldShopVariantTypeValueList();
        $variantValues->SetLanguage($languageId);
        $variantValues->GoToStart();
        $nameParts = [];
        while ($variantValue = $variantValues->Next()) {
            if (self::VARIANT_NAME_TYPE_URL === $variantNameType) {
                $nameParts[] = $variantValue->GetURLString();
            } else {
                $nameParts[] = $variantValue->fieldName;
            }
        }
        $variantValues->GoToStart();
        if (self::VARIANT_NAME_TYPE_URL === $variantNameType) {
            $glue = '/';
        } else {
            $glue = ', ';
        }

        return \implode($glue, $nameParts);
    }

    /**
     * {@inheritdoc}
     */
    public function generateNamesForAllLanguages(\TdbShopArticle $product, $variantNameType = ProductVariantNameGeneratorInterface::VARIANT_NAME_TYPE_DEFAULT)
    {
        if (false === $product->IsVariant()) {
            return null;
        }

        $cmsConfig = \TCMSConfig::GetInstance();
        $languageIdList = $cmsConfig->GetMLTIdList('cms_language_mlt');
        if (false === \in_array($cmsConfig->fieldTranslationBaseLanguageId, $languageIdList)) {
            $languageIdList[] = $cmsConfig->fieldTranslationBaseLanguageId;
        }

        $nameList = [];
        foreach ($languageIdList as $languageId) {
            $nameList[$languageId] = $this->generateNameForLanguage($product, $variantNameType, $languageId);
        }

        return $nameList;
    }
}
