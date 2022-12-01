<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

class MTShopMyAccountCore extends MTExtranetMyAccountCore
{
    public function Execute()
    {
        parent::Execute();

        $activePage = $this->getActivePageService()->getActivePage();
        $aSignup = array('module_fnc' => array($this->sModuleSpotName => 'NewsletterSubscribe'));
        $this->data['sNewsSignupLink'] = $activePage->GetRealURLPlain($aSignup);

        $aSignout = array('module_fnc' => array($this->sModuleSpotName => 'NewsletterUnsubscribe'));
        $this->data['sNewsSignoutLink'] = $activePage->GetRealURLPlain($aSignout);

        return $this->data;
    }

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'NewsletterSubscribe';
        $this->methodCallAllowed[] = 'NewsletterUnsubscribe';
    }

    /**
     * @return void
     */
    public function NewsletterSubscribe()
    {
        // subscribe shop user to newsletter
        $oUser = TdbDataExtranetUser::GetInstance();

        $oNewsletter = TdbPkgNewsletterUser::GetInstanceForActiveUser();
        if (is_null($oNewsletter)) {
            $oNewsletter = TdbPkgNewsletterUser::GetNewInstance();
            $aData = array();
            $sNow = date('Y-m-d H:i:s');
            $aData['email'] = $oUser->GetUserEMail();
            $aData['data_extranet_salutation_id'] = $oUser->fieldDataExtranetSalutationId;
            $aData['lastname'] = $oUser->fieldLastname;
            $aData['firstname'] = $oUser->fieldFirstname;
            $aData['signup_date'] = $sNow;
            /**
             * There are different ways of opting in into the newsletter table:
             * - Subscribe on Website
             * - Subscribe on MyAccount page
             * - Subscribe while ordering
             * This information is now put into the optincode field.
             */
            $aData['optincode'] = 'MyAccount';
            $aData['data_extranet_user_id'] = $oUser->id;
            $aData['cms_portal_id'] = $oUser->fieldCmsPortalId;
            $oNewsletter->LoadFromRow($aData);
            $oNewsletter->ConfirmSignup();
            $oNewsletter->Save();
            $oNewsletter->PostSignUpNewsletterUserOnly();
            TdbPkgNewsletterUser::GetInstanceForActiveUser(true);

            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage(self::MSG_BASE_NAME.'-newsletter', 'NEWSLETTER-USER-SUBSCRIBED');
        } elseif (false == $oNewsletter->fieldOptin) {
            $oNewsletter->ConfirmSignup();
        }
        $this->RedirectToCurrentPage();
    }

    /**
     * @return void
     */
    public function NewsletterUnsubscribe()
    {
        $oNewsletter = TdbPkgNewsletterUser::GetInstanceForActiveUser();
        if (!is_null($oNewsletter)) {
            $oNewsletter->SignOut();
            TdbPkgNewsletterUser::GetInstanceForActiveUser(true);
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage(self::MSG_BASE_NAME.'-newsletter', 'NEWSLETTER-USER-UNSUBSCRIBED');
        }
        $this->RedirectToCurrentPage();
    }

    /**
     * @return never
     */
    protected function RedirectToCurrentPage()
    {
        $this->getRedirect()->redirectToActivePage();
    }

    /**
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgNewsletter'));

        return $aIncludes;
    }

    public function _AllowCache()
    {
        return false;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
