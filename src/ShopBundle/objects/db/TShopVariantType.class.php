<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopVariantType extends TAdbShopVariantType
{
    public const URL_PARAMETER = 'aVariantTypeValues';

    /**
     * Verfügbare Variantenwerte.
     *
     * @return TdbShopVariantTypeValueList
     */
    public function GetFieldShopVariantTypeValueList()
    {
        $oValueList = TdbShopVariantTypeValueList::GetListForShopVariantTypeId($this->id, $this->iLanguageId);
        $oValueList->ChangeOrderBy([$this->fieldShopVariantTypeValueCmsfieldname => 'ASC']);

        return $oValueList;
    }
}
