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

class MTShopArticleQuestionEndPoint extends MTPkgViewRendererAbstractModuleMapper
{
    /**
     * @var array<string, array{bRequired: bool, sFilter: string}>
     * @psalm-var array<string, array{bRequired: bool, sFilter: TCMSUserInput::FILTER_*}>
     */
    protected $aInputDefinition = array(
                'name' => array('bRequired' => true, 'sFilter' => TCMSUserInput::FILTER_SAFE_TEXT),
                'email' => array('bRequired' => true, 'sFilter' => TCMSUserInput::FILTER_DEFAULT),
                'question' => array('bRequired' => true, 'sFilter' => TCMSUserInput::FILTER_SAFE_TEXTBLOCK),
            );

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'askQuestion';
    }

    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapeprVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $oMsgManager = TCMSMessageManager::GetInstance();

        $oVisitor->SetMappedValue('sFormMessages', $oMsgManager->RenderMessages($this->sModuleSpotName));
        reset($this->aInputDefinition);
        $aErrors = array();
        foreach ($this->aInputDefinition as $sField => $aFieldDef) {
            $sError = $oMsgManager->RenderMessages($this->sModuleSpotName.'-'.$sField);
            if (!empty($sError)) {
                $aErrors[$sField] = $sError;
            }
        }

        $oVisitor->SetMappedValue('aErrors', $aErrors);

        $sHiddenFields = $this->getHiddenFields();
        $oVisitor->SetMappedValue('sHiddenFormFields', $sHiddenFields);
    }

    /**
     * @return string
     */
    protected function getHiddenFields()
    {
        $aParameter = array(
            'module_fnc' => array($this->sModuleSpotName => 'askQuestion'),
        );

        $sHiddenFields = TTools::GetArrayAsFormInput($aParameter);
        $sHiddenFields .= "\n".$this->getHiddenAntiSpamField();

        return $sHiddenFields;
    }

    /**
     * returns a hidden honeypot form field to fool SPAM bots.
     *
     * @return string
     */
    protected function getHiddenAntiSpamField()
    {
        $oCaptcha = TdbPkgCmsCaptcha::GetInstanceFromName('text-field-javascript-hidden');
        $sAntiSpamField = $oCaptcha->getHTMLSnippet('question');

        return $sAntiSpamField;
    }

    /**
     * checks the anti spam field.
     *
     * @return bool
     */
    protected function isAntiSpamFieldValid()
    {
        $bCodeIsValid = false;
        $oCaptcha = TdbPkgCmsCaptcha::GetInstanceFromName('text-field-javascript-hidden');
        if ($oCaptcha->CodeIsValid('question', '')) {
            $bCodeIsValid = true;
        }

        return $bCodeIsValid;
    }

    /**
     * @return void
     */
    protected function askQuestion()
    {
        $aFilteredInput = array();

        $bHasErrors = false;
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($this->isAntiSpamFieldValid()) {
            foreach ($this->aInputDefinition as $sField => $aFieldDef) {
                $sData = trim($this->GetUserInput($sField, '', $aFieldDef['sFilter']));

                if ($aFieldDef['bRequired'] && empty($sData)) {
                    $oMsgManager->AddMessage($this->sModuleSpotName.'-'.$sField, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                    $bHasErrors = true;
                } elseif ('email' === $sField && false === TTools::IsValidEMail($sData)) {
                    $oMsgManager->AddMessage($this->sModuleSpotName.'-'.$sField, 'ERROR-E-MAIL-INVALID-INPUT');
                    $bHasErrors = true;
                }
                $aFilteredInput[$sField] = $sData;
            }
        } else {
            $bHasErrors = true;
        }
        $bHasErrors = !$this->postFieldCheckHook($aFilteredInput) || $bHasErrors;
        if (false === $bHasErrors) {
            $this->askQuestionExecute($aFilteredInput);
        }
    }

    /**
     * @param string[] $aFilteredInput
     *
     * @return bool
     */
    protected function postFieldCheckHook($aFilteredInput)
    {
        return true;
    }

    /**
     * sends the user question via mail profile: "product-info-request".
     *
     * @param array $aData
     *
     * @return never
     */
    protected function askQuestionExecute($aData)
    {
        $oMailProfile = TdbDataMailProfile::GetProfile('product-info-request');
        $oMailProfile->AddDataArray($aData);
        // now add product info as well
        /** @var $oActiveProduct TdbShopArticle */
        $oActiveProduct = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct();
        $aArticleData = $oActiveProduct->GetObjectPropertiesAsArray();
        $oMailProfile->AddDataArray($aArticleData);
        // add link to article

        $sLink = $oActiveProduct->getLink(true, null, array(TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => null));
        $oMailProfile->AddData('sLink', $sLink);
        $oMailProfile->ChangeFromAddress($aData['email'], $aData['name']);
        $oMailProfile->SendUsingObjectView('emails', 'Customer');

        $oMsgManager = TCMSMessageManager::GetInstance();
        $oMsgManager->AddMessage($this->sModuleSpotName, 'QUESTION-SEND');

        $this->getRedirect()->redirect($this->getActivePageService()->getActivePage()->GetRealURLPlain());
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
