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
 * get the path to the product catalog.
/**/
class TCMSSmartURLHandler_ShopRemoteSearch extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = TCMSSmartURLData::GetActive();

        $oShop = TdbShop::GetInstance($oURLData->iPortalId);

        $sProductPath = $oShop->GetLinkToSystemPage('remotesearch');
        if ('.html' == substr($sProductPath, -5)) {
            $sProductPath = substr($sProductPath, 0, -5);
        }
        if ('http://' == substr($sProductPath, 0, 7) || 'https://' == substr($sProductPath, 0, 8)) {
            $sProductPath = substr($sProductPath, strpos($sProductPath, '/', 8));
        }

        if (strlen($sProductPath) < strlen($oURLData->sRelativeFullURL) && substr($oURLData->sRelativeFullURL, 0, strlen($sProductPath)) == $sProductPath) {
            $articlePath = substr($oURLData->sRelativeFullURL, strlen($sProductPath));
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
                    $aEndings = array('.csv', '.txt', '.xml');
                    foreach ($aEndings as $sEnding) {
                        if (substr($sKey, -strlen($sEnding)) == $sEnding) {
                            $sKey = substr($sKey, 0, strlen($sKey) - strlen($sEnding));
                        }
                    }
                    $this->aCustomURLParameters['module_fnc'] = array($sSpotName => 'ExecuteExport');
                    $this->aCustomURLParameters['view'] = $sView;
                    $this->aCustomURLParameters['key'] = $sKey;

                    $iNode = $oShop->GetSystemPageNodeId('remotesearch');
                    $oNode = new TCMSTreeNode();
                    /** @var $oNode TCMSTreeNode */
                    $oNode->Load($iNode);
                    $iPageId = $oNode->GetLinkedPage();
                }
            }
        }

        return $iPageId;
    }
}
