<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;

class MTPkgShopArticlePreorder_ShopCentralHandlerCore extends MTPkgShopArticlePreorder_ShopCentralHandlerCoreAutoParent
{
    /**
     * add your custom methods as array to $this->methodCallAllowed here
     * to allow them to be called from web.
     */
    protected function DefineInterface(): void
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'PreorderArticle';
    }

    /**
     * @return false|null
     */
    public function PreorderArticle()
    {
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists('user_email')) {
            $sUserEmail = $oGlobal->GetUserData('user_email');
            if (TTools::IsValidEMail($sUserEmail)) {
                $oActiveArticle = TdbShop::GetActiveItem();
                if ($oActiveArticle) {
                    $oActivePortal = $this->getPortalDomainService()->getActivePortal();
                    $aData = ['shop_article_id' => $oActiveArticle->id, 'preorder_user_email' => $sUserEmail, 'cms_portal_id' => $oActivePortal->id];
                    $oPreorder = TdbPkgShopArticlePreorder::GetNewInstance();
                    if ($oPreorder->LoadFromFields($aData)) {
                        // do nothing, article already preordered by this email
                    } else {
                        $aData['preorder_date'] = date('Y-m-d H:i:s');
                        $oPreorder->LoadFromRow($aData);
                        $oPreorder->AllowEditByAll(true);
                        $oPreorder->Save();
                        $oPreorder->AllowEditByAll(false);
                    }
                    $oMsgManager = TCMSMessageManager::GetInstance();
                    $aMessageData = ['sArticleName' => $oActiveArticle->GetName(), 'sArticleDetailLink' => $oActiveArticle->getLink()];
                    $oMsgManager->AddMessage('PKG-SHOP-ARTICLE-PREORDER', 'SUCCESS-SIGNUP-PREORDER-ARTICLE', $aMessageData);
                }
            } else {
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage('PKG-SHOP-ARTICLE-PREORDER', 'ERROR-E-MAIL-INVALID-INPUT');

                return false;
            }
        }
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
