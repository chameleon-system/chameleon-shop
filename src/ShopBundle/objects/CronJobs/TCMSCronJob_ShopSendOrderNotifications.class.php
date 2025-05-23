<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;

/**
 * resend unsent order emails flagged by TShopOrder (try n times).
 * /**/
class TCMSCronJob_ShopSendOrderNotifications extends TdbCmsCronjobs
{
    public const MAX_AGE_DAYS = 5; // only fetch orders newer than n-days
    public const MAX_TRIES = 3; // try to resend notification n-times
    public const MAIL_SEND_FAIL = 'shop-confirm-order-failed';
    /**
     * @var string
     */
    private $developmentEmailAddress;
    /**
     * @var LanguageServiceInterface
     */
    private $languageService;

    public function __construct(string $developmentEmailAddress, LanguageServiceInterface $languageService)
    {
        parent::__construct();
        $this->developmentEmailAddress = $developmentEmailAddress;
        $this->languageService = $languageService;
    }

    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sMinDate = date('Y-m-d H:i:s', time() - (self::MAX_AGE_DAYS * 24 * 3600));

        $sQuery = "
        SELECT *
          FROM `shop_order`
         WHERE `system_order_save_completed` = '1'
           AND `system_order_notification_send` = '0'
           AND `datecreated` > '{$sMinDate}'
           AND `canceled` = '0'
      GROUP BY `cms_language_id`
      ORDER BY `datecreated` ASC
         LIMIT 1000
    ";
        $oShopOrders = TdbShopOrderList::GetList($sQuery);

        $currentLanguageId = $this->languageService->getActiveLanguageId();
        $initialLanguageId = $currentLanguageId;

        while ($oShopOrder = $oShopOrders->Next()) {
            if ('' !== $oShopOrder->fieldCmsLanguageId && $oShopOrder->fieldCmsLanguageId !== $currentLanguageId) {
                $this->languageService->setActiveLanguage($oShopOrder->fieldCmsLanguageId);
                $currentLanguageId = $oShopOrder->fieldCmsLanguageId;
            }

            if (!empty($oShopOrder->fieldObjectMail)) {
                $oMail = unserialize(base64_decode($oShopOrder->fieldObjectMail));
                if ($oMail->SendUsingObjectView('emails', 'Customer')) {
                    $quotedOrderId = $connection->quote($oShopOrder->id);

                    $updateQuery = "
                    UPDATE `shop_order`
                       SET `system_order_notification_send` = '1',
                           `object_mail` = ''
                     WHERE `id` = {$quotedOrderId}
                ";
                    $connection->executeStatement($updateQuery);
                } else {
                    $iAttemps = $oMail->GetData('iAttemptsToSend');
                    if ($iAttemps > self::MAX_TRIES) {
                        // give up, send notification
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
                        $oMail->SetSubject(
                            ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans(
                                'chameleon_system_shop.cron_resend_order_mail.subject',
                                [
                                    '%mail%' => $sSendToMail,
                                    '%date%' => date('Y-m-d H:i:s'),
                                ]
                            )
                        );
                        $oMail->SendUsingObjectView('emails', 'Customer');

                        $quotedOrderId = $connection->quote($oShopOrder->id);

                        $updateQuery = "
                        UPDATE `shop_order`
                           SET `system_order_notification_send` = '1',
                               `object_mail` = ''
                         WHERE `id` = {$quotedOrderId}
                    ";
                        $connection->executeStatement($updateQuery);
                    } else {
                        ++$iAttemps;
                        $oMail->AddData('iAttemptsToSend', $iAttemps);

                        $quotedOrderId = $connection->quote($oShopOrder->id);
                        $quotedObjectMail = $connection->quote(base64_encode(serialize($oMail)));

                        $updateQuery = "
                        UPDATE `shop_order`
                           SET `object_mail` = {$quotedObjectMail}
                         WHERE `id` = {$quotedOrderId}
                    ";
                        $connection->executeStatement($updateQuery);
                    }
                }
            } else {
                $bOrderSend = false;
                // we don't have a mail object, most likely system crashed before calling SendOrderNotifaction => try sending without the mail object

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

                    $aMailData = [
                        'ordernumber' => $oShopOrder->fieldOrdernumber,
                        'customer_name' => $oShopOrder->fieldAdrBillingFirstname.' '.$oShopOrder->fieldAdrBillingLastname,
                        'customer_email' => $oShopOrder->fieldUserEmail,
                        'orderid' => $oShopOrder->id,
                    ];

                    $oMail->AddDataArray($aMailData);
                    $oMail->SendUsingObjectView('emails', 'Customer');
                }

                $quotedOrderId = $connection->quote($oShopOrder->id);

                $updateQuery = "
                UPDATE `shop_order`
                   SET `system_order_notification_send` = '1'
                 WHERE `id` = {$quotedOrderId}
            ";
                $connection->executeStatement($updateQuery);
            }
        } // while

        $this->languageService->setActiveLanguage($initialLanguageId);
    }

    // function
}
