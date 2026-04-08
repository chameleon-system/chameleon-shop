<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class OrderValidationOutcomeDataModel
{
    public function __construct(
        private readonly bool $showInvalidOrderHint = false,
        private readonly ?\TShopOrder $resolvedOrder = null
    ) {
    }

    public function shouldShowInvalidOrderHint(): bool
    {
        return $this->showInvalidOrderHint;
    }

    public function getResolvedOrder(): ?\TShopOrder
    {
        return $this->resolvedOrder;
    }
}
