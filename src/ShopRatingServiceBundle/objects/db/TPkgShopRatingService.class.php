<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopRatingService extends TPkgShopRatingServiceAutoParent
{
    /**
     * return the rating service via system name.
     *
     * @static
     *
     * @param string $sSystemName
     *
     * @return TdbPkgShopRatingService|null
     */
    public static function GetInstanceFromSystemName($sSystemName)
    {
        $sQuery = "SELECT * FROM `pkg_shop_rating_service` WHERE `system_name` = '".MySqlLegacySupport::getInstance()->real_escape_string($sSystemName)."'";
        $aData = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($sQuery));
        if (is_array($aData)) {
            $oInstance = TdbPkgShopRatingService::GetNewInstance($aData); // need to do this to morph to the correct subclass
        } else {
            $oInstance = null;
        }

        return $oInstance;
    }

    /**
     * Return new instance of "row-object".
     *
     * factory creates a new instance and returns it.
     *
     * @param string|array $sData     - either the id of the object to load, or the row with which the instance should be initialized
     * @param string       $sLanguage - init with the language passed
     *
     * @return TdbPkgShopRatingService     */
    public static function GetNewInstance($sData = null, $sLanguage = null)
    {
        $oObject = parent::GetNewInstance($sData, $sLanguage);

        if ($oObject && !empty($oObject->fieldClass)) {
            $aData = $oObject->sqlData;
            $sClassName = $aData['class'];
            $oObject = new $sClassName();
            /** @var $oInterface TCMSInterfaceManagerBase */
            $oObject->LoadFromRow($aData);
        }

        return $oObject;
    }

    /**
     * Render rating-widget for active rating-service.
     *
     * @param string $sViewName
     * @param string $sViewSubType
     * @param string $sViewType
     * @param null   $sSpotName
     * @param array  $aCallTimeVars
     *
     * @return string
     */
    public function Render($sViewName = 'RatingService_standard', $sViewSubType = 'pkgShopRatingService/views', $sViewType = 'Customer', $sSpotName = null, $aCallTimeVars = array())
    {
        $sHTML = '';
        $oView = new TViewParser();
        /** @var $oView TViewParser */

        //create view name for this "active" rating-service
        if (!empty($this->fieldAffiliateValue)) {
            $sViewName = $sViewName.'_'.trim($this->fieldAffiliateValue);
        }

        foreach ($aCallTimeVars as $sKeyName => $sValue) {
            if (!empty($sKeyName)) {
                $oView->AddVar($sKeyName, $sValue);
            }
        }

        $sHTML .= $oView->RenderObjectPackageView($sViewName, $sViewSubType, $sViewType);

        return $sHTML;
    }

    /**
     * Import all ratings.
     *
     * @return bool
     */
    public function Import()
    {
        return false;
    }

    /**
     * Update main score value of rating service - calculated from `pkg_shop_rating_service_rating`.`score`.
     *
     * @return bool
     */
    protected function UpdateMainScroeValue()
    {
        $bRet = false;
        $sQuery = " SELECT AVG( `score` ) AS main_score FROM `pkg_shop_rating_service_rating` WHERE `pkg_shop_rating_service_id` = '".$this->id."' ";
        $rs = MySqlLegacySupport::getInstance()->query($sQuery);
        if ($rs) {
            $oAVG = MySqlLegacySupport::getInstance()->fetch_object($rs);
        }

        //Update value
        if ($oAVG->main_score > 0) {
            $sQuery = "UPDATE pkg_shop_rating_service SET current_rating = '".$oAVG->main_score."', current_rating_date = NOW() WHERE id = '".$this->id."' ";
            MySqlLegacySupport::getInstance()->query($sQuery);
            $bRet = $oAVG->main_score;
        }

        return $bRet;
    }

    /**
     * Get Rating-Platform link.
     *
     * @return string
     */
    public function GetLinkRating()
    {
        $sRetLink = '';
        if (!empty($this->fieldRatingUrl)) {
            $sRetLink = $this->fieldRatingUrl;
        }

        return $sRetLink;
    }

    /**
     * Get link to users rating-page.
     *
     * @return string
     */
    public function GetLinkInfoPage()
    {
        $sRetLink = '';
        if (!empty($this->fieldRatingUserinfoUrl)) {
            $sRetLink = $this->fieldRatingUserinfoUrl;
        }

        return $sRetLink;
    }

    /**
     * @param array<string, mixed> $aOrder
     * @param TdbDataExtranetUser $oUser
     * @return bool
     */
    public function SendShopRatingEmail($oUser, $aOrder)
    {
        $oMailProfile = TdbDataMailProfile::GetProfile('shop_rating_request');

        $sSalutationName = '';
        $oSalutation = TdbDataExtranetSalutation::GetNewInstance();
        if ($oSalutation->Load($aOrder['adr_billing_salutation_id'])) {
            $sSalutationName = $oSalutation->fieldName;
        }
        $oMailProfile->AddData('adr_billing_salutation', $sSalutationName);

        $oMailProfile->AddDataArray($aOrder);
        $oMailProfile->AddData('order_number', $aOrder['ordernumber']);

        $oMailProfile->AddData('rating_service_name', $this->fieldName);
        $oMailProfile->AddData('rating_service_url', $this->fieldRatingUrl);
        $oMailProfile->AddData('rating_service_email_text', $this->fieldEmailText);

        $oShop = TdbShop::GetInstance();
        $aShopData = $oShop->GetSQLWithTablePrefix();
        $oMailProfile->AddDataArray($aShopData);
        $oMailProfile->AddData('shop_name', $oShop->fieldName);

        $oMailProfile->ChangeToAddress($aOrder['user_email'], $aOrder['adr_billing_firstname'].' '.$aOrder['adr_billing_lastname']);

        return $oMailProfile->SendUsingObjectView('emails', 'Customer');
    }
}
