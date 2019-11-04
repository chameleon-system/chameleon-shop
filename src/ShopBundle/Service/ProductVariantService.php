<?php

namespace ChameleonSystem\ShopBundle\Service;

use ChameleonSystem\ShopBundle\Interfaces\ProductVariantServiceInterface;

class ProductVariantService implements ProductVariantServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function getProductBasedOnSelection(\TdbShopArticle $shopArticle, array $typeSelection): \TdbShopArticle
    {
        // shop_article -> shop_variant_set -> shop_variant_type -> shop_variant_type_value

        if (true === $shopArticle->IsVariant() && \count($typeSelection) === 0) {
            // no change possible if nothing is (further) selected in $typeSelection

            return $shopArticle;
        }

        if (true === $shopArticle->IsVariant()) {
            $shopArticle = $shopArticle->GetFieldVariantParent();
        }

        $variantSet = $shopArticle->GetFieldShopVariantSet();

        if (null === $variantSet) {
            return $shopArticle;
        }

        $variantTypes = $variantSet->GetFieldShopVariantTypeList();
        $variantTypes->GoToStart();

        $typeSelection = $this->matchUserSelectionToAvailableVariantValues($shopArticle, $typeSelection, $variantTypes);

        $variantList = $shopArticle->GetFieldShopArticleVariantsList($typeSelection);

        if (1 === $variantList->Length()) {
            return $variantList->Current();
        }

        return $shopArticle;
    }

    /**
     * @param \TdbShopArticle         $shopArticle
     * @param array                   $userSelection
     * @param \TdbShopVariantTypeList $variantTypes
     * @return array - the matched selection
     */
    private function matchUserSelectionToAvailableVariantValues(
        \TdbShopArticle $shopArticle,
        array $userSelection,
        \TdbShopVariantTypeList $variantTypes
    ): array {

        $tmpSelected = [];
        $calculatedSelection = $userSelection;

        foreach ($calculatedSelection as $typeId => $valueId) {
            if (null === $valueId || '' === $valueId) {
                unset($calculatedSelection[$typeId]);
            }
        }

        while (false !== ($variantType = $variantTypes->Next())) {
            // query article repeatedly for variant values and fill $tmpSelected, if there is only one value: pick it

            $availableValues = $shopArticle->GetVariantValuesAvailableForType($variantType, $tmpSelected);
            $availableValues->GoToStart();

            if (true === \array_key_exists($variantType->id, $calculatedSelection)) {
                if (false === $availableValues->IsInList($calculatedSelection[$variantType->id])) {
                    unset($calculatedSelection[$variantType->id]);
                }
            }
            $availableValues->GoToStart();

            if (false === \array_key_exists($variantType->id, $calculatedSelection)) {
                if (1 === $availableValues->Length()) {
                    $calculatedSelection[$variantType->id] = $availableValues->Current()->id;
                }
            }

            if (true === \array_key_exists($variantType->id, $calculatedSelection)) {
                $tmpSelected[$variantType->id] = $calculatedSelection[$variantType->id];
            }
        }

        return $calculatedSelection;
    }
}
