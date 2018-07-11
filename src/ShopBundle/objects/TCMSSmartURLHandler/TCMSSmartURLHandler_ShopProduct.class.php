<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * get the url to the detail page for articles.
 *
 * @deprecated - use config/route.yml instead
/**/
class TCMSSmartURLHandler_ShopProduct extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();

        $oShop = &TdbShop::GetInstance($oURLData->iPortalId);
        if (!array_key_exists('product_url_mode', $oShop->sqlData)) {
            $oShop->sqlData['product_url_mode'] = 'V1';
        }

        switch ($oShop->sqlData['product_url_mode']) {
            case 'V2':
                $iPageId = $this->GetPageDefV2();
                if (false == $iPageId && AUTOMATICALLY_MAP_SHOP_PRODUCT_V1_URLS_TO_V2_IF_ACTIVE) {
                    $this->GetPageDefV1();
                }
                break;

            case 'V1':
            default:
                $iPageId = $this->GetPageDefV1();
                break;
        }

        return $iPageId;
    }

    protected function GetPageDefV1()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();

        $oShop = &TdbShop::GetInstance($oURLData->iPortalId);

        $sProductPath = $oShop->GetLinkToSystemPage('product');
        if ('.html' == substr($sProductPath, -5)) {
            $sProductPath = substr($sProductPath, 0, -5);
        }
        if ('http://' == substr($sProductPath, 0, 7) || 'https://' == substr($sProductPath, 0, 8)) {
            $sProductPath = substr($sProductPath, strpos($sProductPath, '/', 8));
        }
        if ('/' != substr($sProductPath, -1)) {
            $sProductPath .= '/';
        }
        if (strlen($sProductPath) < strlen($oURLData->sRelativeFullURL) && substr($oURLData->sRelativeFullURL, 0, strlen($sProductPath)) == $sProductPath) {
            $iNode = $oShop->GetSystemPageNodeId('product');
            $oNode = new TCMSTreeNode();
            /** @var $oNode TCMSTreeNode */
            $oNode->Load($iNode);
            $iPageId = $oNode->GetLinkedPage();
            $this->getRequest()->query->set('refererPageId', $iPageId);

            $articlePath = substr($oURLData->sRelativeFullURL, strlen($sProductPath));
            // now we should have a path like this: /cat1/cat2.../productname/id/productid
            // or /cat1/cat2...
            // so the last part may be our id...
            $aParts = explode('/', $articlePath);

            $aParts = $this->CleanPath($aParts);
            $aCatParts = $aParts;
            $iArtId = null;
            if (count($aParts) > 2) {
                $lastPartName = $aParts[count($aParts) - 2];
                if ('id' == $lastPartName) {
                    $iArtId = $aParts[count($aParts) - 1];
                    $this->aCustomURLParameters[MTShopArticleCatalogCore::URL_ITEM_ID] = $iArtId;
                    unset($aCatParts[count($aCatParts) - 1]); // article id
                    unset($aCatParts[count($aCatParts) - 1]); // "id" label
                    unset($aCatParts[count($aCatParts) - 1]); // article name

                    $oArticle = TdbShopArticle::GetNewInstance(); /** @var $oArticle TdbShopArticle */
                    if ($oArticle->Load($iArtId)) {
                        $this->getRequest()->attributes->set('activeShopArticle', $oArticle);
                    }
                } elseif ('ean' == $lastPartName) {
                    $ean = $aParts[count($aParts) - 1];
                    $oArticle = TdbShopArticle::GetNewInstance();
                    /** @var $oArticle TdbShopArticle */
                    if ($oArticle->LoadFromField('articlenumber', $ean)) {
                        // if there is a URL like http://www.mydomain.tdl/Produkt/-/ean/9783833809972
                        // we redirect to the correct detail page of the article...
                        $newURL = REQUEST_PROTOCOL.'://'.$oURLData->sOriginalDomainName.$oArticle->GetDetailLink();
                        $this->getRedirect()->redirect($newURL, Response::HTTP_MOVED_PERMANENTLY);
                    }
                }
            }
            // now try to find a category that matches the path
            $oPathCat = &TdbShopCategoryList::GetCategoryForCategoryPath($aCatParts);
            if (!is_null($oPathCat)) {
                $this->aCustomURLParameters[MTShopArticleCatalogCore::URL_CATEGORY_ID] = $oPathCat->id;
                $this->getRequest()->attributes->set('activeShopCategory', $oPathCat);
            }

            //$this->aCustomURLParameters['sCatPath'] = implode('/',$aCatParts);
            if (!is_null($iArtId) && 'V2' == $oShop->sqlData['product_url_mode']) {
                $oArticle = TdbShopArticle::GetNewInstance();
                if ($oArticle->Load($iArtId)) {
                    $sCatId = null;
                    if ($oPathCat) {
                        $sCatId = $oPathCat->id;
                    }
                    $sURL = $oArticle->GetDetailLink(false, $sCatId);
                    if ($sURL != $oURLData->sRelativeFullURL) {
                        $newURL = REQUEST_PROTOCOL.'://'.$oURLData->sOriginalDomainName.$sURL;
                        $this->getRedirect()->redirect($newURL, Response::HTTP_MOVED_PERMANENTLY);
                    }
                }
            }
        }

        return $iPageId;
    }

    protected function GetPageDefV2()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();
        // we have a match, if the url ident is found
        $iPosIdent = strpos($oURLData->sRelativeURL, TdbShopArticle::URL_ID_IDENT);
        if (false !== $iPosIdent) {
            // ok, must be an article...
            $sArticleIdString = substr($oURLData->sRelativeURL, $iPosIdent + strlen(TdbShopArticle::URL_ID_IDENT));
            // need to strip the part that is not ID...
            // replace possible separators
            $sArticleId = null;
            $sArticleIdString = str_replace(array('.', '/'), array('|', '|'), $sArticleIdString);
            $aTmpPart = explode('|', $sArticleIdString);
            if (is_array($aTmpPart) && count($aTmpPart) > 0) {
                $sArticleId = $aTmpPart[0];
            }

            $sSep = strpos($sArticleId, '_');
            $sCatId = '';
            if (false !== $sSep) {
                $sCatId = substr($sArticleId, 0, $sSep);
                $sArticleId = substr($sArticleId, $sSep + 1);
            }

            if ($sArticleId) {
                // need to map ident to real id
                if (PKG_SHOP_PRODUCT_URL_KEY_FIELD != 'id') {
                    $query = 'SELECT id FROM `shop_article` WHERE `'.MySqlLegacySupport::getInstance()->real_escape_string(PKG_SHOP_PRODUCT_URL_KEY_FIELD)."`= '".MySqlLegacySupport::getInstance()->real_escape_string($sArticleId)."'";
                    if ($tmpInf = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                        $sArticleId = $tmpInf['id'];
                    }
                }
                $query = "SELECT id FROM `shop_category` WHERE `cmsident`= '".MySqlLegacySupport::getInstance()->real_escape_string($sCatId)."'";
                if ($tmpInf = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                    $sCatId = $tmpInf['id'];
                }

                // to prevent double load, we push the id into TGlobal->userData and use the GetActive method of the article to load it
                /** @var Request $request */
                $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
                $request->query->set(MTShopArticleCatalogCore::URL_ITEM_ID, $sArticleId);
                $oArticle = TdbShop::GetActiveItem();
                /** @var $oArticle TdbShopArticle */
                if ($oArticle) {
                    // check if we need to 301 redirect because the path has changed
                    $sURL = $oArticle->GetDetailLink(true, $sCatId);
                    $sRealRelativeURL = REQUEST_PROTOCOL.'://'.$oURLData->sOriginalDomainName.$oURLData->sRelativeFullURL;
                    if ($sURL != $sRealRelativeURL) {
                        $sURL = $this->addParametersToUrl($sURL, $oURLData);
                        $this->getRedirect()->redirect($sURL, Response::HTTP_MOVED_PERMANENTLY);
                    } else {
                        // article found... set page
                        $oShop = &TdbShop::GetInstance($oURLData->iPortalId);
                        $iNode = $oShop->GetSystemPageNodeId('product');
                        $oNode = new TCMSTreeNode();
                        /** @var $oNode TCMSTreeNode */
                        $oNode->Load($iNode);
                        $iPageId = $oNode->GetLinkedPage();

                        $this->aCustomURLParameters[MTShopArticleCatalogCore::URL_ITEM_ID] = $sArticleId;
                        $this->aCustomURLParameters[MTShopArticleCatalogCore::URL_CATEGORY_ID] = $sCatId;
                    }
                }
            }
        }

        return $iPageId;
    }
}
