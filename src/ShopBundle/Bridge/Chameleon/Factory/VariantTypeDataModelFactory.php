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

use ChameleonSystem\ShopBundle\Interfaces\VariantTypeDataModelFactoryInterface;
use ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeDataModelInterface;

class VariantTypeDataModelFactory implements VariantTypeDataModelFactoryInterface
{
    private string $dataModelClassName = 'ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeDataModel';
 
    public function createFromVariantTypeRecord(
        \TdbShopVariantType $shopVariantType, 
        bool $isSelectionAllowed,
        ?string $dataModelClassName = null): VariantTypeDataModelInterface
    {
        if (null === $dataModelClassName) {
            $dataModelClassName = $this->dataModelClassName;
        }
        
        return new $dataModelClassName(
            $shopVariantType->fieldName,
            $shopVariantType->fieldIdentifier,
            $shopVariantType->fieldCmsMediaId,
            $isSelectionAllowed
        );
    }
}
