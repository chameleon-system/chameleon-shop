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
 * resend unsent order emails flagged by TShopOrder (try n times).
/**/
class TCMSCronJob_ShopSendOrderNotifications extends TCMSCronJob
{
    const MAX_AGE_DAYS = 5; //only fetch orders newer than n-days
    const MAX_TRIES = 3; //try to resend notification n-times
    const MAIL_SEND_FAIL = 'shop-confirm-order-failed';
    /**
     * @var string
     */
    private $developmentEmailAddress;

    /**
     * @param string $developmentEmailAddress
     */
    public function __construct($developmentEmailAddress)
    {
        parent::TCMSCronJob();
        $this->developmentEmailAddress = $developmentEmailAddress;
    }

    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        $sMinDate = date('Y-m-d H:i:s', time() - (self::MAX_AGE_DAYS * 24 * 3600));
        $oShopOrders = TdbShopOrderList::GetList("SELECT *
                                                  FROM `shop_order`
                                                 WHERE `system_order_save_completed` = '1'
                                                   AND `system_order_notification_send` = '0'
                                                   AND `datecreated` > '".$sMinDate."'
                                                   AND `canceled` = '0'
                                              ORDER BY `datecreated`
                                             ASC LIMIT 1000");
        while ($oShopOrder = $oShopOrders->Next()) {
            if (!empty($oShopOrder->fieldObjectMail)) {
                $oMail = unserialize(base64_decode($oShopOrder->fieldObjectMail));
                if ($oMail->SendUsingObjectView('emails', 'Customer')) {
                    MySqlLegacySupport::getInstance()->query("UPDATE `shop_order` SET `system_order_notification_send`='1', `object_mail`='' WHERE `id`='".MySqlLegacySupport::getInstance()->real_escape_string($oShopOrder->id)."'");
                } else {
                    $iAttemps = $oMail->GetData('iAttemptsToSend');
                    if ($iAttemps > self::MAX_TRIES) {
                        //give up, send notification
                        $sSendToMail = $oMail->GetData('sSendToMail');
                        $sText = date('Y-m-d H:i:s').' - '.$sSendToMail.' - Send E-Mail Error';
                        TTools::WriteLogEntrySimple($sText, 1, __FILE__, __LINE__);
                        $sNewTargetMail = $this->developmentEmailAddress;
                        $aBCC = explode("\n", $oMail->sqlData['mailbcc']);
                        if (is_array($aBCC) && count($aBCC) > 0) {
                            $sNewTargetMail = $aBCC[0];
                        } elseif (!is_array($aBCC) && !empty($aBCC)) {
                            $sNewTargetMail = $aBCC;
                        }
                        $oMail->ChangeToAddress($sNewTargetMail, 'SendToName');
                        $oMail->SetSubject(TGlobal::Translate('chameleon_system_shop.cron_resend_order_mail.subject', array('%mail%' => $sSendToMail, '%date%' => date('Y-m-d H:i:s'))));
                        $oMail->SendUsingObjectView('emails', 'Customer');
                        MySqlLegacySupport::getInstance()->query("UPDATE `shop_order` SET `system_order_notification_send`='1', `object_mail`='' WHERE `id`='".MySqlLegacySupport::getInstance()->real_escape_string($oShopOrder->id)."'");
                    } else {
                        $iAttemps = $iAttemps + 1;
                        $oMail->AddData('iAttemptsToSend', $iAttemps);
                        MySqlLegacySupport::getInstance()->query("UPDATE `shop_order` SET `object_mail`='".base64_encode(serialize($oMail))."' WHERE `id`='".MySqlLegacySupport::getInstance()->real_escape_string($oShopOrder->id)."'");
                    }
                }
            } else {
                $bOrderSend = false;
                //we don't have a mail object, most likely system crashed before calling SendOrderNotifaction => try sending without the mail object
                $oOrderItems = $oShopOrder->GetFieldShopOrderItemList();
                if (0 == $oOrderItems->Length() || $oOrderItems->FindItemWithProperty('fieldShopArticleId', '') || $oOrderItems->FindItemWithProperty('fieldShopArticleId', '0')) {
                    // order broken!
                    $bOrderSend = false;
                } else {
                    // send order
                    $bOrderSend = $oShopOrder->SendOrderNotification();
                }

                if (!$bOrderSend) {
                    $oMail = TDataMailProfile::GetProfile(self::MAIL_SEND_FAIL);
                    $aMailData = array();
                    $aMailData['ordernumber'] = $oShopOrder->fieldOrdernumber;
                    $aMailData['customer_name'] = $oShopOrder->fieldAdrBillingFirstname.' '.$oShopOrder->fieldAdrBillingLastname;
                    $aMailData['customer_email'] = $oShopOrder->fieldUserEmail;
                    $aMailData['orderid'] = $oShopOrder->id;
                    $oMail->AddDataArray($aMailData);
                    $oMail->SendUsingObjectView('emails', 'Customer');
                }

                $sQuery = "UPDATE `shop_order` SET `system_order_notification_send`='1' WHERE `id`='".$oShopOrder->id."'";
                MySqlLegacySupport::getInstance()->query($sQuery);
            }
        } // while
    }

    // function
}
