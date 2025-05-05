<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterMapper_FilterBoolean extends AbstractPkgShopListfilterMapper_Filter
{
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);

        /** @var $oFilterItem TPkgShopListfilterItemBoolean */
        $oFilterItem = $oVisitor->GetSourceObject('oFilterItem');
        $aFilterData = [
            '0' => [
                'sValue' => '',
                'bActive' => false,
                'iCount' => 0,
                'sURL' => '#',
            ],
            '1' => [
                'sValue' => '',
                'bActive' => false,
                'iCount' => 0,
                'sURL' => '#',
            ],
        ];

        $aOptions = $oFilterItem->GetOptions();
        $iActiveValue = $oFilterItem->GetActiveValue();
        foreach ($aOptions as $sValue => $iCount) {
            $aFilterData[$sValue] = [
                'sValue' => $sValue,
                'bActive' => $sValue == $iActiveValue,
                'iCount' => $iCount,
                'sURL' => $oFilterItem->GetAddFilterURL($sValue),
            ];
        }

        $oVisitor->SetMappedValue('aFilterData', $aFilterData);
    }
}
