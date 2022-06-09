<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * manages the content quantity for products (needed for base price calculations).
/**/
class TShopUnitOfMeasurement extends TShopUnitOfMeasurementAutoParent
{
    /**
     * Returns the base price for the source price.
     *
     * @param float $sourcePrice
     * @param float $sourceQuantity
     *
     * @return float
     */
    public function GetBasePrice($sourcePrice, $sourceQuantity)
    {
        if (empty($this->fieldFactor)) {
            $factor = 1;
        } else {
            $factor = $this->fieldFactor;
        }
        // 4€ = 500ml; 4€ = 500(0,001)L; 4/(500*0,001)€ = 1L; 4/0,5€=1L; 8€ = 1L
        $conversionFactor = $sourceQuantity * $factor;
        if ($conversionFactor < 0.000001 && $conversionFactor > -0.000001) {
            $basePrice = $sourcePrice;
        } else {
            $basePrice = $sourcePrice / $conversionFactor;
        }

        return $basePrice;
    }

    /**
     * @return TdbShopUnitOfMeasurement|null
     */
    public function &GetFieldShopUnitOfMeasurement()
    {
        if (empty($this->fieldShopUnitOfMeasurementId)) {
            return $this;
        } else {
            return parent::GetFieldShopUnitOfMeasurement();
        }
    }
}
