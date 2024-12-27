<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;

/**
 * get the path to the product catalog.
 * /**/
class TCMSSmartURLHandler_ShopRemoteSearch extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $pageId = false;
        $oURLData = TCMSSmartURLData::GetActive();

        $shop = ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId($oURLData->iPortalId);

        $productPath = $this->getSystemPageService()->getLinkToSystemPageRelative('remotesearch');
        if ('.html' === substr($productPath, -5)) {
            $productPath = substr($productPath, 0, -5);
        }
        if ('http://' === substr($productPath, 0, 7) || 'https://' == substr($productPath, 0, 8)) {
            $productPath = substr($productPath, strpos($productPath, '/', 8));
        }

        if (strlen($productPath) < strlen($oURLData->sRelativeFullURL) && substr($oURLData->sRelativeFullURL, 0, strlen($productPath)) == $productPath) {
            $articlePath = substr($oURLData->sRelativeFullURL, strlen($productPath));
            if (false === strpos($articlePath, '/')) {
                $articlePath = str_replace('-', '/', $articlePath);
            }
            // now we should have a path like this: /sModuleSpotName/NAME/view/NAME/key/key[.csv|.txt|.xml]
            $aParts = explode('/', $articlePath);
            $aParts = $this->CleanPath($aParts);
            if (6 == count($aParts)) {
                if (MTShopRemoteSearchCore::URL_MODULE_SPOT == $aParts[0] && MTShopRemoteSearchCore::URL_VIEW == $aParts[2] && MTShopRemoteSearchCore::URL_KEY == $aParts[4]) {
                    $sSpotName = $aParts[1];
                    $sView = $aParts[3];
                    $sKey = $aParts[5];
                    // clean key...
                    $aEndings = ['.csv', '.txt', '.xml'];
                    foreach ($aEndings as $sEnding) {
                        if (substr($sKey, -strlen($sEnding)) == $sEnding) {
                            $sKey = substr($sKey, 0, strlen($sKey) - strlen($sEnding));
                        }
                    }
                    $this->aCustomURLParameters['module_fnc'] = [$sSpotName => 'ExecuteExport'];
                    $this->aCustomURLParameters['view'] = $sView;
                    $this->aCustomURLParameters['key'] = $sKey;

                    $iNode = $shop->GetSystemPageNodeId('remotesearch');
                    $oNode = new TCMSTreeNode();
                    $oNode->Load($iNode);
                    $pageId = $oNode->GetLinkedPage();
                }
            }
        }

        return $pageId;
    }

    private function getSystemPageService(): SystemPageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.system_page_service');
    }
}
