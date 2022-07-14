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
 * @deprecated since 6.2.0 - no longer used.
 */
class TCMSWizardStepShopTellAFriend extends TdbCmsWizardStep
{
    const SESSION_CAPTCHA = 'tellafriendcaptchavalue';

    /**
     * @var array
     */
    protected $aUserInput = array();

    /**
     * method is called from the init method of the calling module. here you can check
     * if the step may be viewed, and redirect to another step if the user does not have permission.
     *
     * @return void
     */
    public function Init()
    {
        $oGlobal = TGlobal::instance();
        $this->aUserInput = $oGlobal->GetUserData('aInput');
        if (!is_array($this->aUserInput)) {
            $this->aUserInput = array();
        }

        if (!array_key_exists(MTShopArticleCatalogCore::URL_ITEM_ID, $this->aUserInput)) {
            $oGlobal = TGlobal::instance();
            $this->aUserInput[MTShopArticleCatalogCore::URL_ITEM_ID] = $oGlobal->GetUserData(MTShopArticleCatalogCore::URL_ITEM_ID);
        }

        $aInitInputData = array('name', 'email', 'toname', 'toemail', 'comment', 'captcha');
        foreach ($aInitInputData as $sField) {
            if (!array_key_exists($sField, $this->aUserInput)) {
                $this->aUserInput[$sField] = '';
            }
        }
        $this->aUserInput = $this->FilterUserData($this->aUserInput);
    }

    /**
     * @return false|string
     */
    protected function GetCaptchaValue()
    {
        $sCaptchaValue = false;
        if (array_key_exists(self::SESSION_CAPTCHA, $_SESSION)) {
            $sCaptchaValue = $_SESSION[self::SESSION_CAPTCHA];
        }

        return $sCaptchaValue;
    }

    /**
     * @return string
     */
    protected function GenerateCaptcha()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $val = $num1 + $num2;
        $_SESSION[self::SESSION_CAPTCHA] = $val;

        $sCaptchaQuestion = TGlobal::Translate('chameleon_system_shop.tell_a_friend.captcha', array('%num1%' => $num1, '%num2%' => $num2));

        return $sCaptchaQuestion;
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function &GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = array();

        // generate a captcha
        $this->aUserInput['captcha-question'] = $this->GenerateCaptcha();

        // if no article id is in aInputData, then we fetch it from post/get
        $oArticle = TdbShopArticle::GetNewInstance();
        /** @var $oArticle TdbShopArticle */
        if (!$oArticle->Load($this->aUserInput[MTShopArticleCatalogCore::URL_ITEM_ID])) {
            $oArticle = null;
        }
        $aViewVariables['oArticle'] = &$oArticle;

        $oMsgManager = TCMSMessageManager::GetInstance();

        $aInitInputData = array('name', 'email', 'toname', 'toemail', 'comment', 'captcha');
        $aFieldMessages = array();
        foreach ($aInitInputData as $sField) {
            if (!array_key_exists($sField, $this->aUserInput)) {
                $this->aUserInput[$sField] = '';
            }
            $aFieldMessages[$sField] = false;
            $sConsumerName = 'tell-a-friend-field-'.$sField;
            if ($oMsgManager->ConsumerHasMessages($sConsumerName)) {
                $oMsgs = $oMsgManager->ConsumeMessages($sConsumerName);
                while ($oMsg = &$oMsgs->Next()) {
                    $aFieldMessages[$sField] .= $oMsg->Render();
                }
            }
        }
        $this->aUserInput['captcha'] = '';
        $aViewVariables['aFieldMessages'] = &$aFieldMessages;
        $aViewVariables['aUserInput'] = &$this->aUserInput;

        return $aViewVariables;
    }

    /**
     * called by the ExecuteStep Method - place any logic for the standard proccessing of this step here
     * return false if any errors occure (returns the user to the current step for corrections).
     *
     * @return bool
     */
    protected function ProcessStep()
    {
        $bContinue = parent::ProcessStep();

        if ($this->ValidateUserData()) {
            $oArticle = TdbShopArticle::GetNewInstance();
            /** @var $oArticle TdbShopArticle */
            if (!$oArticle->Load($this->aUserInput[MTShopArticleCatalogCore::URL_ITEM_ID])) {
                $oArticle = null;
            }

            // send email
            $oMail = TDataMailProfile::GetProfile('shop-tell-a-friend');
            $aData = $this->aUserInput;
            $aData['sProduct'] = $oArticle->Render('email-tell-a-friend', 'Customer');
            $aData['sProduct-text'] = $oArticle->Render('email-tell-a-friend.txt', 'Customer');

            $oMail->AddDataArray($aData);
            $oMail->ChangeFromAddress($this->aUserInput['email'], $this->aUserInput['name']);
            $oMail->ChangeToAddress($this->aUserInput['toemail'], $this->aUserInput['toname']);
            $oMail->SendUsingObjectView('emails', 'Customer');

            // log data
            $oShop = TdbShop::GetInstance();
            if ($oShop->fieldLogArticleSuggestions) {
                $iUserId = '';
                $oUser = TdbDataExtranetUser::GetInstance();
                if ($oUser->IsLoggedIn()) {
                    $iUserId = $oUser->id;
                }
                $aData = array('datecreated' => date('Y-m-d H:i:s'), 'data_extranet_user_id' => $iUserId, 'shop_article_id' => $this->aUserInput[MTShopArticleCatalogCore::URL_ITEM_ID], 'from_email' => $this->aUserInput['email'], 'from_name' => $this->aUserInput['name'], 'to_email' => $this->aUserInput['toemail'], 'to_name' => $this->aUserInput['toname'], 'comment' => $this->aUserInput['comment']);
                $oLog = TdbShopSuggestArticleLog::GetNewInstance();
                /** @var $oLog TdbShopSuggestArticleLog */
                $oLog->LoadFromRow($aData);
                $oLog->AllowEditByAll(true);
                $oLog->Save();
            }
        } else {
            $bContinue = false;
        }

        return $bContinue;
    }

    /**
     * hook to filter user data.
     *
     * @param array $aData
     *
     * @return array
     */
    protected function FilterUserData($aData)
    {
        return $aData;
    }

    /**
     * validate the user input.
     *
     * @return bool
     */
    protected function ValidateUserData()
    {
        $bIsValid = true;

        $oMsgManager = TCMSMessageManager::GetInstance();

        // check user data
        $sCaptchaValue = $this->GetCaptchaValue();
        if (empty($sCaptchaValue) || $sCaptchaValue != $this->aUserInput['captcha']) {
            $bIsValid = false;
            $oMsgManager->AddMessage('tell-a-friend-field-captcha', 'INPUT-ERROR-INVALID-CAPTCHA');
        }
        $aRequiredFields = $this->RequiredFields();
        foreach ($aRequiredFields as $sFieldName) {
            $sVal = trim($this->aUserInput[$sFieldName]);
            if (empty($sVal)) {
                $bIsValid = false;
                $oMsgManager->AddMessage('tell-a-friend-field-'.$sFieldName, 'ERROR-USER-REQUIRED-FIELD-MISSING');
            }
        }

        return $bIsValid;
    }

    /**
     * return required fields.
     *
     * @return array
     */
    protected function RequiredFields()
    {
        return array('name', 'email', 'toname', 'toemail');
    }
}
