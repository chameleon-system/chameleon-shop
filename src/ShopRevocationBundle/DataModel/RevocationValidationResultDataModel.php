<?php

namespace ChameleonSystem\ShopRevocationBundle\DataModel;

class RevocationValidationResultDataModel
{
    public function __construct(
        private readonly RevocationFormErrorsDataModel $errors,
        private readonly bool $showInvalidOrderHint = false,
        private readonly ?\TShopOrder $resolvedOrder = null
    ) {
    }

    public function getErrors(): RevocationFormErrorsDataModel
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors->hasErrors();
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
