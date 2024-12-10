<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopCurrencyBundle\Bridge\Chameleon\Objects;

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopCurrencyBundle\Interfaces\ShopCurrencyServiceInterface;

class CurrencyBasket extends \ChameleonSystemShopCurrencyBundleBridgeChameleonObjectsCurrencyBasketAutoParent
{
    /**
     * Reloads the active payment method on currency change,
     * so that the payment method holds the payment charges in the correct currency.
     *
     * {@inheritdoc}
     */
    public function SetActivePaymentMethod($oShopPayment): bool
    {
        $oldActivePaymentMethod = $this->GetActivePaymentMethod();
        $isPaymentSet = parent::SetActivePaymentMethod($oShopPayment);
        $newActivePaymentMethod = $this->GetActivePaymentMethod();
        if (null === $oldActivePaymentMethod || null === $newActivePaymentMethod) {
            return $isPaymentSet;
        }
        if (false === $oldActivePaymentMethod->IsSameAs($newActivePaymentMethod)) {
            return $isPaymentSet;
        }
        $activeCurrency = $this->getCurrencyService()->getObject();

        if (null === $newActivePaymentMethod->fieldValueOriginal && true == $activeCurrency->fieldIsBaseCurrency) {
            return $isPaymentSet;
        }

        if (null !== $newActivePaymentMethod->fieldValueOriginal) {
            $convertedValue = \TdbPkgShopCurrency::ConvertToActiveCurrency($newActivePaymentMethod->fieldValueOriginal);
            if ($convertedValue === $newActivePaymentMethod->GetPrice()) {
                return $isPaymentSet;
            }
        }

        $reloadedPayment = \TdbShopPaymentMethod::GetNewInstance($newActivePaymentMethod->id);
        parent::SetActivePaymentMethod($reloadedPayment);

        return $isPaymentSet;
    }

    private function getCurrencyService(): ShopCurrencyServiceInterface
    {
        return ServiceLocator::get('chameleon_system_shop_currency.shop_currency');
    }
}
