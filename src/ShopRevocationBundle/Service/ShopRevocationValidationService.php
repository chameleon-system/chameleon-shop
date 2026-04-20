<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopRevocationBundle\DataModel\OrderValidationOutcomeDataModel;
use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationFormDataModel;
use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationFormErrorsDataModel;
use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationValidationResultDataModel;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShopRevocationValidationService
{
    private const TRANSLATION_PREFIX = 'chameleon_system_shop_revocation.';

    public function __construct(
        private readonly \TTools $tools,
        private readonly ShopRevocationOrderRelevanceService $orderRelevanceService,
        private readonly ShopServiceInterface $shopService,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function validate(RevocationFormDataModel $formData, ?string $activeExtranetUserId): RevocationValidationResultDataModel
    {
        $isLoggedIn = null !== $activeExtranetUserId && '' !== $activeExtranetUserId;
        $errors = $this->getEmptyErrors();

        $this->validateMandatoryFields($formData, $errors, $isLoggedIn);
        $this->validateLegalConsent($formData, $errors);
        $this->validateEmail($formData, $errors);
        $orderValidationOutcome = $this->validateOrder($formData, $errors, $activeExtranetUserId);

        return new RevocationValidationResultDataModel(
            $errors,
            $orderValidationOutcome->shouldShowInvalidOrderHint(),
            $orderValidationOutcome->getResolvedOrder()
        );
    }

    public function getEmptyErrors(): RevocationFormErrorsDataModel
    {
        return new RevocationFormErrorsDataModel();
    }

    private function validateMandatoryFields(RevocationFormDataModel $formData, RevocationFormErrorsDataModel $errors, bool $isLoggedIn): void
    {
        if ('' === $formData->getFirstName()) {
            $errors->setFirstName($this->trans('validation.first_name_required'));
        }

        if ('' === $formData->getLastName()) {
            $errors->setLastName($this->trans('validation.last_name_required'));
        }

        if ('' === $formData->getEmail()) {
            $errors->setEmail($this->trans('validation.email_required'));
        }

        if (true === $isLoggedIn) {
            if ('' === $formData->getShopOrderId() && '' === $formData->getOrderNumber()) {
                $errors->setShopOrderId($this->trans('validation.order_selection_required'));
            }

            return;
        }

        if ('' === $formData->getOrderNumber()) {
            $errors->setOrderNumber($this->trans('validation.order_number_required'));
        }
    }

    private function validateLegalConsent(RevocationFormDataModel $formData, RevocationFormErrorsDataModel $errors): void
    {
        if (true === $formData->hasLegalConsent()) {
            return;
        }

        $errors->setLegalConsent($this->trans('validation.legal_consent_required'));
    }

    private function validateEmail(RevocationFormDataModel $formData, RevocationFormErrorsDataModel $errors): void
    {
        $email = $formData->getEmail();
        if ('' !== $email && false === $this->tools->IsValidEMail($email)) {
            $errors->setEmail($this->trans('validation.email_invalid'));
        }
    }

    private function validateOrder(RevocationFormDataModel $formData, RevocationFormErrorsDataModel $errors, ?string $activeExtranetUserId): OrderValidationOutcomeDataModel
    {
        $isLoggedIn = null !== $activeExtranetUserId && '' !== $activeExtranetUserId;

        if (true === $isLoggedIn) {
            if (null !== $errors->getShopOrderId()) {
                return $this->failedValidationResult();
            }

            if ('' !== $formData->getShopOrderId()) {
                return $this->validateLoggedInSelectedOrder($formData, $errors, $activeExtranetUserId);
            }

            if ('' !== $formData->getOrderNumber()) {
                return $this->validateLoggedInFallbackOrderNumber($formData, $errors, $activeExtranetUserId);
            }

            return $this->failedValidationResult();
        }

        if (null !== $errors->getOrderNumber()) {
            return $this->failedValidationResult();
        }

        if (null !== $errors->getEmail()) {
            return $this->failedValidationResult();
        }

        $order = $this->loadOrderByNumber($formData->getOrderNumber());
        if (null === $order || false === $this->orderBelongsToActiveShop($order)) {
            $errors->setOrderNumber($this->trans('validation.order_number_not_found'));

            return $this->failedValidationResult(true);
        }

        if (0 !== strcasecmp((string) $order->fieldUserEmail, $formData->getEmail())) {
            $errors->setEmail($this->trans('validation.email_order_mismatch'));

            return $this->failedValidationResult(true);
        }

        if (false === $this->orderRelevanceService->isOrderRelevantForRevocation($order)) {
            $errors->setOrderNumber($this->trans('validation.order_number_not_relevant'));

            return $this->failedValidationResult(true);
        }

        return $this->successfulValidationResult($order);
    }

    private function validateLoggedInSelectedOrder(RevocationFormDataModel $formData, RevocationFormErrorsDataModel $errors, string $activeExtranetUserId): OrderValidationOutcomeDataModel
    {
        $order = $this->loadOrderById($formData->getShopOrderId());
        if (null === $order || false === $this->orderBelongsToActiveShop($order)) {
            $errors->setShopOrderId($this->trans('validation.selected_order_not_found'));

            return $this->failedValidationResult(true);
        }

        if ((string) $order->fieldDataExtranetUserId !== $activeExtranetUserId) {
            $errors->setShopOrderId($this->trans('validation.selected_order_not_owned'));

            return $this->failedValidationResult(true);
        }

        if (0 !== strcasecmp((string) $order->fieldUserEmail, $formData->getEmail())) {
            $errors->setEmail($this->trans('validation.email_order_mismatch'));

            return $this->failedValidationResult(true);
        }

        if (false === $this->orderRelevanceService->isOrderRelevantForRevocation($order)) {
            $errors->setShopOrderId($this->trans('validation.selected_order_not_relevant'));

            return $this->failedValidationResult(true);
        }

        return $this->successfulValidationResult($order);
    }

    private function validateLoggedInFallbackOrderNumber(RevocationFormDataModel $formData, RevocationFormErrorsDataModel $errors, string $activeExtranetUserId): OrderValidationOutcomeDataModel
    {
        if (null !== $errors->getEmail()) {
            return $this->failedValidationResult();
        }

        $order = $this->loadOrderByNumber($formData->getOrderNumber());
        if (null === $order || false === $this->orderBelongsToActiveShop($order)) {
            $errors->setOrderNumber($this->trans('validation.order_number_not_found'));

            return $this->failedValidationResult(true);
        }

        if ((string) $order->fieldDataExtranetUserId !== $activeExtranetUserId) {
            $errors->setOrderNumber($this->trans('validation.order_number_not_owned'));

            return $this->failedValidationResult(true);
        }

        if (0 !== strcasecmp((string) $order->fieldUserEmail, $formData->getEmail())) {
            $errors->setEmail($this->trans('validation.email_order_mismatch'));

            return $this->failedValidationResult(true);
        }

        if (false === $this->orderRelevanceService->isOrderRelevantForRevocation($order)) {
            $errors->setOrderNumber($this->trans('validation.order_number_not_relevant'));

            return $this->failedValidationResult(true);
        }

        return $this->successfulValidationResult($order);
    }

    private function orderBelongsToActiveShop(\TShopOrder $order): bool
    {
        $activeShop = $this->shopService->getActiveShop();

        return (string) $order->fieldShopId === (string) $activeShop->id;
    }

    private function loadOrderById(string $orderId): ?\TShopOrder
    {
        $order = new \TShopOrder();
        if (false === $order->Load($orderId)) {
            return null;
        }

        return $order;
    }

    private function loadOrderByNumber(string $orderNumber): ?\TShopOrder
    {
        $order = new \TShopOrder();
        if (false === $order->LoadFromField('ordernumber', $orderNumber)) {
            return null;
        }

        return $order;
    }

    private function trans(string $key, array $parameters = []): string
    {
        return $this->translator->trans(self::TRANSLATION_PREFIX.$key, $parameters);
    }

    private function failedValidationResult(bool $showInvalidOrderHint = false): OrderValidationOutcomeDataModel
    {
        return new OrderValidationOutcomeDataModel($showInvalidOrderHint);
    }

    private function successfulValidationResult(\TShopOrder $order): OrderValidationOutcomeDataModel
    {
        return new OrderValidationOutcomeDataModel(false, $order);
    }
}
