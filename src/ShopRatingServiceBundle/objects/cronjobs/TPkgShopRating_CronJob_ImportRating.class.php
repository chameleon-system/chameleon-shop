<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * the cronjob imports all rating data
 * see #10260.
 *
/**/
class TPkgShopRating_CronJob_ImportRating extends TCMSCronJob
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * executes the cron job (add your custom method calls here).
     */
    protected function _ExecuteCron()
    {
        parent::_ExecuteCron();

        $oList = TdbPkgShopRatingServiceList::GetList();
        while ($oService = $oList->Next()) {
            if ($oService->fieldActive && $oService->fieldAllowImport) {
                echo 'Importing: '.$oService->fieldClass.'...';
                if ($oService->Import()) {
                    echo 'ok.<br />';
                } else {
                    echo 'error!<br />';
                }
            }
        }
    }
}
