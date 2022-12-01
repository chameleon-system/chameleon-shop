<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgImageHotspot extends TAdbPkgImageHotspot
{
    /**
     * @return TdbPkgImageHotspotItemList
     */
    public function GetFieldPkgImageHotspotItemList()
    {
        $oList = TdbPkgImageHotspotItemList::GetListForPkgImageHotspotId($this->id, $this->iLanguageId);
        $oList->ChangeOrderBy(array('position' => 'ASC'));
        $oList->bAllowItemCache = true;
        $i = 0;
        $oList->GoToStart();
        while ($oItem = $oList->Next()) {
            ++$i;
        }
        $oList->GoToStart();

        return $oList;
    }
}
