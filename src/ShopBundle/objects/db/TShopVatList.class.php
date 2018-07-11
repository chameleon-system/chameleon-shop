<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopVatList extends TShopVatListAutoParent
{
    /**
     * get the total vat value for the current group.
     *
     * @return float
     */
    public function GetTotalVatValue()
    {
        $dVal = 0;
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            $dVal += $oItem->GetVatValue();
        }
        $this->setItemPointer($iPointer);

        return $dVal;
    }

    /**
     * get the total net value for the current group.
     *
     * @return float
     */
    public function GetTotalNetValue()
    {
        $dVal = 0;
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            $dVal += $oItem->getNetValue();
        }
        $this->setItemPointer($iPointer);

        return $dVal;
    }

    /**
     * get the total gross value for the current group.
     *
     * @return float
     */
    public function GetTotalValue()
    {
        $dVal = 0;
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            $dVal += $oItem->getTotalValue();
        }
        $this->setItemPointer($iPointer);

        return $dVal;
    }

    /**
     * return largest item in list.
     *
     * @return TdbShopVat
     */
    public function GetMaxItem()
    {
        /** @var $oMaxItem null|TdbShopVat */
        $oMaxItem = null;
        $iPt = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            if ($oItem->getTotalValue() > 0 && (is_null($oMaxItem) || $oItem->getTotalValue() > $oMaxItem->getTotalValue())) {
                $oMaxItem = $oItem;
            }
        }
        $this->setItemPointer($iPt);

        return $oMaxItem;
    }
}
