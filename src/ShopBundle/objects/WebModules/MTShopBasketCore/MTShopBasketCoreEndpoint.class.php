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
use ChameleonSystem\CoreBundle\Service\PageServiceInterface;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\CoreBundle\Service\TreeServiceInterface;
use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\ShopBundle\Interfaces\BasketProductAmountValidatorInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 *  The module manages access to the basket object.
 * IMPORTANT: do not extend directly form this class. use the "cms Klassen-vererbungsmanager" (pkg_cms_class_manager) instead
 * or, if you have to, extend from  "MTShopBasketCore".
/**/
class MTShopBasketCoreEndpoint extends TShopUserCustomModelBase
{
    const MSG_CONSUMER_NAME = 'mtshopbasketcoremsg';
    const MSG_CONSUMER_NAME_MINIBASKET = 'mtshopbasketmini';

    /**
     * the request field names that the basket objects understands.
     */
    const URL_ITEM_ID = 'basket[shop_article_id]';
    const URL_ITEM_BASKET_KEY = 'basket[sBasketItemKey]';
    const URL_ITEM_AMOUNT = 'basket[amount]';
    const URL_REDIRECT_NODE_ID = 'basket[redirectNodeId]';
    const URL_MESSAGE_CONSUMER = 'basket[consumer]';
    const URL_ACTION = 'basket[action]';
    const URL_CLEAR_BASKET = 'basket[bClearBasket]';

    /**
     * the request field names cut into the diefferent parts.
     */
    const URL_REQUEST_PARAMETER = 'basket';
    const URL_ITEM_ID_NAME = 'shop_article_id';
    const URL_CUSTOM_PARAMETER = 'custom';
    const URL_ITEM_BASKET_KEY_NAME = 'sBasketItemKey';
    const URL_ITEM_AMOUNT_NAME = 'amount';
    const URL_ACTION_NAME = 'action';
    const URL_REDIRECT_NODE_ID_NAME = 'redirectNodeId';
    const URL_CLEAR_BASKET_NAME = 'bClearBasket';
    const URL_MESSAGE_CONSUMER_NAME = 'consumer';
    const URL_VOUCHER_CODE = 'sShopVoucherCode';
    const URL_VOUCHER_BASKET_KEY = 'sBasketVoucherKey';

    protected $bAllowHTMLDivWrapping = true;
    private $basketHasMessages = null;

    public function Init()
    {
        parent::Init();

        // load affiliate code if passed as param
        $oShop = TdbShop::GetInstance();
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists($oShop->fieldAffiliateParameterName)) {
            $sCode = $oGlobal->GetUserData($oShop->fieldAffiliateParameterName);
            if (!empty($sCode)) {
                $oShop->SetAffiliateCode($sCode);
            }
        }
        $aBasketParam = $oGlobal->GetUserData('basket');
        if (is_array($aBasketParam) && array_key_exists(self::URL_CLEAR_BASKET_NAME, $aBasketParam)) {
            if ('true' == $aBasketParam[self::URL_CLEAR_BASKET_NAME]) {
                $this->ClearBasket();
            }
        }

        $this->ProcessAffiliatePartnerProgramms();
    }

    /**
     * clear all products from the basket and redirect to active page.
     */
    protected function ClearBasket()
    {
        $oBasket = TShopBasket::GetInstance();
        if ($oBasket->iTotalNumberOfUniqueArticles > 0) {
            $oBasket->ClearBasket();
            $aParams = $this->global->GetUserData();
            if (array_key_exists('basket', $aParams) && is_array($aParams['basket'])) {
                if (array_key_exists(self::URL_CLEAR_BASKET_NAME, $aParams['basket'])) {
                    unset($aParams['basket'][self::URL_CLEAR_BASKET_NAME]);
                }
            }
            $sURL = $this->getActivePageService()->getLinkToActivePageRelative($aParams, array('pagedef'));
            $this->getRedirect()->redirect($sURL);
        }
    }

    /**
     * method looks for affiliate parter codes passed to the website (if none are set yet).
     * if a code is found, it is stored in the session. note: we check only ONCE for a session
     * if suche a code is passed!
     */
    protected function ProcessAffiliatePartnerProgramms()
    {
        $sKey = 'MTShopBasketCore-affiliate-partner-program-processed';
        if (!array_key_exists($sKey, $_SESSION)) {
            $_SESSION[$sKey] = 1;
            TdbPkgShopAffiliate::ScanURLForAffiliateProgramCodes();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function &Execute()
    {
        parent::Execute();
        $this->data['oBasket'] = $this->getShopService()->getActiveBasket();

        return $this->data;
    }

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'JumpToBasketPage';
        $this->methodCallAllowed[] = 'AddToBasket';
        $this->methodCallAllowed[] = 'AddToBasketAjax';
        $this->methodCallAllowed[] = 'RemoveFromBasket';
        $this->methodCallAllowed[] = 'RemoveFromBasketViaBasketItemKey';
        $this->methodCallAllowed[] = 'UpdateBasketItem';
        $this->methodCallAllowed[] = 'UpdateBasketItems';
        $this->methodCallAllowed[] = 'AddToNoticeList';
        $this->methodCallAllowed[] = 'TransferFromNoticeList';
        $this->methodCallAllowed[] = 'TransferToNoticeList';
        $this->methodCallAllowed[] = 'RemoveFromNoticeListAjax';
        $this->methodCallAllowed[] = 'RemoveFromNoticeList';
        $this->methodCallAllowed[] = 'AddVoucher';
        $this->methodCallAllowed[] = 'RemoveVoucher';
    }

    /**
     * call the method to jump to the basket detail page. it will store the calling URL in the session, so
     * that it becomes possible to jump back to this page from the basket.
     *
     * @throws RouteNotFoundException
     */
    public function JumpToBasketPage()
    {
        $bJumpAsFarAsPossible = false;
        if ('1' === $this->getInputFilterUtil()->getFilteredInput('bJumpAsFarAsPossible')) {
            $bJumpAsFarAsPossible = true;
        }

        // save calling url to session
        $sCallingURL = $this->getCallingUrl();

        MTShopOrderWizardCore::SetCallingURL($sCallingURL);

        $systemPageService = $this->getSystemPageService();
        $checkoutSystemPage = $systemPageService->getSystemPage('checkout');
        if (null === $checkoutSystemPage) {
            throw new RouteNotFoundException("No system page with system name 'checkout' found - unable to redirect to the basket.");
        }
        $iRequestRedirectNodeId = $checkoutSystemPage->fieldCmsTreeId;

        $bNeedToRedirect = true;
        if (false === $bJumpAsFarAsPossible && $this->getActivePageService()->getActivePage()->GetMainTreeId() == $iRequestRedirectNodeId) {
            // we are already on the
            $bNeedToRedirect = false;
        }
        if (true === $bNeedToRedirect) {
            $oNode = new TCMSTreeNode();
            /** @var $oNode TCMSTreeNode */
            if ($oNode->Load($iRequestRedirectNodeId)) {
                $sURL = null;
                if ($bJumpAsFarAsPossible) {
                    $oUser = $this->getExtranetUserProvider()->getActiveUser();
                    $oStep = null;
                    $oBillingAdr = $oUser->GetBillingAddress();
                    if ($oUser->IsLoggedIn() && !is_null($oBillingAdr) && $oBillingAdr->ValidateData(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING)) {
                        $oStep = TdbShopOrderStep::GetStep('shipping');
                    } else {
                        $oStep = TdbShopOrderStep::GetStep('user');
                    }
                    if (!is_null($oStep)) {
                        $sURL = $oStep->GetStepURL();
                    }
                }
                if (null === $sURL) {
                    $sURL = $systemPageService->getLinkToSystemPageRelative('checkout');
                }
                $this->getRedirect()->redirect($sURL);
            } else {
                $this->RedirectToCallingPage();
            }
        }
    }

    /**
     * returns the url to the current page.
     *
     * @return string
     */
    protected function getCallingUrl()
    {
        $oGlobal = TGlobal::instance();
        $sCallingURL = $oGlobal->GetUserData('sourceurl');
        if (true === empty($sCallingURL)) {
            //the method will most likely be called on a basket page, in this case, ignore it...
            $activePageService = $this->getActivePageService();
            $oActivePage = $activePageService->getActivePage();
            $checkoutSystemPage = $this->getSystemPageService()->getSystemPage('checkout');
            if (null === $checkoutSystemPage || $checkoutSystemPage->fieldCmsTreeId !== $oActivePage->GetMainTreeId()) {
                $aExcludeParams = array('module_fnc');
                // we may be on a category list or an item page... so ignore those parameters, too
                $aExcludeParams[] = MTShopArticleCatalogCore::URL_CATEGORY_ID;
                $aExcludeParams[] = MTShopArticleCatalogCore::URL_ITEM_ID;
                $aExcludeParams[] = MTShopBasketCore::URL_REQUEST_PARAMETER;

                $sCallingURL = $activePageService->getLinkToActivePageRelative(array(), $aExcludeParams);
            } else {
                //basket, so try to get from session
                if (isset($_SESSION[MTShopOrderWizardCore::SESSION_PARAM_NAME]) && !empty($_SESSION[MTShopOrderWizardCore::SESSION_PARAM_NAME])) {
                    $sCallingURL = $_SESSION[MTShopOrderWizardCore::SESSION_PARAM_NAME];
                } else {
                    $portal = $this->getPortalDomainService()->getActivePortal();
                    if (null !== $portal) {
                        $sCallingURL = $this->getPageService()->getLinkToPortalHomePageAbsolute();
                    } else {
                        $sCallingURL = '/';
                    }
                }
            }
        }

        return $sCallingURL;
    }

    /**
     * moves an item from the notice list to the basket
     * $aArticleIdsToMove should have the form 'id'=>amount
     * any data not passed as a parameter is fetched from get/post
     * the data passed via get/post such as self::URL_ITEM_ID can be an array or a value.
     * example: <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ITEM_ID)?>[]" value="<?=TGlobal::OutHTML($iArticleId)?>" />
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ITEM_AMOUNT)?>[<?=TGlobal::OutHTML($iArticleId)?>]" value="2" />
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ITEM_ID)?>[]" value="<?=TGlobal::OutHTML($iArticleId2)?>" />
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ITEM_AMOUNT)?>[<?=TGlobal::OutHTML($iArticleId2)?>]" value="1" />
     * or, if you only want to transfer one item:
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ITEM_ID)?>" value="<?=TGlobal::OutHTML($iArticleId)?>" />
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ITEM_AMOUNT)?>" value="1" />.
     *
     * by default the articles will be removed from the notice list. if you want to keep some of them, make sure to add the following
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ACTION)?>" value="copy" />
     * OR
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ACTION)?>[<?=TGlobal::OutHTML($iArticleId)?>]" value="copy" />
     *          <input type="hidden" name="<?=TGlobal::OutHTML(MTShopBasketCore::URL_ACTION)?>[<?=TGlobal::OutHTML($iArticleId2)?>]" value="move" />
     * if you are moving more than one, and want to keep the first and move the second
     */
    public function TransferFromNoticeList($aArticleIdsToMove = null, $aKeepOnNoticeList = null, $sMessageHandler = null)
    {
        $oGlobal = TGlobal::instance();
        $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        if (is_null($aArticleIdsToMove) && array_key_exists(self::URL_ITEM_ID_NAME, $aRequestData)) {
            $aIdList = $aRequestData[self::URL_ITEM_ID_NAME];
            if (!is_array($aIdList)) {
                $aIdList = array($aIdList);
            }

            $aAmountList = null;
            if (array_key_exists(self::URL_ITEM_AMOUNT_NAME, $aRequestData)) {
                $aAmountList = $aRequestData[self::URL_ITEM_AMOUNT_NAME];
            }
            if (is_null($aAmountList)) {
                $aAmountList = array();
            }

            $aArticleIdsToMove = array();
            foreach ($aIdList as $index => $iArticleId) {
                $aArticleIdsToMove[$iArticleId] = '1';
                if (!is_array($aAmountList)) {
                    $aArticleIdsToMove[$iArticleId] = $aAmountList;
                } elseif (array_key_exists($iArticleId, $aAmountList)) {
                    $aArticleIdsToMove[$iArticleId] = $aAmountList[$iArticleId];
                }
            }
        }
        if (is_null($aKeepOnNoticeList) && array_key_exists(self::URL_ACTION_NAME, $aRequestData)) {
            $aKeepOnNoticeList = $aRequestData[self::URL_ACTION_NAME];
            if (!is_array($aKeepOnNoticeList)) {
                $aKeepOnNoticeList = array($aKeepOnNoticeList);
            }
        }
        if (is_null($aKeepOnNoticeList)) {
            $aKeepOnNoticeList = array();
        }

        if (is_null($sMessageHandler) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
            $sMessageHandler = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
            if (empty($sMessageHandler)) {
                $sMessageHandler = null;
            }
        }
        if (is_null($sMessageHandler)) {
            $sMessageHandler = self::MSG_CONSUMER_NAME;
        }

        reset($aArticleIdsToMove);

        // write message...
        $oShopConfig = TdbShop::GetInstance();
        $sBasketLinkStart = '<a href="'.$oShopConfig->GetBasketLink().'">';
        $sBasketLinkEnd = '</a>';
        $aMessageVars = array('BasketLinkStart' => $sBasketLinkStart, 'BasketLinkEnd' => $sBasketLinkEnd);
        $oMessage = TCMSMessageManager::GetInstance();
        $oMessage->AddMessage($sMessageHandler, 'NOTICE-LIST-MOVED-ITEM-TO-BASKET', $aMessageVars);

        foreach ($aArticleIdsToMove as $iArticleId => $iAmount) {
            //$this->UpdateBasketItem($a)
            $aAddToBasketRequest = array(self::URL_ITEM_ID_NAME => $iArticleId, self::URL_ITEM_AMOUNT_NAME => abs($iAmount), self::URL_MESSAGE_CONSUMER_NAME => $sMessageHandler);
            $this->UpdateBasketItem($aAddToBasketRequest, true, true);

            $sCommand = 'move';
            if (array_key_exists($iArticleId, $aKeepOnNoticeList)) {
                $sCommand = $aKeepOnNoticeList[$iArticleId];
            }
            if ('move' == $sCommand) {
                // remove from notice list
                $oUser = TdbDataExtranetUser::GetInstance();
                $oUser->RemoveArticleFromNoticeList($iArticleId);
            }
        }
        // now redirect either to requested page, or to calling page
        $iRedirectNodeId = null;
        if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aRequestData)) {
            $iRedirectNodeId = $aRequestData[self::URL_REDIRECT_NODE_ID_NAME];
            if (empty($iRedirectNodeId)) {
                $iRedirectNodeId = null;
            }
        }

        $this->RedirectAfterEvent($iRedirectNodeId);
    }

    public function RemoveFromNoticeListAjax($aArticleIdsToMove = null, $sMessageHandler = null, $bIsInteralCall = false)
    {
        return $this->RemoveFromNoticeList($aArticleIdsToMove, $sMessageHandler, $bIsInteralCall, true);
    }

    /**
     * remove item from notice list.
     *
     * @param array  $aArticleIdsToMove
     * @param string $sMessageHandler
     * @param bool   $bIsInteralCall
     */
    public function RemoveFromNoticeList($aArticleIdsToMove = null, $sMessageHandler = null, $bIsInteralCall = false, $bIsAjaxCall = false)
    {
        $oGlobal = TGlobal::instance();
        $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        if (is_null($aArticleIdsToMove) && array_key_exists(self::URL_ITEM_ID_NAME, $aRequestData)) {
            $aArticleIdsToMove = $aRequestData[self::URL_ITEM_ID_NAME];
            if (!is_array($aArticleIdsToMove)) {
                $aArticleIdsToMove = array($aArticleIdsToMove);
            }
        }

        if (is_null($sMessageHandler) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
            $sMessageHandler = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
            if (empty($sMessageHandler)) {
                $sMessageHandler = null;
            }
        }
        if (is_null($sMessageHandler)) {
            $sMessageHandler = self::MSG_CONSUMER_NAME;
        }
        $oMessageManager = TCMSMessageManager::GetInstance();
        $oUser = TdbDataExtranetUser::GetInstance();
        foreach ($aArticleIdsToMove as $iArticleId) {
            $oUser->RemoveArticleFromNoticeList($iArticleId);
            $oArticle = TdbShopArticle::GetNewInstance();
            /** @var $oArticle TdbShopArticle */
            $oArticle->Load($iArticleId);
            $aMessageData = array('sArticleLinkStart' => '<a href="'.TGlobal::OutHTML($oArticle->GetDetailLink()).'">', 'sArticleLinkEnd' => '</a>', 'sArticleName' => $oArticle->GetName());
            if (!$bIsAjaxCall) {
                $oMessageManager->AddMessage($sMessageHandler, 'NOTICE-LIST-REMOVED-ITEM', $aMessageData);
            }
        }

        $iRedirectNodeId = null;
        if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aRequestData)) {
            $iRedirectNodeId = $aRequestData[self::URL_REDIRECT_NODE_ID_NAME];
            if (empty($iRedirectNodeId)) {
                $iRedirectNodeId = null;
            }
        }

        if (!$bIsInteralCall) {
            if ($bIsAjaxCall) {
                return array('message' => '');
            } else {
                $this->RedirectAfterEvent($iRedirectNodeId);
            }
        }
    }

    /**
     * moves or copies one or more articles from the basket to the notice list. products to be moved must be passed.
     *
     * @param int    $iArticleId      - if you pass null, it will attempt to fetch the item id from post
     * @param int    $iAmount         - amount to add
     * @param string $sMessageHandler - info passed to this handler - uses the default message consumer for the shop if non passed (can also be passed via get/post)
     * @param bool   $bIsInternalCall
     */
    public function TransferToNoticeList($iArticleId = null, $iAmount = null, $sMessageHandler = null, $bIsInternalCall = false)
    {
        $this->RemoveFromBasket($iArticleId, $sMessageHandler, true);
        $this->AddToNoticeList($iArticleId, $iAmount, $sMessageHandler, $bIsInternalCall);
    }

    /**
     * insert an item into the notice list.
     *
     * @param int    $iArticleId      - if you pass null, it will attempt to fetch the item id from post
     * @param int    $iAmount         - amount to add
     * @param string $sMessageHandler - info passed to this handler - uses the default message consumer for the shop if non passed (can also be passed via get/post)
     * @param bool   $bIsInternalCall
     */
    public function AddToNoticeList($iArticleId = null, $iAmount = null, $sMessageHandler = null, $bIsInternalCall = false)
    {
        $oGlobal = TGlobal::instance();
        $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        if (is_null($iArticleId) && array_key_exists(self::URL_ITEM_ID_NAME, $aRequestData)) {
            $iArticleId = $aRequestData[self::URL_ITEM_ID_NAME];
            if (empty($iArticleId)) {
                $iArticleId = null;
            }
        }
        if (is_null($iAmount) && array_key_exists(self::URL_ITEM_AMOUNT_NAME, $aRequestData)) {
            $iAmount = $aRequestData[self::URL_ITEM_AMOUNT_NAME];
            if (empty($iAmount)) {
                $iAmount = 1;
            }
        }

        if (is_null($sMessageHandler) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
            $sMessageHandler = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
            if (empty($sMessageHandler)) {
                $sMessageHandler = null;
            }
        }
        if (is_null($sMessageHandler)) {
            $sMessageHandler = self::MSG_CONSUMER_NAME;
        }

        $oMsgManager = TCMSMessageManager::GetInstance();
        if (is_null($iArticleId)) {
            $oMsgManager->AddMessage($sMessageHandler, 'ERROR-SHOP-NOTICE-LIST-ADD-ITEM-NO-ID-GIVEN');
        } else {
            $oItem = TdbShopArticle::GetNewInstance();
            /** @var $oItem TdbShopArticle */
            if ($oItem->Load($iArticleId)) {
                $oShop = TdbShop::GetInstance();
                // add item to list
                $oUser = TdbDataExtranetUser::GetInstance();
                $dNewAmountOnList = $oUser->AddArticleIdToNoticeList($iArticleId, $iAmount);
                $aInfoData = array('sLinkNoticeListStart' => '<a href="'.$oShop->GetLinkToSystemPage('noticelist').'">', 'sLinkNoticeListEnd' => '</a>', 'sLinkArticleStart' => '<a href="'.$oItem->GetDetailLink().'">', 'sLinkArticleEnd' => '</a>', 'sArticleName' => TGlobal::OutHTML($oItem->GetName()), 'dAddedAmount' => $iAmount, 'dNewAmount' => $dNewAmountOnList, 'sArticleAddedId' => $oItem->id);
                if (false === $dNewAmountOnList) {
                    // article was already on notice list...
                    $oMsgManager->AddMessage($sMessageHandler, 'NOTICE-LIST-ITEM-ALREADY-ON-LIST', $aInfoData);
                } else {
                    $oMsgManager->AddMessage($sMessageHandler, 'NOTICE-LIST-ADDED-ITEM', $aInfoData);
                }
            } else {
                $oMsgManager->AddMessage($sMessageHandler, 'ERROR-SHOP-NOTICE-LIST-INVALID-ITEM-ID');
            }
        }

        // now redirect either to requested page, or to calling page
        $iRedirectNodeId = null;
        if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aRequestData)) {
            $iRedirectNodeId = $aRequestData[self::URL_REDIRECT_NODE_ID_NAME];
            if (empty($iRedirectNodeId)) {
                $iRedirectNodeId = null;
            }
        }

        if (!$bIsInternalCall) {
            $this->RedirectAfterEvent($iRedirectNodeId);
        }
    }

    /**
     * adds a voucher to the basket. relevant messages will be send to the consumer defined by $sMessageConsumerName.
     *
     * @param string $sShopVoucherCode       - the voucher code to add
     * @param string $sMessageHandler        - name of the consumer to which any messages should be send
     * @param bool   $bIsInternalCall        - set to true, if you want to prevent redirection and get the success/failure as a boolean return value
     * @param int    $iRedirectSuccessNodeId - optional tree node to which we want to redirect on success to. if not set, we will redirect to the calling page
     */
    protected function AddVoucher($sShopVoucherCode = null, $sMessageHandler = null, $bIsInternalCall = false, $iRedirectSuccessNodeId = null)
    {
        $bVoucherAdded = false;
        $aBasketData = $this->global->GetUserData(self::URL_REQUEST_PARAMETER);

        if (is_null($sShopVoucherCode) && array_key_exists(self::URL_VOUCHER_CODE, $aBasketData)) {
            $sShopVoucherCode = $aBasketData[self::URL_VOUCHER_CODE];
        }
        if (is_null($sMessageHandler) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aBasketData)) {
            $sMessageHandler = $aBasketData[self::URL_MESSAGE_CONSUMER_NAME];
        } elseif (is_null($sMessageHandler)) {
            $sMessageHandler = self::MSG_CONSUMER_NAME;
        }

        $oVoucher = $this->getValidVoucher($sShopVoucherCode);

        if (is_object($oVoucher)) {
            $oBasket = TShopBasket::GetInstance();
            $bVoucherAdded = $oBasket->AddVoucher($oVoucher, $sMessageHandler);
        } else {
            $oMessageManager = TCMSMessageManager::GetInstance();
            $oMessageManager->AddMessage($sMessageHandler, 'VOUCHER-ERROR-NOT-FOUND', array('code' => TGlobal::OutHTML($sShopVoucherCode)));
        }

        // if this is not an internal call, then we redirect to either the successpage or the calling page
        if (!$bIsInternalCall) {
            if ($bVoucherAdded) {
                // now redirect either to requested page, or to calling page
                if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aBasketData)) {
                    $iRedirectSuccessNodeId = $aBasketData[self::URL_REDIRECT_NODE_ID_NAME];
                    if (empty($iRedirectSuccessNodeId)) {
                        $iRedirectSuccessNodeId = null;
                    }
                }
                // redirect to success node (or current page, if no success node is given)
                $this->RedirectAfterEvent($iRedirectSuccessNodeId);
            } else {
                $this->RedirectAfterEvent(null);
            } // redirect to current page
        }

        // we get here only if this is an internal call. in that case, we return success or failure
        return $bVoucherAdded;
    }

    /**
     * validates the voucher code and tries to load the voucher record.
     *
     * @param $sShopVoucherCode
     *
     * @return TdbShopVoucher|null
     */
    protected function getValidVoucher($sShopVoucherCode)
    {
        $oValidVoucher = null;
        /** @var $oVoucher TdbShopVoucher */
        $oVoucher = TdbShopVoucher::GetNewInstance();

        if (strlen($sShopVoucherCode) >= 3) {
            if ($oVoucher->LoadFromFields(array('code' => $sShopVoucherCode, 'is_used_up' => '0'))) {
                $oValidVoucher = $oVoucher;
            }
        }

        return $oValidVoucher;
    }

    /**
     * Removes a voucher from the basket. returns result only if $bIsInternalCall is set to true.
     *
     * @param string $sBasketVoucherKey - The sBasketVoucherKey of the voucher that should be removed
     * @param string $sMessageHandler   - The message handler that should receive the result message
     * @param bool   $bIsInternalCall   - set this to true if you want to prevent the redirect after the operation
     *
     * @return bool
     */
    public function RemoveVoucher($sBasketVoucherKey = null, $sMessageHandler = null, $bIsInternalCall = false)
    {
        $oMessageManager = TCMSMessageManager::GetInstance();
        $aBasketData = $this->global->GetUserData(self::URL_REQUEST_PARAMETER);

        $bWasRemoved = true;

        if (is_null($sBasketVoucherKey) && array_key_exists(self::URL_VOUCHER_BASKET_KEY, $aBasketData)) {
            $sBasketVoucherKey = $aBasketData[self::URL_VOUCHER_BASKET_KEY];
        }
        if (is_null($sMessageHandler) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aBasketData)) {
            $sMessageHandler = $aBasketData[self::URL_MESSAGE_CONSUMER_NAME];
        } elseif (is_null($sMessageHandler)) {
            $sMessageHandler = self::MSG_CONSUMER_NAME;
        }

        $oBasket = TShopBasket::GetInstance();
        $bWasRemoved = $oBasket->RemoveVoucher($sBasketVoucherKey, $sMessageHandler);

        if (!$bIsInternalCall) {
            $this->RedirectAfterEvent(null); // redirect to current page
        }

        return $bWasRemoved;
    }

    /**
     * adds a product to the current basket. expects data in get/post:
     * basket[amount]=x
     * basket[shop_article_id]=y
     * basket[redirectNodeId] = y
     * basket[consumer] = z
     * if the product exists in the basket, the amount will be added
     * the consumer is optional and will be used to set the objects response message. if no consumer is given, the response will be send to the global consumer.
     *
     * @param array $aRequestData - you can pass the request data directly. if you do not, the data will be fetched from get/post
     */
    public function AddToBasket($aRequestData = null)
    {
        $this->UpdateBasketItem($aRequestData, true);
    }

    /**
     * adds a product to the current basket. expects data in get/post:
     * basket[amount]=x
     * basket[shop_article_id]=y
     * basket[redirectNodeId] = y
     * basket[consumer] = z
     * if the product exists in the basket, the amount will be added
     * the consumer is optional and will be used to set the objects response message. if no consumer is given, the response will be send to the global consumer.
     *
     * @param array $aRequestData - you can pass the request data directly. if you do not, the data will be fetched from get/post
     */
    public function AddToBasketAjax($aRequestData = null)
    {
        $this->UpdateBasketItem(null, true, true, true);

        // we do not redirect - so we need to recalculate basket by hand
        $oBasket = TShopBasket::GetInstance();
        $oBasket->RecalculateBasket();

        $oResponse = new stdClass();
        $oResponse->sNotificationWindow = TTools::CallModule('MTShopBasket', 'system');
        $oResponse->sMiniBasket = TTools::CallModule('MTShopBasket', 'mini');

        return $oResponse;
    }

    /**
     * the method updates more than one item in the basket at once. it expects the data in basket in this form:
     * basket[][basket_item_id] = x
     * basket[][amount] = y
     * basket[][consumer] = z
     * redirectNodeId = y.
     *
     * @param array $aRequestData               - if no request data is passed, the method will look in get/post
     * @param bool  $bAddAmountToExistingAmount - if you set this to true, the amount passed will be added to any existing amount, instead of overwriting the amount
     * @param bool  $bIsInternalCall            - set this to true if you want to manage the messages and the redirects from the calling method
     *
     * @return bool - if this is an internal call, then the method will return false if it was passed invalid data
     */
    public function UpdateBasketItems($aRequestData = null, $bAddAmountToExistingAmount = false, $bIsInternalCall = false)
    {
        $oMessage = TCMSMessageManager::GetInstance();
        $oGlobal = TGlobal::instance();
        if (is_null($aRequestData)) {
            $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        }

        $bDataValid = true;
        if (!is_array($aRequestData)) {
            // invalid data
            $oMessage->AddMessage($this->sModuleSpotName, 'ERROR-UPDATE-BASKET-ITEMS-PARAMETERS-MISSING', array('sMissingParameters' => TGlobal::Translate('chameleon_system_shop.module_basket.error_no_data_sent_to_update_basket_items')));
            $bDataValid = false;
        }
        if ($bDataValid) {
            $sConsumerName = null;
            if (array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
                $sConsumerName = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
            }
            $bAllItemsUpdated = true;
            foreach ($aRequestData as $aItemRequestData) {
                if (is_array($aItemRequestData)) {
                    if (!is_null($sConsumerName)) {
                        $aItemRequestData[self::URL_MESSAGE_CONSUMER_NAME] = $sConsumerName;
                    }
                    if (!$this->UpdateBasketItem($aItemRequestData, $bAddAmountToExistingAmount, true)) {
                        $bAllItemsUpdated = false;
                    }
                }
            }
            if ($bAllItemsUpdated) {
                $oMessage->AddMessage($this->sModuleSpotName, 'BASKET-ITEMS-UPDATED');
                // now perform any redirect requested
                if (!$bIsInternalCall) {
                    $iRedirectNodeId = null;
                    if ($oGlobal->UserDataExists(self::URL_REDIRECT_NODE_ID_NAME)) {
                        $iRedirectNodeId = $oGlobal->GetUserData(self::URL_REDIRECT_NODE_ID_NAME);
                    }
                    $this->RedirectAfterEvent($iRedirectNodeId);
                    exit(0);
                }
            } else {
                $bDataValid = false;
            }
        }

        if (!$bIsInternalCall && !$bDataValid) {
            $this->RedirectToCallingPage();
        }

        return $bDataValid;
    }

    /**
     * removes a product from the basket. expects basket id in get/post
     * basket[article_id]=x
     * basket[consumer] = z
     * the consumer is optional and will be used to set the objects respons message. if no conumser is given, the repsonse will be send to the global consumer.
     *
     * @deprecated use RemoveFromBasketViaBasketItemKey whenever possible
     */
    public function RemoveFromBasket($iArticleId = null, $sConsumer = null, $bIsInternalCall = false)
    {
        $oGlobal = TGlobal::instance();
        $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        if (!is_array($aRequestData)) {
            $aRequestData = array();
        }
        if (!is_null($iArticleId)) {
            $aRequestData[self::URL_ITEM_ID_NAME] = $iArticleId;
        }
        if (!is_null($sConsumer)) {
            $aRequestData[self::URL_MESSAGE_CONSUMER_NAME] = $sConsumer;
        }
        $aRequestData[self::URL_ITEM_AMOUNT_NAME] = 0;
        $this->UpdateBasketItem($aRequestData, false, $bIsInternalCall);
    }

    /**
     * removes an item from the basket via the items basket key. the method expects the data in the form:
     * basket[sBasketItemKey] =x, basket[consumer] = y.
     *
     * @param string $sBasketItemKey
     * @param string $sConsumer       - optional. if no consumer is give, the response will be send to the global consumer.
     * @param bool   $bIsInternalCall
     */
    protected function RemoveFromBasketViaBasketItemKey($sBasketItemKey = null, $sConsumer = null, $bIsInternalCall = false)
    {
        $oGlobal = TGlobal::instance();
        $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        if (!is_array($aRequestData)) {
            $aRequestData = array();
        }
        if (!is_null($sBasketItemKey)) {
            $aRequestData[self::URL_ITEM_BASKET_KEY_NAME] = $sBasketItemKey;
        }
        if (!is_null($sConsumer)) {
            $aRequestData[self::URL_MESSAGE_CONSUMER_NAME] = $sConsumer;
        }

        if (array_key_exists(self::URL_ITEM_BASKET_KEY_NAME, $aRequestData)) {
            $sBasketItemKey = $aRequestData[self::URL_ITEM_BASKET_KEY_NAME];
        }
        if (array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
            $sConsumer = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
        }

        $oBasket = TShopBasket::GetInstance();
        $oRemovedItem = $oBasket->RemoveItem($sBasketItemKey);
        if ($oRemovedItem) {
            $this->PostRemoveItemInBasketHook($oRemovedItem);
            $oShopConfig = TdbShop::GetInstance();
            $sArticleName = $oRemovedItem->GetName();

            $sBasketLinkStart = '<a href="'.$oShopConfig->GetBasketLink().'">';
            $sBasketLinkEnd = '</a>';
            $aMessageVars = array('ArticleName' => $sArticleName, 'BasketLinkStart' => $sBasketLinkStart, 'BasketLinkEnd' => $sBasketLinkEnd, 'amount' => $oRemovedItem->dAmount);
            $aMessageVars['ArticleLinkStart'] = '<a href="'.$oRemovedItem->GetDetailLink().'">';
            $aMessageVars['ArticleLinkEnd'] = '</a>';
            $oMessage = TCMSMessageManager::GetInstance();
            $oMessage->AddMessage($sConsumer, 'BASKET-REMOVED-ITEM', $aMessageVars);

            if (false == $bIsInternalCall) {
                $iRedirectNodeId = null;
                if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aRequestData)) {
                    $iRedirectNodeId = $aRequestData[self::URL_REDIRECT_NODE_ID_NAME];
                }
                $this->RedirectAfterEvent($iRedirectNodeId);
            }
        } else {
            $oMessage = TCMSMessageManager::GetInstance();
            $oMessage->AddMessage($sConsumer, 'ERROR-ADD-TO-BASKET-PARAMETERS-MISSING', array('sMissingParameters' => TGlobal::Translate('chameleon_system_shop.module_basket.error_basket_item_key_unknown', array('%key%' => $sBasketItemKey))));
            if (false == $bIsInternalCall) {
                $this->RedirectToCallingPage();
            }
        }
    }

    /**
     * updates the amount of a basket item. expects data in get/post
     * basket[basket_item_id] =x
     * basket[amount] = y
     * basket[redirectNodeId] = y
     * basket[consumer] = z
     * the consumer is optional and will be used to set the objects respons message. if no conumser is given, the repsonse will be send to the global consumer.
     *
     * @param array $aRequestData                - you can pass the request data directly. if you do not, the data will be fetched from get/post
     * @param bool  $bAddAmountToExistingAmount  - if you set this to true, the amount passed will be added to any existing amount, instead of overwriting the amount
     * @param bool  $bIsInternalCall             - set this to true if you want to manage the messages and the redirects from the calling method
     * @param bool  $bWriteMessageOnInternalCall - if $bIsInternalCall is set to true you can force the success message by setting $bWriteMessageOnInternalCall to true
     *
     * @return bool - if this is an internal call, then the method will return false if it was passed invalid data
     */
    public function UpdateBasketItem($aRequestData = null, $bAddAmountToExistingAmount = false, $bIsInternalCall = false, $bWriteMessageOnInternalCall = false)
    {
        $request = $this->getCurrentRequest();
        /** @var TPKgCmsSession $session */
        $session = $request->getSession();
        if (false === $session->RestartSessionWithWriteLock()) {
            TTools::WriteLogEntry('unable to add article to basket because session could not be locked', 1, __FILE__, __LINE__);

            return false;
        }

        $oGlobal = TGlobal::instance();
        // check if all needed parameters are present
        $oBasket = TShopBasket::GetInstance();
        $oMessage = TCMSMessageManager::GetInstance();
        $oArticle = null;

        $bDataValid = true;
        if (is_null($aRequestData)) {
            $aRequestData = $oGlobal->GetUserData(self::URL_REQUEST_PARAMETER);
        }
        $sMessageHandler = null;
        if (is_array($aRequestData) && array_key_exists(self::URL_MESSAGE_CONSUMER_NAME, $aRequestData)) {
            $sMessageHandler = $aRequestData[self::URL_MESSAGE_CONSUMER_NAME];
            if (empty($sMessageHandler)) {
                $sMessageHandler = null;
            }
        }
        if (is_null($sMessageHandler)) {
            $sMessageHandler = $this->sModuleSpotName;
        }
        $iAmount = 1;

        if (!is_array($aRequestData)) {
            $bDataValid = false;
            $oMessage->AddMessage($sMessageHandler, 'ERROR-ADD-TO-BASKET-PARAMETERS-MISSING', array('sMissingParameters' => TGlobal::Translate('chameleon_system_shop.module_basket.error_add_to_basket_no_data')));
        } elseif (!array_key_exists(self::URL_ITEM_ID_NAME, $aRequestData)) {
            $bDataValid = false;
            $oMessage->AddMessage($sMessageHandler, 'ERROR-ADD-TO-BASKET-PARAMETERS-MISSING', array('sMissingParameters' => TGlobal::Translate('chameleon_system_shop.module_basket.error_add_to_basket_no_id')));
        } else {
            $oArticle = new TShopBasketArticle();
            if (!$oArticle->Load($aRequestData[self::URL_ITEM_ID_NAME])) {
                $bDataValid = false;
                $oMessage->AddMessage($sMessageHandler, 'ERROR-ADD-TO-BASKET-PARAMETERS-MISSING', array('sMissingParameters' => TGlobal::Translate('chameleon_system_shop.module_basket.error_add_to_basket_id_unknown', array(
                        '%id%' => $aRequestData[self::URL_ITEM_ID_NAME],
                    ))));
            }

            if (isset($aRequestData[self::URL_ITEM_AMOUNT_NAME])) {
                $requestedAmount = $aRequestData[self::URL_ITEM_AMOUNT_NAME];
                if (true === $this->getBasketAmountValidator()->isAmountValid($oArticle, $requestedAmount)) {
                    $iAmount = (float) $requestedAmount;
                } else {
                    $bDataValid = false;
                    $oMessage->AddMessage($sMessageHandler, 'ERROR-ADD-TO-BASKET-PARAMETERS-MISSING', array('sMissingParameters' => TGlobal::Translate('Die Mengenangabe ist ungültig.')));
                }
            }
        }
        $basketItemKey = (null !== $this->getBasketItemKeyFromUserInput($aRequestData)) ? $this->getBasketItemKeyFromUserInput($aRequestData) : $oArticle->sBasketItemKey;

        $iAlreadyInBasket = 0;
        $iAmountAvailable = 0;
        $oExistingItem = null;
        if ($bDataValid) {
            $oExistingItem = $oBasket->FindItemByBasketItemKey($basketItemKey);
            if ($oExistingItem) {
                $customData = $oExistingItem->getCustomData();
                if (true === \is_array($customData) && 0 !== \count($customData)) {
                    $oArticle->setCustomData($oExistingItem->getCustomData());
                }
                $iAlreadyInBasket = $oExistingItem->dAmount;
            } else {
                $oExistingItem = null;
            }
            if ($bAddAmountToExistingAmount) {
                $iAmountAvailable = $oArticle->TotalStockAvailable() - $iAlreadyInBasket;
            } else {
                $iAmountAvailable = $oArticle->TotalStockAvailable();
            }
            $iRealAmountAdded = $iAmount;

            if ($iAmountAvailable < 0) {
                $iAmountAvailable = 0;
            }
        }

        if ($bDataValid && ($iAmountAvailable < $iAmount)) {
            $aErrorCodes = $oArticle->GetSQLWithTablePrefix();
            $aErrorCodes['dStockWanted'] = $iAmount;
            if ($oArticle->TotalStockAvailable() > 0) {
                $aErrorCodes['dStockAvailable'] = $oArticle->TotalStockAvailable();
                $oMessage->AddMessage($sMessageHandler, 'ERROR-ADD-TO-BASKET-NOT-ENOUGH-STOCK', $aErrorCodes);
            } else {
                $oMessage->AddMessage($sMessageHandler, 'ERROR-ADD-TO-BASKET-NO-STOCK', $aErrorCodes);
                $iAmount = 0;
            }
            $iRealAmountAdded = $iAmountAvailable;
        } elseif ($bDataValid) {
            // make sure we are allowed to purchase the item
            if (!$oArticle->IsBuyable()) {
                $aErrorCodes = $oArticle->GetSQLWithTablePrefix();
                $bDataValid = false;
                $oMessage->AddMessage($sMessageHandler, 'ERROR-ADD-TO-BASKET-PARAMETERS-MISSING', array('sMissingParameters' => TGlobal::Translate('chameleon_system_shop.module_basket.error_add_to_basket_not_buyable')));
            }
        }

        $customData = [];
        if (($oArticle instanceof IPkgShopBasketArticleWithCustomData) && true === $oArticle->isConfigurableArticle()) {
            $customData = $this->getCustomDataFromRequest($oArticle, $aRequestData, $oExistingItem);
            if (null !== $customData) {
                $bDataValid = $this->customDataIsValid($oArticle, $customData, $oMessage, $sMessageHandler);
            }
        }

        if ($bDataValid) {
            // ok, add to basket...
            $oArticle->ChangeAmount($iAmount);
            // add to basket object...
            $bItemWasUpdated = false;
            if (!$bAddAmountToExistingAmount) {
                $bItemWasUpdated = $oBasket->UpdateItemAmount($oArticle);
            } else {
                $bItemWasUpdated = $oBasket->AddItem($oArticle);
            }
            if (($oArticle instanceof IPkgShopBasketArticleWithCustomData) && true === $oArticle->isConfigurableArticle()) {
                $oBasket->updateCustomData($oArticle->sBasketItemKey, $customData);
            }

            $this->PostUpdateItemInBasketEvent($oArticle, $bItemWasUpdated);

            // now write messages and redirect - but only if this is not an internal call
            if (false == $bIsInternalCall || true == $bWriteMessageOnInternalCall) {
                // write success message
                $oShopConfig = TdbShop::GetInstance();
                $sArticleName = $oArticle->GetName();

                $sBasketLinkStart = '<a href="'.$oShopConfig->GetBasketLink().'">';
                $sBasketLinkEnd = '</a>';
                $aMessageVars = array('ArticleName' => $sArticleName, 'BasketLinkStart' => $sBasketLinkStart, 'BasketLinkEnd' => $sBasketLinkEnd, 'amount' => $iRealAmountAdded, 'sArticleAddedId' => $oArticle->id, 'basketItem' => $oArticle);

                if ($bItemWasUpdated) {
                    if (!$bAddAmountToExistingAmount && null !== $oExistingItem) {
                        $oMessage->AddMessage($sMessageHandler, 'BASKET-ARTICLE-AMOUNT-CHANGED', $aMessageVars);
                    } else {
                        $oMessage->AddMessage($sMessageHandler, 'BASKET-ADDED-ARTICLE-TO-BASKET', $aMessageVars);
                    }
                } else {
                    $aMessageVars['ArticleLinkStart'] = '<a href="'.$oArticle->GetDetailLink().'">';
                    $aMessageVars['ArticleLinkEnd'] = '</a>';
                    $oMessage->AddMessage($sMessageHandler, 'BASKET-REMOVED-ITEM', $aMessageVars);
                }

                if (false == $bIsInternalCall) {
                    // now redirect either to requested page, or to calling page
                    $iRedirectNodeId = null;
                    if (array_key_exists(self::URL_REDIRECT_NODE_ID_NAME, $aRequestData)) {
                        $iRedirectNodeId = $aRequestData[self::URL_REDIRECT_NODE_ID_NAME];
                    }
                    $this->RedirectAfterEvent($iRedirectNodeId);
                }
            }
        } elseif (!$bIsInternalCall) {
            $this->RedirectToCallingPage();
        }

        return $bDataValid;
    }

    /**
     * Hook is called after a successful change in the basket... oArticle is what was added/removed from the basket.
     *
     * @param TShopBasketArticle $oArticle
     * @param bool               $bItemWasUpdated
     */
    protected function PostUpdateItemInBasketEvent($oArticle, $bItemWasUpdated)
    {
    }

    /**
     * Hook is called after a successful removal in the basket... oArticle is what was removed from the basket.
     *
     * @param TShopBasketArticle $oArticle
     */
    protected function PostRemoveItemInBasketHook($oArticle)
    {
    }

    /**
     * redirects the user to the requested node after performing some action.
     *
     * @param string|int $iRequestRedirectNodeId
     */
    protected function RedirectAfterEvent($iRequestRedirectNodeId)
    {
        if (is_null($iRequestRedirectNodeId)) {
            $this->RedirectToCallingPage();
        } else {
            $checkoutSystemPage = $this->getSystemPageService()->getSystemPage('checkout');
            if (null !== $checkoutSystemPage && $checkoutSystemPage->fieldCmsTreeId == $iRequestRedirectNodeId) {
                $this->JumpToBasketPage();
            } else {
                $oNode = new TdbCmsTree();
                if ($oNode->Load($iRequestRedirectNodeId)) {
                    $url = $this->getTreeService()->getLinkToPageForTreeRelative($oNode);
                    $this->getRedirect()->redirect($url);
                } else {
                    $this->RedirectToCallingPage();
                }
            }
        }
    }

    /**
     * redirects to the calling page, passing all parameters back again to that page (except for the parameters
     * needed to perform the shop basket operation).
     */
    protected function RedirectToCallingPage()
    {
        $aParameters = array();
        $aIncludeParams = TdbShop::GetURLPageStateParameters();
        $oGlobal = TGlobal::instance();
        foreach ($aIncludeParams as $sKeyName) {
            if ($oGlobal->UserDataExists($sKeyName)) {
                $aParameters[$sKeyName] = $oGlobal->GetUserData($sKeyName);
            }
        }
        $sURL = $this->getActivePageService()->getActivePage()->GetRealURLPlain($aParameters);
        $this->getRedirect()->redirect($sURL);
    }

    /**
     * if this function returns true, then the result of the execute function will be cached.
     *
     * @deprecated - use a mapper instead (see getMapper)
     *
     * @return bool
     */
    public function _AllowCache()
    {
        if (0 != TShopBasket::GetInstance()->iTotalNumberOfUniqueArticles) {
            return false;
        }

        if (true === $this->cacheDisabledBecauseBasketHasMessages()) {
            return false;
        }

        return true;
    }

    private function cacheDisabledBecauseBasketHasMessages()
    {
        if (null !== $this->basketHasMessages) {
            return true === $this->basketHasMessages;
        }
        $oMessageManager = TCMSMessageManager::GetInstance();
        $this->basketHasMessages = $oMessageManager->ConsumerHasMessages(MTShopBasketCore::MSG_CONSUMER_NAME);

        return true === $this->basketHasMessages;
    }

    protected function setResponseCacheHeader(Request $request, \Symfony\Component\HttpFoundation\Response $response)
    {
        $response = parent::setResponseCacheHeader($request, $response);
        $response->setMaxAge(0); // since the basket changes as soon as the user adds an item we need a max age of zero
        return $response;
    }

    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgShop/shopBasket'));
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('common/userInput'));

        return $aIncludes;
    }

    private function getBasketItemKeyFromUserInput($aRequestData)
    {
        if (true === isset($aRequestData[self::URL_ITEM_BASKET_KEY_NAME])) {
            if ('' !== $aRequestData[self::URL_ITEM_BASKET_KEY_NAME]) {
                return $aRequestData[self::URL_ITEM_BASKET_KEY_NAME];
            }
        }

        return null;
    }

    /**
     * @param TShopBasketArticle $oArticle
     * @param array              $aRequestData
     * @param TShopBasketArticle $oExistingItem
     */
    private function getCustomDataFromRequest(TShopBasketArticle $oArticle, array $aRequestData, TShopBasketArticle $oExistingItem = null)
    {
        $existingCustomData = array();
        if (null !== $oExistingItem) {
            $existingCustomData = $oExistingItem->getCustomData();
        }

        $customData = null;
        if (true === isset($aRequestData[self::URL_CUSTOM_PARAMETER])) {
            $customData = $aRequestData[self::URL_CUSTOM_PARAMETER];
            if (!is_array($customData)) {
                $customData = array($customData);
            }
        }
        if (null === $customData && true === $oArticle->requiresCustomData()) {
            $customData = array();
        }
        $customData = array_merge($existingCustomData, $customData);

        return $customData;
    }

    private function customDataIsValid(IPkgShopBasketArticleWithCustomData $oArticle, array $customData, TCMSMessageManager $oMessage, $messageHandler)
    {
        /** @var $oArticle IPkgShopBasketArticleWithCustomData */
        $validationErrors = $oArticle->validateCustomData($customData);
        if (0 === count($validationErrors)) {
            return true;
        }

        foreach ($validationErrors as $validationError) {
            $msgHandler = $messageHandler;
            if ('' !== $validationError->getItemName()) {
                $msgHandler .= '-'.$validationError->getItemName();
            }
            $oMessage->AddMessage($msgHandler,
                $validationError->getErrorMessageCode(), $validationError->getAdditionalData()
            );
        }

        return false;
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }

    /**
     * @return BasketProductAmountValidatorInterface
     */
    private function getBasketAmountValidator()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.basket.basket_product_amount_validator');
    }

    /**
     * @return InputFilterUtilInterface
     */
    private function getInputFilterUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.input_filter');
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return PageServiceInterface
     */
    private function getPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.page_service');
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    /**
     * @return TreeServiceInterface
     */
    private function getTreeService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.tree_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
