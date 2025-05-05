<?php

namespace ChameleonSystem\ShopCurrencyBundle\RequestState;

use ChameleonSystem\CoreBundle\RequestState\Interfaces\RequestStateElementProviderInterface;
use ChameleonSystem\CoreBundle\RequestType\RequestTypeInterface;
use ChameleonSystem\CoreBundle\Service\RequestInfoServiceInterface;
use ChameleonSystem\ShopCurrencyBundle\Interfaces\ShopCurrencyServiceInterface;
use ChameleonSystem\ShopCurrencyBundle\ShopCurrencyEvents;
use Symfony\Component\HttpFoundation\Request;

class CurrencyRequestStateProvider implements RequestStateElementProviderInterface
{
    /**
     * @var RequestInfoServiceInterface
     */
    private $requestInfoService;
    /**
     * @var ShopCurrencyServiceInterface
     */
    private $currencyService;

    public function __construct(
        RequestInfoServiceInterface $requestInfoService,
        ShopCurrencyServiceInterface $currencyService
    ) {
        $this->requestInfoService = $requestInfoService;
        $this->currencyService = $currencyService;
    }

    /**
     * {@inheritdoc}
     */
    public function getStateElements(Request $request)
    {
        if (false === $this->requestInfoService->isChameleonRequestType(RequestTypeInterface::REQUEST_TYPE_FRONTEND)) {
            return [];
        }

        return ['sActivePkgShopCurrencyId' => $this->currencyService->getActiveCurrencyId()];
    }

    /**
     * {@inheritdoc}
     */
    public static function getResetStateEvents()
    {
        return [
            ShopCurrencyEvents::CURRENCY_CHANGED,
        ];
    }
}
