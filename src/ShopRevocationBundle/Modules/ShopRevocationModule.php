<?php

namespace ChameleonSystem\ShopRevocationBundle\Modules;

use ChameleonSystem\CoreBundle\Interfaces\FlashMessageServiceInterface;
use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationFormDataModel;
use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationFormErrorsDataModel;
use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationMailDataModel;
use ChameleonSystem\ShopRevocationBundle\Service\ShopRevocationMailService;
use ChameleonSystem\ShopRevocationBundle\Service\ShopRevocationOrderLinkService;
use ChameleonSystem\ShopRevocationBundle\Service\ShopRevocationOrderSelectionService;
use ChameleonSystem\ShopRevocationBundle\Service\ShopRevocationPersistenceService;
use ChameleonSystem\ShopRevocationBundle\Service\ShopRevocationValidationService;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShopRevocationModule extends \MTPkgViewRendererAbstractModuleMapper
{
    private const MESSAGE_CONSUMER = 'shop_revocation_module';
    private const DEFAULT_SUBMIT_FUNCTION = 'submitRevocation';
    private const CONTACT_SYSTEM_PAGE = 'contactPage';
    private const TRANSLATION_PREFIX = 'chameleon_system_shop_revocation.';

    private ExtranetUserProviderInterface $extranetUserProvider;

    private FlashMessageServiceInterface $flashMessageService;

    private InputFilterUtilInterface $inputFilterUtil;

    private ShopServiceInterface $shopService;

    private ShopRevocationOrderSelectionService $orderSelectionService;

    private ShopRevocationValidationService $validationService;

    private RevocationFormDataModel $formData;

    private RevocationFormErrorsDataModel $errors;

    private ?string $generalError = null;

    private ?string $successMessage = null;

    private ?string $invalidOrderHint = null;

    private ?string $contactPageUrl = null;

    public function __construct(
        ExtranetUserProviderInterface $extranetUserProvider,
        FlashMessageServiceInterface $flashMessageService,
        InputFilterUtilInterface $inputFilterUtil,
        ShopServiceInterface $shopService,
        ShopRevocationOrderSelectionService $orderSelectionService,
        ShopRevocationValidationService $validationService,
        private readonly ShopRevocationPersistenceService $persistenceService,
        private readonly ShopRevocationOrderLinkService $orderLinkService,
        private readonly ShopRevocationMailService $mailService,
        private readonly SystemPageServiceInterface $systemPageService,
        private readonly TranslatorInterface $translator
    ) {
        parent::__construct();

        $this->extranetUserProvider = $extranetUserProvider;
        $this->flashMessageService = $flashMessageService;
        $this->inputFilterUtil = $inputFilterUtil;
        $this->shopService = $shopService;
        $this->orderSelectionService = $orderSelectionService;
        $this->validationService = $validationService;
        $this->formData = new RevocationFormDataModel();
        $this->errors = new RevocationFormErrorsDataModel();
    }

    public function Accept(
        \IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        \IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        $activeUser = $this->extranetUserProvider->getActiveUser();
        $isLoggedIn = null !== $activeUser && true === $activeUser->IsLoggedIn();
        $orders = [];
        $showLoggedInOrderNumberFallback = false;

        if (true === $isLoggedIn) {
            if ('' === $this->formData->getName()) {
                $this->formData->setName(trim(sprintf('%s %s', (string) $activeUser->fieldFirstname, (string) $activeUser->fieldLastname)));
            }
            if ('' === $this->formData->getEmail()) {
                $this->formData->setEmail((string) $activeUser->fieldEmail);
            }

            $activeShop = $this->shopService->getActiveShop();
            $orders = $this->orderSelectionService->getSelectableOrders((string) $activeShop->id, (string) $activeUser->id);
            $showLoggedInOrderNumberFallback = 0 === \count($orders);
        }

        $oVisitor->SetMappedValue('moduleSpotName', $this->sModuleSpotName);
        $oVisitor->SetMappedValue('submitFunction', self::DEFAULT_SUBMIT_FUNCTION);
        $oVisitor->SetMappedValue('messages', $this->flashMessageService->renderMessages(self::MESSAGE_CONSUMER));
        $oVisitor->SetMappedValue('generalError', $this->generalError);
        $oVisitor->SetMappedValue('successMessage', $this->successMessage);
        $oVisitor->SetMappedValue('isLoggedIn', $isLoggedIn);
        $oVisitor->SetMappedValue('formValues', $this->formData->toArray());
        $oVisitor->SetMappedValue('errors', $this->errors->toArray());
        $oVisitor->SetMappedValue('orders', $orders);
        $oVisitor->SetMappedValue('hiddenFields', []);
        $oVisitor->SetMappedValue('showLoggedInOrderNumberFallback', $showLoggedInOrderNumberFallback);
        $oVisitor->SetMappedValue('loggedInOrderNumberFallbackHint', true === $showLoggedInOrderNumberFallback ? $this->trans('hint.no_relevant_orders') : null);
        $oVisitor->SetMappedValue('invalidOrderHint', $this->invalidOrderHint);
        $oVisitor->SetMappedValue('contactPageUrl', $this->contactPageUrl);
        $oVisitor->SetMappedValue('showRequiredFieldsHint', true);
    }

    /**
     * {@inheritdoc}
     */
    public function _AllowCache()
    {
        return false;
    }

    /**
     * Validates the submitted revocation and persists it on success.
     */
    public function submitRevocation(): void
    {
        $activeUser = $this->extranetUserProvider->getActiveUser();
        $activeExtranetUserId = null;
        if (null !== $activeUser && true === $activeUser->IsLoggedIn()) {
            $activeExtranetUserId = (string) $activeUser->id;
        }

        $this->formData = new RevocationFormDataModel(
            trim((string) $this->inputFilterUtil->getFilteredPostInput('name', '', false, 'TCMSUserInput_SafeTextBlock')),
            trim((string) $this->inputFilterUtil->getFilteredPostInput('email', '')),
            trim((string) $this->inputFilterUtil->getFilteredPostInput('ordernumber', '')),
            trim((string) $this->inputFilterUtil->getFilteredPostInput('shopOrderId', '')),
            trim((string) $this->inputFilterUtil->getFilteredPostInput('customerNote', '', false, 'TCMSUserInput_SafeTextBlock'))
        );

        $this->errors = $this->validationService->getEmptyErrors();
        $this->generalError = null;
        $this->successMessage = null;
        $this->invalidOrderHint = null;
        $this->contactPageUrl = null;

        $validationResult = $this->validationService->validate($this->formData, $activeExtranetUserId);
        $this->errors = $validationResult->getErrors();

        if (true === $validationResult->hasErrors()) {
            $this->generalError = $this->errors->getGeneral() ?? $this->trans('error.check_input');

            if (true === $validationResult->shouldShowInvalidOrderHint()) {
                $this->invalidOrderHint = $this->trans('hint.invalid_order');
                $this->contactPageUrl = $this->getContactPageUrl();
            }

            return;
        }

        $resolvedOrder = $validationResult->getResolvedOrder();
        if (null === $resolvedOrder) {
            $this->generalError = $this->trans('error.save_failed');

            return;
        }

        try {
            $submittedAt = new \DateTimeImmutable();
            $revocationRecordId = $this->persistenceService->createRevocation($this->formData, $resolvedOrder, $submittedAt);
            $this->orderLinkService->linkRevocationToOrder($resolvedOrder, $revocationRecordId);
            $this->mailService->sendRevocationMails(
                new RevocationMailDataModel(
                    $revocationRecordId,
                    (string) $resolvedOrder->id,
                    (string) $resolvedOrder->fieldOrdernumber,
                    $this->formatOrderDate((string) $resolvedOrder->fieldDatecreated),
                    $this->formData->getName(),
                    $this->formData->getEmail(),
                    $this->formData->getCustomerNote(),
                    $submittedAt
                )
            );
        } catch (\Throwable) {
            $this->generalError = $this->trans('error.save_failed');

            return;
        }

        $this->formData = new RevocationFormDataModel();
        $this->errors = $this->validationService->getEmptyErrors();
        $this->generalError = null;
        $this->invalidOrderHint = null;
        $this->contactPageUrl = null;
        $this->successMessage = $this->trans('success.submitted');
    }

    /**
     * {@inheritdoc}
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = self::DEFAULT_SUBMIT_FUNCTION;
    }

    private function getContactPageUrl(): ?string
    {
        try {
            return $this->systemPageService->getLinkToSystemPageRelative(self::CONTACT_SYSTEM_PAGE);
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatOrderDate(string $orderDate): string
    {
        try {
            return (new \DateTimeImmutable($orderDate))->format('d.m.Y');
        } catch (\Exception) {
            return $orderDate;
        }
    }

    private function trans(string $key, array $parameters = []): string
    {
        return $this->translator->trans(self::TRANSLATION_PREFIX.$key, $parameters);
    }
}
