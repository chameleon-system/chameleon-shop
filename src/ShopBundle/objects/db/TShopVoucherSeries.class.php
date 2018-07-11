<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopVoucherSeries extends TShopVoucherSeriesAutoParent implements IPkgShopVatable
{
    /**
     * @return TdbShopVat
     */
    public function GetVat()
    {
        return $this->GetFieldShopVat();
    }

    /**
     * return true if the voucher series is active.
     *
     * @return bool
     */
    public function IsActive()
    {
        $sToday = date('Y-m-d H:i:s');
        $bIsActive = ($this->fieldActive && ($this->fieldActiveFrom <= $sToday && ($this->fieldActiveTo >= $sToday || '0000-00-00 00:00:00' == $this->fieldActiveTo)));

        return $bIsActive;
    }

    /**
     * returns the number of vouchers from that series that have been used by the user (note, we count
     * also the vouchers that have been used in part only.
     *
     * @param int   $iDataExtranetUserId (if null, use the current user)
     * @param array $aExcludeVouchers    - the voucher ids to exclude from the count
     *
     * @return int
     */
    public function NumberOfTimesUsedByUser($iDataExtranetUserId = null, $aExcludeVouchers = array())
    {
        $iNumberOfVouchersUsed = 0;

        if (is_null($iDataExtranetUserId)) {
            $oUser = TdbDataExtranetUser::GetInstance();
            $iDataExtranetUserId = $oUser->id;
        }

        $sRestriction = '';
        if (count($aExcludeVouchers) > 0) {
            $aExcludeVouchers = TTools::MysqlRealEscapeArray($aExcludeVouchers);
            $sRestriction = "AND `shop_voucher`.`id` NOT IN ('".implode("','", $aExcludeVouchers)."')";
        }
        $query = "SELECT COUNT(DISTINCT `shop_voucher`.`id`) AS number_of_vouchers
                  FROM `shop_voucher_use`
            INNER JOIN `shop_voucher` ON `shop_voucher_use`.`shop_voucher_id` = `shop_voucher`.`id`
            INNER JOIN `shop_order` ON `shop_voucher_use`.`shop_order_id` = `shop_order`.`id`
                 WHERE `shop_order`.`data_extranet_user_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($iDataExtranetUserId)."'
                   AND `shop_voucher`.`shop_voucher_series_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                   {$sRestriction}
              GROUP BY `shop_voucher`.`shop_voucher_series_id`
               ";

        if ($aTmpRow = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
            $iNumberOfVouchersUsed = $aTmpRow['number_of_vouchers'];
        }

        return $iNumberOfVouchersUsed;
    }

    /**
     * create a new voucher.
     *
     * @param string $sCode - code to create
     *
     * @return TdbShopVoucher
     */
    public function CreateNewVoucher($sCode = null)
    {
        if (is_null($sCode)) {
            $bCodeUnique = false;
            $dMaxTry = 15;
            $sCode = '';
            while (!$bCodeUnique && $dMaxTry > 0) {
                --$dMaxTry;
                $sCode = TdbShopVoucher::GenerateVoucherCode();
                $query = "SELECT * FROM `shop_voucher` WHERE `code` = '".MySqlLegacySupport::getInstance()->real_escape_string($sCode)."'";
                $tRes = MySqlLegacySupport::getInstance()->query($query);
                if (0 == MySqlLegacySupport::getInstance()->num_rows($tRes)) {
                    $bCodeUnique = true;
                }
            }
        }
        $aData = array('shop_voucher_series_id' => $this->id, 'code' => $sCode, 'datecreated' => date('Y-m-d H:i:s'));
        $oVoucher = TdbShopVoucher::GetNewInstance();
        $oVoucher->LoadFromRow($aData);
        $oVoucher->AllowEditByAll();
        $oVoucher->Save();

        return $oVoucher;
    }
}
