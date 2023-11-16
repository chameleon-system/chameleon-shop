<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\Factory;

use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\ShopBundle\Interfaces\VariantTypeValueDataModelFactoryInterface;
use ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeValueDataModelInterface;

class VariantTypeValueDataModelFactory implements VariantTypeValueDataModelFactoryInterface
{
    private string $dataModelClass = 'ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeValueDataModel';
    private UrlUtil $urlUtil;

    public function __construct(UrlUtil $urlUtil) 
    {
        $this->urlUtil = $urlUtil;
    }    
    
    public function createFromVariantTypeValueRecord(
        \TdbShopVariantType $variantTypeRecord,
        \TdbShopVariantTypeValue $shopVariantTypeValue,
        bool $loadInactiveItems, 
        array $currentSelectedParameters,
        bool $variantIsActive): VariantTypeValueDataModelInterface
    {
        return new $this->dataModelClass(
            $shopVariantTypeValue->fieldName,
            $shopVariantTypeValue->fieldColorCode,
            $this->getImageId($shopVariantTypeValue),
            $variantIsActive,
            $this->getVariantSelectionUrlParameters($currentSelectedParameters, $variantTypeRecord, $shopVariantTypeValue),
            $this->isProductVariantSelectable($shopVariantTypeValue, $loadInactiveItems)
        );
    }

    /**
     * @inheritDoc
     */
    public function setDataModelClass(string $dataModelClass): void
    {
        if (!is_a($dataModelClass, VariantTypeValueDataModelInterface::class, true)) {
            throw new \InvalidArgumentException('dataModelClass must implement ' . VariantTypeValueDataModelInterface::class);
        }        
        
        $this->dataModelClass = $dataModelClass;
    }
    
    private function getImageId(\TdbShopVariantTypeValue $shopVariantTypeValue): string
    {
        $imageId = $shopVariantTypeValue->fieldCmsMediaId;
        if (is_numeric($imageId) && $imageId < 1000) { // dummy image
            $imageId = '';
        }
        
        return $imageId;
    }

    private function isProductVariantSelectable(\TdbShopVariantTypeValue $variantTypeValueRecord, bool $loadInactiveItems): bool
    {
        /**
         * @note "articleactive" is only set if the variants are loaded via TShopArticle::GetVariantValuesAvailableForTypeIncludingInActive
         */
        
        if (!isset($variantTypeValueRecord->sqlData['articleactive'])) {
            return true;
        }
        
        $isProductActive = '1' === $variantTypeValueRecord->sqlData['articleactive'];
        
        if (true === $isProductActive) {
            return true;
        }
        
        return (true === $loadInactiveItems && false === $isProductActive);
    }

    private function getVariantSelectionUrlParameters(array $currentSelectedParameters, \TdbShopVariantType $variantTypeRecord, \TdbShopVariantTypeValue $variantTypeValueRecord): string
    {
        $currentSelectedParameters[$variantTypeRecord->id] = $variantTypeValueRecord->id;
        $aPageParameters[\TShopVariantType::URL_PARAMETER] = $currentSelectedParameters;

        return $this->urlUtil->getArrayAsUrl($aPageParameters, '?', '&amp;');
    }
}
