<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * assumes the path in the TCMSSmartURLData is a simple tree path.
/**/
class TCMSSmartURLHandler_ShopBasketSteps extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = TCMSSmartURLData::GetActive();

        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId($oURLData->iPortalId);
        $oStep = $this->GetActiveStep($oURLData);
        if (!is_null($oStep)) {
            $this->aCustomURLParameters[MTShopOrderWizardCore::URL_PARAM_STEP_SYSTEM_NAME] = $oStep->fieldSystemname;
            $iNode = $oShop->GetSystemPageNodeId('checkout');
            $oNode = new TCMSTreeNode();
            /** @var $oNode TCMSTreeNode */
            $oNode->Load($iNode);
            $iPageId = $oNode->GetLinkedPage();
        }

        return $iPageId;
    }

    /**
     * @param TCMSSmartURLData $oURLData
     *
     * @return TdbShopOrderStep|null
     */
    protected function GetActiveStep($oURLData)
    {
        $oStep = null;
        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId($oURLData->iPortalId);
        $sCheckoutPath = $oShop->GetLinkToSystemPage('checkout');
        if ('.html' == substr($sCheckoutPath, -5)) {
            $sCheckoutPath = substr($sCheckoutPath, 0, -5);
        }
        if ('http://' == substr($sCheckoutPath, 0, 7) || 'https://' == substr($sCheckoutPath, 0, 8)) {
            $sCheckoutPath = substr($sCheckoutPath, strpos($sCheckoutPath, '/', 8));
        }
        if (strlen($sCheckoutPath) < strlen($oURLData->sRelativeFullURL)) {
            $sStepName = substr($oURLData->sRelativeFullURL, strlen($sCheckoutPath));
            if ('/' == substr($sStepName, 0, 1)) {
                $sStepName = substr($sStepName, 1);
            }
            if ('/' == substr($sStepName, -1)) {
                $sStepName = substr($sStepName, 0, -1);
            }
            if ('.html' == substr($sStepName, -5)) {
                $sStepName = substr($sStepName, 0, -5);
            }

            $oStep = TdbShopOrderStep::GetNewInstance();
            /** @var $oStep TdbShopOrderStep */
            if (!is_null($oURLData->sLanguageId)) {
                $oStep->SetLanguage($oURLData->sLanguageId);
            }
            if (!$oStep->LoadFromField('url_name', $sStepName)) {
                $oStep = null;
            } else {
                $oStep = TdbShopOrderStep::GetNewInstance($oStep->sqlData, $oStep->GetLanguage());
            }
        }

        return $oStep;
    }
}
