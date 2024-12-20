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
use ChameleonSystem\CoreBundle\ServiceLocator;

class TPkgShopArticlePreorder extends TPkgShopArticlePreorderAutoParent
{
    /**
     * send email to users email for the given article.
     *
     * @param TShopArticle $oArticle
     * @param string $sEmail
     *
     * @return bool
     */
    public function SendMail($oArticle = null, $sEmail = '')
    {
        static $aPortals = [];
        if (null === $oArticle) {
            $oArticle = $this->GetFieldShopArticle();
        }
        if ('' === $sEmail) {
            $sEmail = $this->fieldPreorderUserEmail;
        }
        if (!is_null($oArticle) && !empty($sEmail)) {
            $oMail = TdbDataMailProfile::GetProfile('preorder-available');
            $sArticleDetailLink = $oArticle->getLink(true);

            if (!array_key_exists($this->fieldCmsPortalId, $aPortals)) {
                $oCMSPortal = TdbCmsPortal::GetNewInstance();
                if ($oCMSPortal->Load($this->fieldCmsPortalId)) {
                    $aPortals[$this->fieldCmsPortalId] = $oCMSPortal;
                }
            }

            $sArticleBasketLink = '';
            if (array_key_exists($this->fieldCmsPortalId, $aPortals)) {
                $sArticleBasketLink = 'https://'.$aPortals[$this->fieldCmsPortalId]->GetPrimaryDomain().'/'.TdbShopArticle::URL_EXTERNAL_TO_BASKET_REQUEST.'/id/'.urlencode($oArticle->id);
            }

            $oMail->AddData('sUserEmail', $sEmail);

            /* @psalm-suppress InvalidPassByReference */
            $oMail->AddData('sArticleName', $oArticle->GetName());
            $oMail->AddData('sArticleDetailLink', $sArticleDetailLink);
            $oMail->AddData('sArticleBasketLink', $sArticleBasketLink);

            $oMail->ChangeToAddress($sEmail);
            $oMail->SendUsingObjectView('emails', 'Customer');

            $this->AllowEditByAll(true);
            $this->Delete();
            $this->AllowEditByAll(false);

            return true;
        }

        return false;
    }

    /**
     * save new preorder to the DB.
     *
     * @param string $sArticleId
     *
     * @return bool
     *
     * @psalm-suppress UndefinedClass
     *
     * @FIXME References `TdbShopArticlePreorder` when it probably should be `TdbPkgShopArticlePreorder`
     */
    public function SaveNewPreorder($sArticleId = '')
    {
        $oGlobal = TGlobal::instance();
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oGlobal->userDataExists('eMail') && TTools::IsValidEMail($oGlobal->GetuserData('eMail')) && !$oShopArticlePreorder = TdbShopArticlePreorder::LoadFromFields(['shop_article_id' => $sArticleId, 'preorder_user_email' => $oGlobal->GetuserData('eMail')])) {
            $activePortal = $this->getPortalDomainService()->getActivePortal();
            $aPostData = ['shop_article_id' => $sArticleId, 'preorder_user_email' => $oGlobal->GetuserData('eMail'), 'preorder_date' => date('Y-m-d H:i:s'), 'cms_portal_id' => $activePortal->id];
            $this->LoadFromRow($aPostData);
            $this->AllowEditByAll(true);
            $this->Save();
            $this->AllowEditByAll(false);
            $oMsgManager->AddMessage('mail-preorder-form-eMail', 'SUCCESS-USER-SIGNUP-PREORDER-ARTICLE');

            return true;
        }

        if (!TTools::IsValidEMail($oGlobal->GetuserData('eMail'))) {
            $oMsgManager->AddMessage('mail-preorder-form-eMail', 'ERROR-E-MAIL-INVALID-INPUT');

            return false;
        }

        if (TdbShopArticlePreorder::LoadFromFields(['shop_article_id' => $sArticleId, 'preorder_user_email' => $oGlobal->GetuserData('eMail')])) {
            $oMsgManager->AddMessage('mail-preorder-form-eMail', 'ERROR-USER-SIGNUP-PREORDER-ARTICLE');

            return false;
        }

        return false;
    }

    private function getPortalDomainService(): PortalDomainServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
