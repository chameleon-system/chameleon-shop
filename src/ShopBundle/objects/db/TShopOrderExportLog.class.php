<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

class TShopOrderExportLog extends TShopOrderExportLogAutoParent
{
    /**
     * write a Log Entry.
     *
     * @param int    $iOrderId
     * @param string $sData
     *
     * @return void
     */
    public static function WriteLog($iOrderId, $sData)
    {
        /** @var $oItem TdbShopOrderExportLog */
        $oItem = TdbShopOrderExportLog::GetNewInstance();
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
        $ip = $request->getClientIp();
        $aData = array('shop_order_id' => $iOrderId, 'datecreated' => date('Y-m-d H:i:s'), 'ip' => $ip, 'data' => $sData, 'user_session_id' => session_id());
        $oItem->LoadFromRow($aData);
        $oItem->AllowEditByAll(true);
        $oItem->Save();
    }
}
