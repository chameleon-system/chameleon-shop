<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Service\OrderStepPageServiceInterface;

class TPkgShop_CmsLanguage extends TPkgShop_CmsLanguageAutoParent
{
    /**
     * Return translated page URL.
     *
     * @return string
     */
    public function GetTranslatedPageURL()
    {
        $sTranslatesPageURL = '';
        $this->TargetLanguageSimulation(true);

        // product page
        $oActiveProduct = TdbShop::GetActiveItem();
        if (null !== $oActiveProduct) {
            $oNewProduct = TdbShopArticle::GetNewInstance($oActiveProduct->id, $this->id);
            $oActiveProductCategory = TdbShop::GetActiveCategory();

            $sCatId = null;
            if (null !== $oActiveProductCategory) {
                $sCatId = $oActiveProductCategory->id;
            }
            $sTranslatesPageURL = $oNewProduct->getLink(true, null, [TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => $sCatId]);
        }

        // category page?
        if (empty($sTranslatesPageURL)) {
            $oActiveProductCategory = TdbShop::GetActiveCategory();
            if (is_object($oActiveProductCategory)) {
                $oNewProductCategory = TdbShopCategory::GetNewInstance($oActiveProductCategory->id, $this->id);
                $sTranslatesPageURL = $oNewProductCategory->GetLink(true);
            }
        }

        $this->TargetLanguageSimulation(false);

        // shop basket wizard?
        if (empty($sTranslatesPageURL)) {
            $oGlobal = TGlobal::instance();
            $sStepName = $oGlobal->GetUserData(MTShopOrderWizardCore::URL_PARAM_STEP_SYSTEM_NAME);
            if (!empty($sStepName)) {
                $oActiveStep = TdbShopOrderStep::GetStep($sStepName);
                $sTranslatesPageURL = $this->getOrderStepPageService()->getLinkToOrderStepPageAbsolute($oActiveStep, [], null, $this);
            }
        }

        if (empty($sTranslatesPageURL)) {
            $sTranslatesPageURL = parent::GetTranslatedPageURL();
        }

        return $sTranslatesPageURL;
    }

    private function getOrderStepPageService(): OrderStepPageServiceInterface
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.order_step_page_service');
    }
}
