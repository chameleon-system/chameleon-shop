<?php

namespace ChameleonSystem\ShopCurrencyBundle\Bridge\Chameleon\Objects;

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopCurrencyBundle\Interfaces\ShopCurrencyServiceInterface;

class CurrencyBasket extends \ChameleonSystemShopCurrencyBundleBridgeChameleonObjectsCurrencyBasketAutoParent
{
    /**
     * Reload active set payment method on currency change.
     * So payment method holds correct price for currency.
     *
     * {@inheritdoc}
     */
    public function SetActivePaymentMethod($oShopPayment)
    {
        $activePaymentMethod = $this->GetActivePaymentMethod();
        $isPaymentSet = parent::SetActivePaymentMethod($oShopPayment);
        $newActivePaymentMethod = $this->GetActivePaymentMethod();
        if (null === $activePaymentMethod || null === $newActivePaymentMethod) {
            return $isPaymentSet;
        }
        if (false === $activePaymentMethod->IsSameAs($newActivePaymentMethod)) {
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
