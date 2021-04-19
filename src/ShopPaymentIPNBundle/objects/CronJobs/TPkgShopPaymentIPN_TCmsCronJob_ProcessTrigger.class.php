<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPN_TCmsCronJob_ProcessTrigger extends TdbCmsCronjobs
{


    public function __construct()
    {
        parent::__construct();
    }

    protected function _ExecuteCron()
    {
        parent::_ExecuteCron();

        $query = "SELECT *
                    FROM pkg_shop_payment_ipn_message_trigger
                   WHERE `done` = '0'
                     AND `next_attempt` <= '".date('Y-m-d H:i:s')."'
                ORDER BY `next_attempt` ASC
                 ";
        $oMessageTriggerList = TdbPkgShopPaymentIpnMessageTriggerList::GetList($query);
        while ($oMessageTrigger = $oMessageTriggerList->Next()) {
            $oTrigger = $oMessageTrigger->GetFieldPkgShopPaymentIpnTrigger();
            $oTrigger->runTrigger($oMessageTrigger);
        }
    }
}
