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
 * the cronjob sends a rating mail to compleat shipped orders
 * see #10393.
 *
/**/
class TPkgShopRating_CronJob_SendRatingMails extends TdbCmsCronjobs
{
    private $bDebug = true; //set to true - for debugging output!
    /**
     * set to true if you do not want to insert the info into the db if the mail was sent.
     *
     * @var bool
     */
    private $bDisableSentHistory = false;

    private $Shopreviewmail_MailDelay;
    private $Shopreviewmail_PercentOfCustomers;
    private $Shopreviewmail_SendForEachOrder;

    private $sShopID = null;
    private $sLanguageID = null;
    private $sCountryID = null;
    private $sUserCountryID = null;


    public function __construct()
    {
        parent::__construct();
    }

    protected function Init()
    {
        $this->GetConfigValues();

        $oShop = TdbShop::GetInstance(1);
        $this->sShopID = $oShop->id;

        $oLanguage = TdbCmsLanguage::GetNewInstance();
        $oLanguage->LoadFromField('iso_6391', 'de');
        $this->sLanguageID = $oLanguage->id;

        $oDelCountry = TdbTCountry::GetNewInstance();
        $oDelCountry->LoadFromField('iso_code_2', 'DE');
        $this->sCountryID = $oDelCountry->id;

        $oUsrCountry = TdbDataCountry::GetNewInstance();
        $oUsrCountry->LoadFromField('t_country_id', $this->sCountryID);
        $this->sUserCountryID = $oUsrCountry->id;
    }

    /**
     * executes the cron job (add your custom method calls here).
     */
    protected function _ExecuteCron()
    {
        parent::_ExecuteCron();
        $this->Init();
        $this->ProcessCompletedOrders();
    }

    /**
     * Read rating-mail-config.
     */
    protected function GetConfigValues()
    {
        $oShopConfig = TdbShop::GetInstance(1);
        $this->Shopreviewmail_MailDelay = $oShopConfig->fieldShopreviewmailMailDelay;
        $this->Shopreviewmail_PercentOfCustomers = $oShopConfig->fieldShopreviewmailPercentOfCustomers;
        $this->Shopreviewmail_SendForEachOrder = $oShopConfig->fieldShopreviewmailSendForEachOrder;
    }

    protected function ProcessCompletedOrders()
    {
        $iSendForShippingDateNewerThan = $this->GetShippingDateLowerBound();
        $iMaxNumberOfShopReviewMailsToSend = $this->GetMaxNumberOfShopReviewMailsToSend($iSendForShippingDateNewerThan);

        $query = "SELECT `shop_order`.*
                  FROM `shop_order`
             LEFT JOIN `pkg_shop_rating_service_history` ON `shop_order`.`id` = `pkg_shop_rating_service_history`.`shop_order_id`
                 WHERE `pkg_shop_rating_service_history`.`id` IS NULL
                   AND `shop_order`.`shop_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sShopID)."'
                   AND `shop_order`.`cms_language_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sLanguageID)."'

                   AND `shop_order`.`pkg_shop_rating_service_order_completely_shipped` > '0000-00-00 00:00:00'
                   AND `shop_order`.`pkg_shop_rating_service_order_completely_shipped` <= '".MySqlLegacySupport::getInstance()->real_escape_string(date('Y-m-d H:i:s', $iSendForShippingDateNewerThan))."'
                   AND `shop_order`.`pkg_shop_rating_service_rating_processed_on` = '0000-00-00 00:00:00'
                   AND `shop_order`.`pkg_shop_rating_service_mail_processed` = '0'

                   AND `shop_order`.`adr_billing_country_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sUserCountryID)."'

              ORDER BY `shop_order`.`pkg_shop_rating_service_order_completely_shipped` ASC
               ";

        if ($this->bDebug) {
            echo __LINE__.': '.$query."\n<br />\n";
        }

        $rRes = MySqlLegacySupport::getInstance()->query($query);
        $iNumberOfMailsStillToBeSend = $iMaxNumberOfShopReviewMailsToSend;
        while ($aOrder = MySqlLegacySupport::getInstance()->fetch_assoc($rRes)) {
            if ($iNumberOfMailsStillToBeSend > 0) {
                if ($this->SendShopReviewMail($aOrder)) {
                    --$iNumberOfMailsStillToBeSend;
                }
            }
            $query = "UPDATE `shop_order` SET `pkg_shop_rating_service_mail_processed` = '1', `pkg_shop_rating_service_rating_processed_on` = '".date('Y-m-d H:i:s')."' WHERE `id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aOrder['id'])."'";
            MySqlLegacySupport::getInstance()->query($query);

            if ($this->bDebug) {
                echo __LINE__.': '.$query."\n<br />\n".MySqlLegacySupport::getInstance()->error();
            }
        }
    }

    /**
     * return the shipping date past which a review notification should be send.
     *
     * @return int (unix time stamp)
     */
    public function GetShippingDateLowerBound()
    {
        $iAgeInDays = $this->Shopreviewmail_MailDelay;
        $iNow = time();
        $hour = date('G', $iNow);
        $minute = date('i', $iNow);
        if ('0' == substr($minute, 0, 1)) {
            $minute = substr($minute, 1);
        }
        $second = date('s', $iNow);
        if ('0' == substr($second, 0, 1)) {
            $second = substr($second, 1);
        }
        $iLowerBound = mktime($hour, $minute, $second, date('n', $iNow), date('j', $iNow) - $iAgeInDays, date('Y', $iNow));

        return $iLowerBound;
    }

    public function SendCustomersMailOnlyOnce()
    {
        return '0' == $this->Shopreviewmail_SendForEachOrder;
    }

    /**
     * return true if a review mail may be send for the given customer/order.
     *
     * @param TdbDataExtranetUser $oUser
     * @param array               $aOrder
     *
     * @return bool
     */
    public function AllowSendingMailForOrder($oUser, $aOrder)
    {
        $bAllowSendingMail = true;

        // make sure the order is a DE order
        if ($aOrder['cms_language_id'] != $this->sLanguageID) {
            $bAllowSendingMail = false;
        }

        if ($this->SendCustomersMailOnlyOnce()) {
            $query = "SELECT * FROM `pkg_shop_rating_service_history`
                   WHERE `data_extranet_user_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oUser->id)."'
                 ";

            if ($this->bDebug) {
                echo __LINE__.': '.$query."\n<br />\n";
            }

            $tRes = MySqlLegacySupport::getInstance()->query($query);
            if (MySqlLegacySupport::getInstance()->num_rows($tRes) > 0) {
                $bAllowSendingMail = false;
            }
        }

        if ($this->bDebug) {
            if ($bAllowSendingMail) {
                echo __LINE__.': bAllowSendingMail = true'."\n<br />\n";
            } else {
                echo __LINE__.': bAllowSendingMail = false'."\n<br />\n";
            }
        }

        return $bAllowSendingMail;
    }

    /**
     * send the shop review mail and log the action in es_shop_review
     * returns true if the mail was send. note that the method checks if the customer is allowed
     * to receiven a mail for the given order.
     *
     * @param array $aOrder - the order data
     *
     * @return bool
     */
    public function SendShopReviewMail($aOrder)
    {
        $bMailWasSend = false;

        $oUser = TdbDataExtranetUser::GetNewInstance();
        $oUser->Load($aOrder['data_extranet_user_id']);

        if ($this->AllowSendingMailForOrder($oUser, $aOrder)) {
            $oRatingService = $this->GetSuitableRatingService($oUser, $aOrder);
            if (null !== $oRatingService) {
                $bMailWasSend = $oRatingService->SendShopRatingEmail($oUser, $aOrder);
                if ($bMailWasSend) {
                    $query = "UPDATE `shop_order` SET `pkg_shop_rating_service_mail_sent` = '1', `pkg_shop_rating_service_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oRatingService->id)."' WHERE `id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aOrder['id'])."'";
                    MySqlLegacySupport::getInstance()->query($query);
                    if (false == $this->bDisableSentHistory) {
                        $sNewID = TTools::GetUUID();
                        $iSendDate = date('Y-m-d H:i:s');
                        $query = "INSERT INTO `pkg_shop_rating_service_history`
                               SET `id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sNewID)."',
                                   `data_extranet_user_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oUser->id)."',
                                   `shop_order_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aOrder['id'])."',
                                   `date` = '".MySqlLegacySupport::getInstance()->real_escape_string($iSendDate)."',
                                   `pkg_shop_rating_service_id_list` = '".MySqlLegacySupport::getInstance()->real_escape_string($oRatingService->id)."'
                           ";
                        if ($this->bDebug) {
                            echo __LINE__.': '.$query."\n<br />\n";
                        }
                        MySqlLegacySupport::getInstance()->query($query);
                    }
                }
            }
        }

        return $bMailWasSend;
    }

    /**
     * Try to cleanup incomming shop_order.affiliate_code.
     *
     * @param $sAffiliateCode
     *
     * @return string
     */
    protected function CleanupAffiliateCode($sAffiliateCode)
    {
        $sRet = '';
        $sAffiliateCode = trim($sAffiliateCode);
        if (!empty($sAffiliateCode)) {
            //get all rating services
            $sQuery = "  SELECT *
                       FROM `pkg_shop_rating_service`
                      WHERE `active` = '1'
                   ORDER BY `position`,`weight`  ASC
                  ";
            $oRatingServiceList = TdbPkgShopRatingServiceList::GetList($sQuery);
            $oRatingServiceList->GoToStart();

            //loop and try to identify one...
            while ($oService = $oRatingServiceList->Next()) {
                /* @var $oService TdbPkgShopRatingService */
                $pos = strpos($sAffiliateCode, strtolower(trim($oService->fieldAffiliateValue)));
                if (false !== $pos) {
                    //service found!
                    $oRatingServiceList->GoToEnd();
                    $sRet = $oService->fieldAffiliateValue;
                }
            }
        }

        return $sRet;
    }

    /**
     * Get all rating services available for this user.
     *
     * @param $oUser
     * @param $aOrder array - table row from `shop_order`
     *
     * @return TdbPkgShopRatingServiceList
     */
    protected function GetAvailableRatingServices($oUser, $aOrder)
    {
        $sQuery = "  SELECT *
                     FROM `pkg_shop_rating_service`
                    WHERE `active` = '1' AND `allow_sending_emails` = '1'
                 ORDER BY `position` ASC
                ";
        $oRatingServiceList = TdbPkgShopRatingServiceList::GetList($sQuery);

        return $oRatingServiceList;
    }

    protected function GetSuitableRatingService($oUser, $aOrder)
    {
        $oAvailableRatingServiceList = $this->GetAvailableRatingServices($oUser, $aOrder);
        $sAffiliateCode = '';
        if (!empty($aOrder['affiliate_code'])) {
            $sAffiliateCode = $this->CleanupAffiliateCode(strtolower(trim($aOrder['affiliate_code'])));
        }
        $aRatingServices = array();
        while ($oRatingService = $oAvailableRatingServiceList->Next()) {
            if (strtolower(trim($oRatingService->fieldAffiliateValue)) === $sAffiliateCode) {
                return $oRatingService;
            }
            $aRatingServices[] = array('weight' => $oRatingService->fieldWeight, 'value' => $oRatingService);
        }
        if (count($aRatingServices)) {
            $oRatingService = TTools::GetWeightedRandomArrayValue($aRatingServices);
        } else {
            $oRatingService = null;
        }

        return $oRatingService;
    }

    /**
     * returns the number of review mails that should be send out. this is relevant
     * if only a specified percentages of all orders should get a mail.
     *
     * @param $iSendForShippingDateNewerThan
     *
     * @return int
     */
    public function GetMaxNumberOfShopReviewMailsToSend($iSendForShippingDateNewerThan)
    {
        if ($this->bDebug) {
            echo '<br><br>';
        }

        $query = "SELECT *
                  FROM `shop_order`
                 WHERE `shop_order`.`pkg_shop_rating_service_order_completely_shipped` > '0000-00-00 00:00:00'
                   AND `shop_order`.`shop_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sShopID)."'
                   AND `shop_order`.`cms_language_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sLanguageID)."'
                   AND `shop_order`.`pkg_shop_rating_service_order_completely_shipped` <= '".MySqlLegacySupport::getInstance()->real_escape_string(date('Y-m-d H:i:s', $iSendForShippingDateNewerThan))."'
                   AND `shop_order`.`pkg_shop_rating_service_rating_processed_on` = '0000-00-00 00:00:00'
                   AND `shop_order`.`pkg_shop_rating_service_mail_processed` = '0'
                   AND `shop_order`.`adr_billing_country_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->sUserCountryID)."'
               ";
        if ($this->SendCustomersMailOnlyOnce()) {
            $query .= 'GROUP BY `shop_order`.`customer_number`';
        }

        if ($this->bDebug) {
            echo __LINE__.': '.$query."\n<br />\n";
        }

        $rRow = MySqlLegacySupport::getInstance()->query($query);
        $iNumberOfCompletedOrders = MySqlLegacySupport::getInstance()->num_rows($rRow);

        $query = ' SELECT * FROM `pkg_shop_rating_service_history` ';
        if ($this->SendCustomersMailOnlyOnce()) {
            $query .= 'GROUP BY `pkg_shop_rating_service_history`.`data_extranet_user_id`';
        }

        if ($this->bDebug) {
            echo __LINE__.': '.$query."\n<br />\n";
        }

        $rRow = MySqlLegacySupport::getInstance()->query($query);
        $iNumberOfReviewMailsSend = MySqlLegacySupport::getInstance()->num_rows($rRow);
        $iPercentageToBeSend = $this->Shopreviewmail_PercentOfCustomers; //$this->GetConfigValue('shopreviewmail_percent_of_customers');;
        $iPercentageMailSend = 100;

        if (0 == $iNumberOfCompletedOrders && 0 == $iNumberOfReviewMailsSend) {
            $iPercentageMailSend = 100;
        } elseif ($iNumberOfCompletedOrders > 0) {
            $iPercentageMailSend = ($iNumberOfReviewMailsSend / $iNumberOfCompletedOrders) * 100;
        } else {
            $iPercentageMailSend = 0;
        }

        if ($this->bDebug) {
            echo '<br />';
        }
        if ($this->bDebug) {
            echo __LINE__.'iPercentageToBeSend: '.$iPercentageToBeSend."\n<br />\n";
        }
        if ($this->bDebug) {
            echo __LINE__.'iNumberOfCompletedOrders: '.$iNumberOfCompletedOrders."\n<br />\n";
        }

        $iNumberOfReviewMailsToBeSend = ceil(($iPercentageToBeSend / 100) * $iNumberOfCompletedOrders);

        if ($this->bDebug) {
            echo __LINE__.'iNumberOfReviewMailsToBeSend: '.$iNumberOfReviewMailsToBeSend."\n<br />\n";
        }
        if ($this->bDebug) {
            echo __LINE__.'iNumberOfReviewMailsSend: '.$iNumberOfReviewMailsSend."\n<br />\n";
        }
        echo '<br><br>';

        // NOTE: the total possible mails covers all orders that have been send out
        // (so it includes orders which had their expected order date passed - Frau sommer 25.11.08)
        return $iNumberOfReviewMailsToBeSend;
    }

    public function SetDisableSentHistory($bDisableSentHistory)
    {
        $this->bDisableSentHistory = $bDisableSentHistory;
    }
}
