<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use esono\pkgCmsCache\CacheInterface;

class TPkgShopAffiliate extends TPkgShopAffiliateAutoParent
{
    public const SESSION_AFFILIATE_PROGRAM_CODE = 'TdbPkgShopAffiliate-data';
    public const COOKIE_NAME = 'chameleon-affiliate-marker';

    /** @var string|null */
    public $sCode;

    /**
     * return instance of class (casted as correct type.
     *
     * @param string $sId
     *
     * @return TdbPkgShopAffiliate
     */
    public static function GetInstance($sId)
    {
        $oElement = TdbPkgShopAffiliate::GetNewInstance();
        $oElement->Load($sId);
        if (!empty($oElement->sqlData['class'])) {
            $sClassName = $oElement->sqlData['class'];
            $oElementNew = new $sClassName();
            $oElementNew->LoadFromRow($oElement->sqlData);
            $oElement = $oElementNew;
        }

        return $oElement;
    }

    /**
     * if a paramter was passed (now or any time before in the session), return
     * the object for that paramter - else return null.
     *
     * @return TdbPkgShopAffiliate|null
     */
    public static function GetActiveInstance()
    {
        /** @var TdbPkgShopAffiliate|null $oInstance */
        static $oInstance = null;

        if (is_null($oInstance) && array_key_exists(TdbPkgShopAffiliate::SESSION_AFFILIATE_PROGRAM_CODE, $_SESSION)) {
            /**
             * @FIXME Setting `$oInstance = false` and immediately overriding it with a different value only confuses the typing in this method.
             */
            $oInstance = false;
            $aData = $_SESSION[TdbPkgShopAffiliate::SESSION_AFFILIATE_PROGRAM_CODE];

            /** @var TdbPkgShopAffiliate $oInstance */
            $oInstance = TdbPkgShopAffiliate::GetInstance($aData['id']);
            $oInstance->sCode = $aData['sCode'];
        }

        if (is_null($oInstance)) { // if is null try to load the tradedoubler affiliate
            $oTradeDoublerAffiliate = TdbPkgShopAffiliate::GetNewInstance();
            if ($oTradeDoublerAffiliate->LoadFromField('name', 'TradeDoubler')) {
                $oInstance = TdbPkgShopAffiliate::GetInstance($oTradeDoublerAffiliate->id);
                $oInstance->sCode = '';
            }
        }

        return $oInstance;
    }

    /**
     * checks for affiliate partner codes - if one is found, the info is stored to session
     * note: if data is already in the session, we overwrite it.
     *
     * @return bool
     */
    public static function ScanURLForAffiliateProgramCodes()
    {
        $bItemFound = false;
        $oGlobal = TGlobal::instance();
        $aParams = $oGlobal->GetUserData();
        if (count($aParams) > 0) { // only process if we have parameter
            $shop = self::getShopService()->getActiveShop();

            $cache = self::getCache();
            $sCacheKey = $cache->getKey([
                'class' => __CLASS__,
                'method' => 'ScanURLForAffiliateProgramCodes',
                'object' => 'GetFieldPkgShopAffiliateList',
                'shop' => $shop->id,
            ]);
            $oCodeList = $cache->get($sCacheKey);
            if (null === $oCodeList) {
                $oCodeList = $shop->GetFieldPkgShopAffiliateList();
                $oCodeList->bAllowItemCache = true;
                $i = 0;
                while ($oCode = $oCodeList->Next()) {
                    $i = $i + 1; // we use the loop to fill the cache - and need the code here to prevent it from being removed by the optimizer
                }

                $oCodeList->GoToStart();
                $aTrigger = [['table' => 'pkg_shop_affiliate', 'id' => null], ['table' => 'pkg_shop_affiliate_parameter', 'id' => null]];
                $cache->set($sCacheKey, $oCodeList, $aTrigger);
            }

            while (!$bItemFound && ($oCode = $oCodeList->Next())) {
                if (array_key_exists($oCode->fieldUrlParameterName, $aParams)) {
                    $bItemFound = true;
                    $sCode = $oGlobal->GetUserData($oCode->fieldUrlParameterName);
                    $_SESSION[TdbPkgShopAffiliate::SESSION_AFFILIATE_PROGRAM_CODE] = ['id' => $oCode->id, 'sCode' => $sCode];

                    // save info in cookie
                    if ($oCode->fieldNumberOfSecondsValid > 0) {
                        $iExpire = time() + $oCode->fieldNumberOfSecondsValid;
                        $sCookieData = self::getMarshalledAffiliateData($_SESSION[TdbPkgShopAffiliate::SESSION_AFFILIATE_PROGRAM_CODE]);
                        $sDomain = TCMSSmartURLData::GetActive()->sOriginalDomainName;
                        if ('www.' == substr($sDomain, 0, 4)) {
                            $sDomain = substr($sDomain, 4);
                        }
                        setcookie(TdbPkgShopAffiliate::COOKIE_NAME, $sCookieData, $iExpire, '/', '.'.$sDomain, false, true);
                    }

                    $oCode->FoundCodeHook($sCode);
                }
            }
        }

        if (!$bItemFound) {
            // see if the data was stored in a cookie - if so, use it
            if (is_array($_COOKIE) && array_key_exists(TdbPkgShopAffiliate::COOKIE_NAME, $_COOKIE)) {
                $tmp = self::getUnmarshalledAffiliateData($_COOKIE[TdbPkgShopAffiliate::COOKIE_NAME]);
                if (is_array($tmp) && array_key_exists('id', $tmp) && array_key_exists('sCode', $tmp)) {
                    $_SESSION[TdbPkgShopAffiliate::SESSION_AFFILIATE_PROGRAM_CODE] = $tmp;
                    $oCode = TdbPkgShopAffiliate::GetInstance($tmp['id']);
                    $oCode->FoundCodeHook($tmp['sCode'], true);
                    $bItemFound = true;
                }
            }
        }

        return $bItemFound;
    }

    /**
     * @return string
     */
    private static function getMarshalledAffiliateData(array $affiliateData)
    {
        return base64_encode(json_encode($affiliateData));
    }

    /**
     * @param string $affiliateData
     *
     * @return array|null
     */
    private static function getUnmarshalledAffiliateData($affiliateData)
    {
        return json_decode(base64_decode($affiliateData), true);
    }

    /**
     * hook is called when an affiliate code is found in the url parameters.
     *
     * @param string $sCode - code found
     * @param bool $bRestoredFromCookie - set to true, if the data was restored from a cookie
     *
     * @return void
     */
    public function FoundCodeHook($sCode, $bRestoredFromCookie = false)
    {
    }

    /**
     * render the HTML/JS Code of the affiliate program.
     *
     * @param TdbShopOrder $oOrder - the order id
     *
     * @return string
     */
    public function RenderHTMLCode($oOrder)
    {
        $aParameter = $oOrder->GetSQLWithTablePrefix($oOrder->table);

        $aParameter['dNetProductValue'] = $oOrder->GetNetProductValue();
        $aParameter['dNetProductAfterDiscountsAndVouchersValue'] = $aParameter['dNetProductValue'];

        $aParameter['sAffiliateCode'] = $this->sCode;
        $oParamList = $this->GetFieldPkgShopAffiliateParameterList();
        while ($oParam = $oParamList->Next()) {
            $aParameter[$oParam->fieldName] = $oParam->fieldValue;
        }
        $this->GetAdditionalViewVariables($oOrder, $aParameter);

        $stringReplacer = new TPkgCmsStringUtilities_VariableInjection();

        return $stringReplacer->replace($this->fieldOrderSuccessCode, $aParameter);
    }

    /**
     * call this method in the order success view to generate the success html for the partner program.
     *
     * @return string
     */
    public static function RenderOrderSuccessHTMLCode()
    {
        $sHTML = '';
        $oUserOrder = TShopBasket::GetLastCreatedOrder();
        $oProgram = TdbPkgShopAffiliate::GetActiveInstance();
        if ($oUserOrder && $oProgram) {
            $sHTML = $oProgram->RenderHTMLCode($oUserOrder);
        }

        return $sHTML;
    }

    /**
     * add custom vars to the view.
     *
     * @param TdbShopOrder $oOrder
     * @param array $aParameter
     *
     * @return void
     */
    protected function GetAdditionalViewVariables($oOrder, &$aParameter)
    {
    }

    /**
     * @return ShopServiceInterface
     */
    private static function getShopService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return CacheInterface
     */
    private static function getCache()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.cache');
    }
}
