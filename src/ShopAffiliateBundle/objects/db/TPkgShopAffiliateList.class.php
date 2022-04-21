<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopAffiliateList extends TPkgShopAffiliateListAutoParent
{
    /**
     * factory returning an element for the list.
     *
     * @param array $aData
     *
     * @return TdbPkgShopAffiliate
     */
    protected function &_NewElement(&$aData)
    {
        if (!empty($aData['class'])) {
            $sClassName = $aData['class'];
            $oElement = new $sClassName();
            $oElement->LoadFromRow($aData);

            return $oElement;
        } else {
            return parent::_NewElement($aData);
        }
    }

    public function __construct($sQuery = null, $sLanguageId = null)
    {
        parent::__construct($sQuery, $sLanguageId);
        $this->bAllowItemCache = true;
    }
}
