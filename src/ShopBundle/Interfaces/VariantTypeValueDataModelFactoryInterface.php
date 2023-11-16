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

use ChameleonSystem\ShopBundle\Library\DataModels\VariantTypeValueDataModelInterface;

interface VariantTypeValueDataModelFactoryInterface
{
    public function createFromVariantTypeValueRecord(
        \TdbShopVariantType $variantTypeRecord,
        \TdbShopVariantTypeValue $shopVariantTypeValue,
        bool $loadInactiveItems,
        array $currentSelectedParameters,
        bool $variantIsActive        
    ): VariantTypeValueDataModelInterface;

    /**
     * @param string $dataModelClass The class name of the data model including the namespace.
     * @throws \InvalidArgumentException If the dataModelClass does not implement VariantTypeValueDataModelInterface.
     */
    public function setDataModelClass(string $dataModelClass): void;
}
