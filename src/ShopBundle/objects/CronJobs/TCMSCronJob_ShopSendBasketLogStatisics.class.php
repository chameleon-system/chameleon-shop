<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;

/**
 * sends stats about basket.
 **/
class TCMSCronJob_ShopSendBasketLogStatisics extends TdbCmsCronjobs
{
    public const MAIL_SYSTEM_NAME = 'basketstatistics';

    /**
     * Uses error message as a key and a counter as a value.
     *
     * @var array<mixed, int>
     *
     * @psalm-var array<array-key, positive-int>
     */
    private array $aCancelStep = [];

    private int $sTotalBaskets = 0;

    /** @var float */
    private $sTotalMoneyCanceled = 0;

    /**
     * Get all none processed shop baskets and send generated statistics to shop owner.
     *
     * @return void
     */
    protected function _ExecuteCron()
    {
        $sQuery = "SELECT * FROM `shop_order_basket`
                         WHERE `shop_order_basket`.`processed` != '1'
                      ORDER BY `shop_order_basket`.`lastmodified` DESC";
        $oOrderBasketList = TdbShopOrderBasketList::GetList($sQuery);
        $sStatistics = "#########################################################\r\n";
        while ($oOrderBasket = $oOrderBasketList->Next()) {
            $sStatistics .= $this->GenerateStatisticsFromOrderBasket($oOrderBasket);
            $this->MarkOrderBasketAsProcessed($oOrderBasket);
            ++$this->sTotalBaskets;
        }
        if ($oOrderBasketList->Length() > 0) {
            $sTotalStatistics = $this->GetTotalStatistics().$sStatistics;
            $this->SendBasketStatistics($sTotalStatistics);
        }
    }

    /**
     * Mark order basket as processed. So no statistics for this order basket will be generated next time.
     *
     * @param TdbShopOrderBasket $oOrderBasket
     *
     * @return void
     */
    protected function MarkOrderBasketAsProcessed($oOrderBasket)
    {
        $oOrderBasket->AllowEditByAll(true);
        $oOrderBasket->sqlData['processed'] = 1;
        $oOrderBasket->Save();
        $oOrderBasket->AllowEditByAll(false);
    }

    /**
     * Get overview statistics of all processed order baskets.
     *
     * @return string
     */
    protected function GetTotalStatistics()
    {
        $sTotalStatistics = "#########################################################\r\n";
        $sTotalStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.headline')."\r\n";
        $sTotalStatistics .= "------------------------------------\r\n";
        foreach ($this->aCancelStep as $sStempName => $sValue) {
            $sPercent = round((100 / $this->sTotalBaskets) * $sValue);
            if ($sStempName !== ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.completed')) {
                $sTotalStatistics .= ServiceLocator::get('translator')->trans(
                    'chameleon_system_shop.cron_send_basket_stats.text_aborted',
                    [
                        '%step%' => $sStempName,
                        '%value%' => $sValue,
                        '%totalBaskets%' => $this->sTotalBaskets,
                        '%percent%' => $sPercent,
                    ]
                )."\r\n";
            } else {
                $sTotalStatistics .= ServiceLocator::get('translator')->trans(
                    'chameleon_system_shop.cron_send_basket_stats.text_completed',
                    [
                        '%value%' => $sValue,
                        '%totalBaskets%' => $this->sTotalBaskets,
                        '%percent%' => $sPercent,
                    ]
                )."\r\n";
            }
        }
        $sTotalStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.text_total_aborted_orders').': '.$this->sTotalMoneyCanceled;

        return $sTotalStatistics."\r\n";
    }

    /**
     * Generate user and basket statistics for given order basket.
     *
     * @param TdbShopOrderBasket $oOrderBasket
     *
     * @return string
     */
    protected function GenerateStatisticsFromOrderBasket($oOrderBasket)
    {
        $sStatistics = '';
        if (empty($oOrderBasket->fieldShopOrderId)) {
            $sStatistics = $this->GetCancelBasketInformation($oOrderBasket);
            $oUser = TdbDataExtranetUser::GetNewInstance();
            $oUser = unserialize(base64_decode($oOrderBasket->fieldRawdataUser));
            if (is_object($oUser)) {
                $sStatistics .= $this->GetUserStatistics($oUser);
            }
            $oBasket = new TShopBasket();
            $oBasket = unserialize(base64_decode($oOrderBasket->fieldRawdataBasket));
            if (is_object($oBasket)) {
                $sStatistics .= $this->GetBasketStatistics($oBasket);
            }
            $sStatistics .= "#########################################################\r\n";
        } else {
            $this->AddCompleteOrderToStatistics();
        }

        return $sStatistics;
    }

    /**
     * If basket was finished completely add it to overview statistics.
     *
     * @return void
     */
    protected function AddCompleteOrderToStatistics()
    {
        if (array_key_exists(ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.completed'), $this->aCancelStep)) {
            ++$this->aCancelStep[ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.completed')];
        } else {
            $this->aCancelStep[ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.completed')] = 1;
        }
    }

    /**
     * Get statistics on which basket step the user canceled.
     *
     * @param TdbShopOrderBasket $oOrderBasket
     *
     * @return string
     */
    protected function GetCancelBasketInformation($oOrderBasket)
    {
        $sCancelBasketInformation = '';
        $sCancelBasketInformation .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.text_general_info')."\r\n";
        $sCancelBasketInformation .= "------------------------------------\r\n";
        $oCancelStep = TdbShopOrderStep::GetStep($oOrderBasket->fieldUpdateStepname);
        $sCancelBasketInformation .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.text_general_aborted', ['%step%' => $oCancelStep->fieldName])."\r\n";
        if (array_key_exists($oCancelStep->fieldName, $this->aCancelStep)) {
            ++$this->aCancelStep[$oCancelStep->fieldName];
        } else {
            $this->aCancelStep[$oCancelStep->fieldName] = 1;
        }

        return $sCancelBasketInformation;
    }

    /**
     * Get user statistics for canceled basket step.
     *
     * @param TdbDataExtranetUser $oUser
     *
     * @return string
     */
    protected function GetUserStatistics($oUser)
    {
        $sUserStatistics = '';
        $sUserStatistics .= "------------------------------------\r\n\r\n";
        $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.text_user')."\r\n";
        $sUserStatistics .= "------------------------------------\r\n";
        if (count($oUser->sqlData) > 0) {
            $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.user_name').': '.$oUser->fieldFirstname.' '.$oUser->fieldLastname." \r\n";
            $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.user_email').': '.$oUser->fieldName." \r\n";
            $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.user_street').': '.$oUser->fieldStreet.' '.$oUser->fieldStreetnr." \r\n";
            $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.user_zip').': '.$oUser->fieldPostalcode.' '.$oUser->fieldCity." \r\n";
            $oCountry = TdbDataCountry::GetNewInstance();
            if ($oCountry->Load($oUser->fieldDataCountryId)) {
                $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.user_country').': '.$oCountry->GetName()." \r\n";
            }
            $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.user_telefon').': '.$oUser->fieldTelefon." \r\n";
        } else {
            $sUserStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.guest_user')."\r\n";
        }

        return $sUserStatistics;
    }

    /**
     * Get basket statistics for canceled basket step.
     *
     * @param TShopBasket $oBasket
     *
     * @return string
     */
    protected function GetBasketStatistics($oBasket)
    {
        $basketStatistics = '';
        $basketStatistics .= "------------------------------------\r\n\r\n";
        $basketStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.basket_content')."\r\n";
        $basketStatistics .= "------------------------------------\r\n";
        $oArticleList = $oBasket->GetBasketContents();

        if (null === $oArticleList) {
            return $basketStatistics;
        }

        $oArticleList->GoToStart();
        while ($oArticle = $oArticleList->Next()) {
            $basketStatistics .= ServiceLocator::get('translator')->trans(
                'chameleon_system_shop.cron_send_basket_stats.basket_content_line',
                [
                    '%amount%' => '',
                    '%name%' => '',
                    '%price%' => '',
                    '%totalPrice%' => '',
                ]
            )."\r\n";
        }
        $basketStatistics .= ServiceLocator::get('translator')->trans('chameleon_system_shop.cron_send_basket_stats.basket_content_total', ['%basketTotal%' => $oBasket->dCostArticlesTotal])."\r\n";
        $this->sTotalMoneyCanceled += $oBasket->dCostArticlesTotal;
        $basketStatistics .= "------------------------------------\r\n";

        return $basketStatistics;
    }

    /**
     * Send complete statistic report to shop owner.
     *
     * @param string $sStatistics
     *
     * @return void
     */
    protected function SendBasketStatistics($sStatistics)
    {
        $oMail = TDataMailProfile::GetProfile(self::MAIL_SYSTEM_NAME);

        if (null === $oMail) {
            return;
        }

        $aMailData = [];
        $aMailData['sStatistics'] = $sStatistics;
        $oMail->AddDataArray($aMailData);
        $oMail->SendUsingObjectView('emails', 'Customer');
    }
}
