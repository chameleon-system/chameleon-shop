<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopOrderStatus extends TShopOrderStatusAutoParent
{
    /**
     * retun the status text.
     *
     * @param bool $bPrepareTextForRemoteUsage
     *
     * @return string
     */
    public function GetStatusText($bPrepareTextForRemoteUsage = false)
    {
        $sHTML = '';
        $oOrder = $this->GetFieldShopOrder();
        $oCode = $this->GetFieldShopOrderStatusCode();
        $aData = $oOrder->GetSQLWithTablePrefix();
        $aTmpData = $this->GetSQLWithTablePrefix();
        $aData = array_merge($aData, $aTmpData);
        $aData['data'] = $this->fieldData;
        if ($bPrepareTextForRemoteUsage) {
            $sHTML .= $oCode->GetTextForExternalUsage('info_text', 600, true, $aData);
        } else {
            $sHTML .= $oCode->GetTextField('info_text', 600, true, $aData);
        }

        if ($bPrepareTextForRemoteUsage) {
            $sHTML .= $this->GetTextForExternalUsage('info', 600, true, $aData);
        } else {
            $sHTML .= $this->GetTextField('info', 600, true, $aData);
        }

        return $sHTML;
    }
}
