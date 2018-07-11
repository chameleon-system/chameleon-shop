<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\TreeServiceInterface;

/**
 * get the path to the product catalog.
 *
 * @deprecated - use TPkgShopCategoryRouteCollectionGenerator instead
 */
class TCMSSmartURLHandler_ShopProducts extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();

        $oShop = &TdbShop::GetInstance($oURLData->iPortalId);

        $sProductPath = $oShop->GetLinkToSystemPage('products');
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
                }
            }
            // now try to find a category that matches the path
            $oPathCat = &TdbShopCategoryList::GetCategoryForCategoryPath($aCatParts);
            if (!is_null($oPathCat)) {
                $this->aCustomURLParameters[MTShopArticleCatalogCore::URL_CATEGORY_ID] = $oPathCat->id;
                $treeService = $this->getTreeService();
                $oNode = $treeService->getById($oPathCat->fieldDetailPageCmsTreeId);
                if (empty($oPathCat->fieldDetailPageCmsTreeId) || null === $oNode) {
                    $iNode = $oShop->GetSystemPageNodeId('products');
                    $oNode = $treeService->getById($iNode);
                }
                $iPageId = $oNode->GetLinkedPage();
            }
        }

        return $iPageId;
    }

    /**
     * @return TreeServiceInterface
     */
    private function getTreeService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.tree_service');
    }
}
