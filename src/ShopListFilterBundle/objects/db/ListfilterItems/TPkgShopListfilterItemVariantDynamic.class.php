<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

/**
 * base class used to select from a specific variant type.
/**/
class TPkgShopListfilterItemVariantDynamic extends TPkgShopListfilterItemVariant
{
    protected $sVariantTypeIdentifier = '';

    /**
     * Get variant system name and set it to sVariantTypeIdentifier.
     */
    protected function PostLoadHook()
    {
        parent::PostLoadHook();
        $oListFilterItemType = $this->GetFieldPkgShopListfilterItemType();
        if ($oListFilterItemType) {
            $this->sVariantTypeIdentifier = $this->fieldVariantIdentifier;
        }
    }

    /**
     * return url that sets the current filter to some value.
     *
     * @param string $sValue
     *
     * @return string
     */
    public function GetAddFilterURL($sValue)
    {
        $oActiveListfilter = TdbPkgShopListfilter::GetActiveInstance();
        $aURLData = $oActiveListfilter->GetCurrentFilterAsArray();
        if (false == $this->fieldAllowMultiSelection || '' == $sValue) {
            if ($this->IsSelected($sValue)) {
                unset($aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id]);
            } else {
                $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id] = $sValue;
            }
        } else {
            if (is_array($aURLData) && count($aURLData) > 0 &&
               true == isset($aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id]) &&
               false === is_array($aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id])) {
                $sTmpValue = $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id];
                $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id] = array();
                $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id][] = $sTmpValue;
            }
            if ($this->IsSelected($sValue)) {
                foreach ($aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id] as $iIndex => $sSelectedValue) {
                    if ($sSelectedValue == $sValue) {
                        unset($aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id][$iIndex]);
                    }
                }
            } else {
                $aURLData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA][$this->id][] = $sValue;
            }
        }
        $aURLData[TdbPkgShopListfilter::URL_PARAMETER_IS_NEW_REQUEST] = '1';

        return $this->getActivePageService()->getLinkToActivePageRelative($aURLData);
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
