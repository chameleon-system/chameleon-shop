<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MTPkgShopWishlistCore extends TUserCustomModelBase
{
    /**
     * controlls the mode of the module.
     */
    const URL_MODE_PARAMETER_NAME = 'sMode';
    const MSG_CONSUMER_NAME = 'MTPkgShopWishlistCore';

    protected $sActiveMode = '';
    protected $aUserInput = false;
    protected $bAllowHTMLDivWrapping = true;

    public function Init()
    {
        parent::Init();
        $this->aUserInput = $this->global->GetUserData(TdbPkgShopWishlist::URL_PARAMETER_FILTER_DATA);
        if ($this->global->userdataExists(self::URL_MODE_PARAMETER_NAME)) {
            $aAllowedModes = array('', 'SendForm');
            $sMode = $this->global->GetUserData(self::URL_MODE_PARAMETER_NAME);
            if (in_array($sMode, $aAllowedModes)) {
                $this->sActiveMode = $sMode;
            }
        }
        if (!empty($this->sActiveMode)) {
            switch ($this->sActiveMode) {
                case 'SendForm':
                    $this->InitSendFormMode();
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Init method called when the module is in the SendForm Mode.
     */
    protected function InitSendFormMode()
    {
        $this->SetTemplate(get_class($this), 'system/vSendForm');
    }

    public function &Execute()
    {
        parent::Execute();
        if (!empty($this->sActiveMode)) {
            switch ($this->sActiveMode) {
                case 'SendForm':
                    $this->ExecuteSendFormMode();
                    break;
                default:
                    break;
            }
        }

        return $this->data;
    }

    /**
     * Execute method called when the module is in the SendForm mode.
     */
    protected function ExecuteSendFormMode()
    {
        if (!is_array($this->aUserInput)) {
            $this->aUserInput = array('to_name' => '', 'to_mail' => '', 'comment' => '');
        }
        $this->data['aUserInput'] = $this->aUserInput;
    }

    protected function DefineInterface()
    {
        parent::DefineInterface();
        if (!is_array($this->methodCallAllowed)) {
            $this->methodCallAllowed = array();
        }
        $this->methodCallAllowed[] = 'UpdateWishlist';
        $this->methodCallAllowed[] = 'RemoveArticle';
        $this->methodCallAllowed[] = 'Search';
        $this->methodCallAllowed[] = 'SendWishlist';
    }

    /**
     * sends the wishlist to the user specified in $this->aUserInput. redirectes
     * back to the wishlist page of the current user on success.
     */
    protected function SendWishlist()
    {
        if (!is_array($this->aUserInput)) {
            $this->aUserInput = array();
        }
        $oMsgManager = TCMSMessageManager::GetInstance();
        // validate input
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($this->SendWishlistDataValid() && $oUser->IsLoggedIn()) {
            $oWishList = &$oUser->GetWishlist();
            if (!is_null($oWishList)) {
                if ($oWishList->SendPerMail($this->aUserInput['to_mail'], $this->aUserInput['to_name'], $this->aUserInput['comment'])) {
                    $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'WISHLIST-SEND-MAIL', $this->aUserInput);
                    $oShop = TdbShop::GetInstance();
                    $sURL = $oShop->GetLinkToSystemPage('wishlist');
                    $this->controller->HeaderURLRedirect($sURL);
                    $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME, 'WISHLIST-UNABLE-TO-SEND-MAIL', $this->aUserInput);
                }
            }
        }
    }

    /**
     * validate the data in $this->aUserInput.
     *
     * @return bool
     */
    protected function SendWishlistDataValid()
    {
        $bIsValid = true;
        $aRequiredFields = array('to_name', 'to_mail', 'comment');
        $oMsgManager = TCMSMessageManager::GetInstance();
        foreach ($aRequiredFields as $sFieldName) {
            if (!array_key_exists($sFieldName, $this->aUserInput)) {
                $this->aUserInput[$sFieldName] = '';
            } else {
                $this->aUserInput[$sFieldName] = trim($this->aUserInput[$sFieldName]);
            }
            if (empty($this->aUserInput[$sFieldName])) {
                $bIsValid = false;
                $oMsgManager->AddMessage(self::MSG_CONSUMER_NAME.'-'.$sFieldName, 'ERROR-USER-REQUIRED-FIELD-MISSING');
            }
        }

        return $bIsValid;
    }

    protected function RemoveArticle()
    {
    }

    /**
     * Updates the wishlist comment, the lists item amount an comments using the data from post.
     */
    protected function UpdateWishlist()
    {
        $aInput = $this->global->GetUserData(TdbPkgShopWishlist::URL_PARAMETER_FILTER_DATA);
        $sWishListDescription = '';
        if (array_key_exists('description', $aInput)) {
            $sWishListDescription = $aInput['description'];
        }
        $oMsgManager = TCMSMessageManager::GetInstance();

        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oUser->IsLoggedIn()) {
            $oWishlist = &$oUser->GetWishlist(true);
            $aTmp = $oWishlist->sqlData;
            $aTmp['description'] = $sWishListDescription;
            if (array_key_exists('is_public', $aInput)) {
                $aTmp['is_public'] = $aInput['is_public'];
            }

            $oWishlist->LoadFromRow($aTmp);
            $oWishlist->Save();

            $aItems = array();
            if (array_key_exists('aItem', $aInput) && is_array($aInput['aItem'])) {
                $aItems = $aInput['aItem'];
                foreach ($aItems as $sWishlistItemId => $aItemData) {
                    $oWishlistItem = TdbPkgShopWishlistArticle::GetNewInstance();
                    /** @var $oWishlistItem TdbPkgShopWishlistArticle */
                    if ($oWishlistItem->LoadFromFields(array('pkg_shop_wishlist_id' => $oWishlist->id, 'id' => $sWishlistItemId))) {
                        $atmpData = $oWishlistItem->sqlData;
                        if (array_key_exists('comment', $aItemData)) {
                            $atmpData['comment'] = $aItemData['comment'];
                        }
                        if (array_key_exists('amount', $aItemData)) {
                            $atmpData['amount'] = $aItemData['amount'];
                        }
                        $oWishlistItem->LoadFromRow($atmpData);
                        $oWishlistItem->AllowEditByAll(true);
                        $oWishlistItem->Save();
                    }
                }
            }

            $oMsgManager->AddMessage($oWishlist->GetMsgConsumerName(), 'WISHLIST-UPDATED-INFOS');
        }
    }
}
