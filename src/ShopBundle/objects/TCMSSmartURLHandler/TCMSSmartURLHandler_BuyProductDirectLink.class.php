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
 * looks for URLS of the form /kaufen/ean/number... these will result in the article being
 * placed in the basket.
 *
 * @psalm-suppress UndefinedPropertyAssignment
 * @FIXME Writing data into `$OURLData` when there is no magic `__set` method for them defined.
 */
class TCMSSmartURLHandler_BuyProductDirectLink extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();

        $aParts = explode('/', $oURLData->sRelativeFullURL);

        $aParts = $this->CleanPath($aParts);

        if (3 == count($aParts) && TdbShopArticle::URL_EXTERNAL_TO_BASKET_REQUEST == $aParts[0] && 'id' == $aParts[1]) {
            $oArticle = TdbShopArticle::GetNewInstance();
            /** @var $oArticle TdbShopArticle */
            if ($oArticle->Load($aParts[2])) {
                // we need to perform a real redirect...
                $oShopConfig = &TdbShop::GetInstance($oURLData->iPortalId);
                $iNode = $oShopConfig->GetSystemPageNodeId('checkout');
                $oNode = new TCMSTreeNode();
                /** @var $oNode TCMSTreeNode */
                $oNode->Load($iNode);
                $iPageId = $oNode->GetLinkedPage();
                $this->getRequest()->query->set('pagedef', $iPageId);
                $oURLData->sOriginalURL = $oArticle->GetDetailLink(false);
                $oURLData->sRelativeFullURL = $oURLData->sOriginalURL;

                $sURL = $oArticle->GetToBasketLink(false, true);
                $sURL = str_replace('&amp;', '&', $sURL);
                $this->getRedirect()->redirect($sURL);
            }
        }

        return $iPageId;
    }
}
