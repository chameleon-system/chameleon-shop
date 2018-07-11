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
use ChameleonSystem\ShopCurrencyBundle\Event\CurrencyChangedEvent;
use ChameleonSystem\ShopCurrencyBundle\Interfaces\ShopCurrencyServiceInterface;
use ChameleonSystem\ShopCurrencyBundle\ShopCurrencyEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class TPkgShopCurrency extends TPkgShopCurrencyAutoParent
{
    const SESSION_NAME = 'esono/pkgCurrency/activeCurrencyId';

    /**
     * format the double value as a string. includes the currency symbol as part of the response.
     * note: will not do any conversion (we assume that dValue is already in the correct currency).
     *
     * @param float $dValue
     *
     * @return string
     */
    public function GetFormattedCurrency($dValue)
    {
        $oLocal = &TCMSLocal::GetActive();
        $sValue = $oLocal->FormatNumber($dValue, 2).' '.$this->GetCurrencyDisplaySymbol();

        return $sValue;
    }

    /**
     * return the ISO-4217 code for the currency.
     *
     * @return null|string
     */
    public function getISO4217Code()
    {
        return $this->fieldIso4217;
    }

    /**
     * return the symbol used to mark the currency in the shop.
     *
     * @return string
     */
    public function GetCurrencyDisplaySymbol()
    {
        return $this->fieldSymbol;
    }

    /**
     * return the active currency. Currency is set defined via
     *   - user session
     *   - user cookie
     *   - user profile
     * note: passing bReset will reset and return NOTHING.
     *
     * @static
     *
     * @return TdbPkgShopCurrency
     *
     * @deprecated use the service chameleon_system_shop_currency.shop_currency instead
     */
    public static function GetActiveInstance($bReset = false)
    {
        /** @var $currencyService ShopCurrencyServiceInterface */
        $currencyService = ServiceLocator::get('chameleon_system_shop_currency.shop_currency');
        if ($bReset) {
            $currencyService->reset();

            return false;
        }

        return $currencyService->getObject();
    }

    /**
     * return the default currency (set via shop.default_pkg_shop_currency_id)
     * we use the method here because a)it is easy to remember, b) we can cache the object.
     *
     * @static
     *
     * @return TdbPkgShopCurrency
     */
    public static function GetDefaultCurrency()
    {
        static $oInstance = null;
        if (is_null($oInstance)) {
            $oShop = TdbShop::GetInstance();
            $oInstance = $oShop->GetFieldDefaultPkgShopCurrency();
            // none set? just take the first one
            if (!$oInstance) {
                $oList = TdbPkgShopCurrencyList::GetList();
                $oList->GoToStart();
                $oInstance = $oList->Current();
            }
        }

        return $oInstance;
    }

    /**
     * return the base currency relative to which all other currencies are calculated.
     *
     * @return TdbPkgShopCurrency
     */
    public static function GetBaseCurrency()
    {
        static $oInstance = null;
        if (null === $oInstance) {
            $oInstance = TdbPkgShopCurrency::GetNewInstance();
            if (false === $oInstance->LoadFromField('is_base_currency', '1')) {
                $oInstance = false;
            }
        }

        return $oInstance;
    }

    /**
     * return the active currency based on session, cookie or user
     * note: if the currency is set via cookie, then we auto-update the user object as well (WITHOUT SAVING IT).
     *
     * @static
     *
     * @param bool $bUseDefaultIfNotDefinedForUser - only checks session and cookie - will not
     *
     * @return string
     *
     * @deprecated use chameleon_system_shop_currency.shop_currency instead
     */
    public static function GetActiveCurrencyId($bUseDefaultIfNotDefinedForUser = true)
    {
        /** @var $currencyService ShopCurrencyServiceInterface */
        $currencyService = ServiceLocator::get('chameleon_system_shop_currency.shop_currency');

        return $currencyService->getActiveCurrencyId($bUseDefaultIfNotDefinedForUser);
    }

    /**
     * returns true, if the user selected a currency (ie. the currency was not selected via default).
     *
     * @static
     *
     * @return bool
     */
    public static function ActiveUserSelectedACurrency()
    {
        $bSelected = false;
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oUser && is_array($oUser->sqlData) && array_key_exists('pkg_shop_currency_id', $oUser->sqlData) && !empty($oUser->sqlData['pkg_shop_currency_id'])) {
            $bSelected = true;
        }

        return $bSelected;
    }

    /**
     * set this currency object as the active object.
     */
    public function SetAsActive()
    {
        $sCurrencyId = $this->id;

        /** @var Request $request */
        $request = ServiceLocator::get('request_stack')->getCurrentRequest();
        $request->getSession()->set(TdbPkgShopCurrency::SESSION_NAME, $sCurrencyId);

        $sDomain = $request->getHost();
        if ('www.' == substr($sDomain, 0, 4)) {
            $sDomain = substr($sDomain, 4);
        }
        setcookie(TdbPkgShopCurrency::SESSION_NAME, base64_encode($sCurrencyId), time() + 60 * 60 * 24 * 365, '/', '.'.$sDomain, false, true);
        $oUser = TdbDataExtranetUser::GetInstance();

        if (!is_null($oUser->id) && !empty($oUser->id) && array_key_exists('pkg_shop_currency_id', $oUser->sqlData) && $oUser->sqlData['pkg_shop_currency_id'] != $sCurrencyId && $oUser->IsLoggedIn()) {
            $oUser->SaveFieldsFast(array('pkg_shop_currency_id' => $sCurrencyId));
        } else {
            $oUser->sqlData['pkg_shop_currency_id'] = $sCurrencyId;
            $oUser->fieldPkgShopCurrencyId = $sCurrencyId;
        }
        TdbPkgShopCurrency::GetActiveInstance(true);

        $this->getEventDispatcher()->dispatch(
            ShopCurrencyEvents::CURRENCY_CHANGED,
            new CurrencyChangedEvent($sCurrencyId)
        );
        // also mark basket as "requires recalculation"
        $oBasket = TShopBasket::GetInstance();
        $oBasket->SetBasketRecalculationFlag(true);
    }

    /**
     * convert a currency relative to a base currency. if none is passed, we assume the base currency is passed
     * IMPORTANT: Result is NOT ROUNDED!
     *
     * @param float              $dValue
     * @param TdbPkgShopCurrency $oBaseCurrency
     *
     * @return float
     */
    public function Convert($dValue, $oBaseCurrency = null)
    {
        if (null == $oBaseCurrency) {
            $oBaseCurrency = TdbPkgShopCurrency::GetBaseCurrency();
        }
        $oDefaultCurrency = TdbPkgShopCurrency::GetBaseCurrency();

        $dNewValue = $dValue;
        // we need to convert to default first (if they are not the same
        if ($oDefaultCurrency && $oBaseCurrency && $oBaseCurrency->id != $oDefaultCurrency->id) {
            $dNewValue = $dNewValue * $oBaseCurrency->fieldFactor;
        }

        // now convert the value to this currency
        $dNewValue = $dNewValue / $this->fieldFactor;

        return $dNewValue;
    }

    /**
     * provides a short-cut to the standard way of converting currency (getting an instance and calling convert)
     * returns the original value if no currency is set
     * IMPORTANT: Result is ROUNDED!
     *
     * @static
     *
     * @param float $dValue
     *
     * @return float
     */
    public static function ConvertToActiveCurrency($dValue)
    {
        $oActiveCurrency = TdbPkgShopCurrency::GetActiveInstance();
        if ($oActiveCurrency) {
            $dValue = round($oActiveCurrency->Convert($dValue), 2);
        }

        return $dValue;
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        return ServiceLocator::get('event_dispatcher');
    }
}
