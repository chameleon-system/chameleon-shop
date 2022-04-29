<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopCurrencyBundle\Service;

use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\ShopCurrencyBundle\Interfaces\ShopCurrencyServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use TdbPkgShopCurrency;

class ShopCurrencyService implements ShopCurrencyServiceInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ExtranetUserProviderInterface
     */
    private $extranetUserProvider;

    public function __construct(RequestStack $requestStack, ExtranetUserProviderInterface $extranetUserProvider)
    {
        $this->requestStack = $requestStack;
        $this->extranetUserProvider = $extranetUserProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getSymbol()
    {
        return $this->getObject()->GetCurrencyDisplaySymbol();
    }

    /**
     * {@inheritdoc}
     */
    public function getIso4217Code()
    {
        return $this->getObject()->getISO4217Code();
    }

    /**
     * {@inheritdoc}
     */
    public function formatNumber($value)
    {
        return $this->getObject()->GetFormattedCurrency($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getObject()
    {
        $activeCurrencyId = $this->getActiveCurrencyId();
        if (null === $activeCurrencyId || $activeCurrencyId === TdbPkgShopCurrency::GetDefaultCurrency()->id) {
            return TdbPkgShopCurrency::GetDefaultCurrency();
        }

        $activeCurrency = TdbPkgShopCurrency::GetNewInstance();
        if (false === $activeCurrency->Load($activeCurrencyId)) {
            $activeCurrency = TdbPkgShopCurrency::GetDefaultCurrency();
            trigger_error('trying to get active currency, but requested currency id does not exists. loading default currency', E_USER_WARNING);
        }

        return $activeCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        // in the past there was built-in cache logic in this class - moved to ShopCurrencyServiceRequestLevelCacheDecorator.
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveCurrencyId($bUseDefaultIfNotDefinedForUser = true)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (false === $request->hasSession() || false === $request->getSession()->isStarted()) {
            return null;
        }

        $sActiveId = null;
        // try user
        if (null === $sActiveId) {
            $oUser = $this->extranetUserProvider->getActiveUser();
            if ($oUser && is_array($oUser->sqlData) && isset($oUser->sqlData['pkg_shop_currency_id']) && !empty($oUser->sqlData['pkg_shop_currency_id'])) {
                $sActiveId = $oUser->sqlData['pkg_shop_currency_id'];
            }
        }

        // try cookie
        if (null === $sActiveId) {
            if (true === $request->cookies->has(TdbPkgShopCurrency::SESSION_NAME)) {
                $sActiveId = base64_decode($request->cookies->get(TdbPkgShopCurrency::SESSION_NAME));
                if (empty($sActiveId)) {
                    $sActiveId = null;
                } else {
                    // recovered from cookie - restore setting
                    $oUser = $this->extranetUserProvider->getActiveUser();
                    if ($oUser && is_array($oUser->sqlData)) {
                        $oUser->sqlData['pkg_shop_currency_id'] = $sActiveId;
                        $oUser->fieldPkgShopCurrencyId = $sActiveId;
                    }
                    $request->getSession()->set(TdbPkgShopCurrency::SESSION_NAME, $sActiveId);
                }
            }
        }

        if (null === $sActiveId && $bUseDefaultIfNotDefinedForUser) {
            $oDefaultCurrency = TdbPkgShopCurrency::GetDefaultCurrency();
            if ($oDefaultCurrency) {
                $sActiveId = $oDefaultCurrency->id;
            }
        }

        return $sActiveId;
    }
}
