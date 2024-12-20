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
use ChameleonSystem\ShopBundle\Interfaces\ProductVariantServiceInterface;
use Symfony\Component\HttpFoundation\Request;

class TShopVariantDisplayHandler extends TAdbShopVariantDisplayHandler
{
    public const VIEW_PATH_BASE = 'pkgShop/views/db/TShopVariantDisplayHandler';

    /**
     * return an instance of the handler type for the given id.
     *
     * @param string $sId
     *
     * @example TdbShopVariantDisplayHandler
     *
     * @return object|null
     */
    public static function GetInstance($sId)
    {
        $oRealObject = null;
        $oObject = TdbShopVariantDisplayHandler::GetNewInstance();
        /** @var $oObject TdbShopVariantDisplayHandler */
        if ($oObject->Load($sId)) {
            $sClassName = $oObject->fieldClass;
            $oRealObject = new $sClassName();
            $oRealObject->LoadFromRow($oObject->sqlData);
        }

        return $oRealObject;
    }

    /**
     * render the filter.
     *
     * @param TdbShopArticle $oArticle - the article object for which we want to render the view
     * @param string $sViewName - name of the view
     * @param string $sViewType - where to look for the view
     * @param array $aCallTimeVars - optional parameters to pass to render method
     *
     * @return string
     */
    public function Render($oArticle, $sViewName = 'vStandard', $sViewType = 'Customer', $aCallTimeVars = [])
    {
        $oView = new TViewParser();

        $request = $this->getCurrentRequest();

        if (null === $request) {
            return '';
        }

        $selectedTypeValues = $request->get(TShopVariantType::URL_PARAMETER);

        if (null === $selectedTypeValues) {
            $selectedTypeValues = [];
        }

        $selectedTypeValues = $this->getProductVariantService()->getProductBasedOnSelection($oArticle, $selectedTypeValues);
        if (!is_array($selectedTypeValues)) {
            $selectedTypeValues = [];
        }

        $oVariantSet = $oArticle->GetFieldShopVariantSet();

        $oView->AddVar('oDisplayHandler', $this);
        $oView->AddVar('aSelectedTypeValues', $selectedTypeValues);
        $oView->AddVar('oVariantSet', $oVariantSet);
        $oView->AddVar('oArticle', $oArticle);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($oArticle, $sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbShopVariantDisplayHandler::VIEW_PATH_BASE.'/'.get_class($this), $sViewType);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param TdbShopArticle $oArticle - the article object for which we want to render the view
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($oArticle, $sViewName, $sViewType)
    {
        return [];
    }

    private function getProductVariantService(): ProductVariantServiceInterface
    {
        return ServiceLocator::get('chameleon_system_shop.product_variant_service');
    }

    private function getCurrentRequest(): ?Request
    {
        return ServiceLocator::get('request_stack')->getCurrentRequest();
    }
}
