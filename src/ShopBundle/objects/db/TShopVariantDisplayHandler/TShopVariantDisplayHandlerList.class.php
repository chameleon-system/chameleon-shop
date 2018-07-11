<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopVariantDisplayHandlerList extends TAdbShopVariantDisplayHandlerList
{
    /**
     * factory returning an element for the list.
     *
     * @param array $aData
     *
     * @return TCMSRecord
     */
    protected function &_NewElement(&$aData)
    {
        $oElement = false;
        $sTableObject = $this->sTableObject;
        if (array_key_exists('class', $aData)) {
            $sTableObject = $aData['class'];
        }

        // try to fetch the element from _items first
        $oElement = new $sTableObject();
        $oElement->table = $this->sTableName;
        $oElement->SetLanguage($this->iLanguageId);
        $oElement->LoadFromRow($aData);

        return $oElement;
    }
}
