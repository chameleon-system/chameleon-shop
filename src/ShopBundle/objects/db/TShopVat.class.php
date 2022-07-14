<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopVat extends TShopVatAutoParent
{
    /**
     * holds the gross total value on which the vat will act.
     *
     * @var float
     */
    protected $dTotalValue = 0;

    /** @var float */
    protected $dNetValue = 0;

    /** @var float */
    protected $dGrossValue = 0;

    /** @var float */
    protected $dVatValue = 0;

    /**
     * @param float|int $dValue
     *
     * @return void
     */
    public function addValue($dValue)
    {
        $this->dTotalValue += $dValue;
        $this->recalculate();
    }

    /**
     * @return void
     */
    protected function recalculate()
    {
        $this->dGrossValue = $this->dTotalValue;
        $this->dNetValue = ($this->dGrossValue / (1 + ($this->fieldVatPercent / 100)));
        $this->dVatValue = $this->dGrossValue - $this->dNetValue;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->dTotalValue = 0;
        $this->dNetValue = 0;
        $this->dGrossValue = 0;
        $this->dVatValue = 0;
    }

    /**
     * return the gross value.
     *
     * @return float
     */
    public function getTotalValue()
    {
        return $this->dTotalValue;
    }

    /**
     * return the net value.
     *
     * @return float
     */
    public function getNetValue()
    {
        return round($this->dNetValue, 2);
    }

    /**
     * @return int
     */
    public function getGrossValue()
    {
        return 0;
    }

    /**
     * return the vat value fo the gross value.
     *
     * @return float
     */
    public function GetVatValue()
    {
        return round($this->dVatValue, 2);
    }
}
