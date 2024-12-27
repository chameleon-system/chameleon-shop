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
class TCMSSmartURLHandler_ShopProductExport extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = TCMSSmartURLData::GetActive();
        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId($oURLData->iPortalId);

        $sProductPath = $oShop->GetLinkToSystemPage('productexport');
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
            if (count($aParts) >= 6) {
                $iNode = $oShop->GetSystemPageNodeId('productexport');
                $oNode = new TCMSTreeNode();
                /** @var $oNode TCMSTreeNode */
                $oNode->Load($iNode);

                if (7 == count($aParts)) {
                    $bFound = false;
                    $oChildNodes = $oNode->GetChildren();
                    /** @var $oChildNode TdbCmsTree */
                    while (($oChildNode = $oChildNodes->Next()) && false === $bFound) {
                        if ($oChildNode->fieldUrlname == $aParts[0]) {
                            $bFound = true;
                            $iPageId = $oChildNode->GetLinkedPage();
                        }
                    }
                    array_shift($aParts);
                } else {
                    $iPageId = $oNode->GetLinkedPage();
                }

                if ('sModuleSpotName' == $aParts[0] && 'view' == $aParts[2] && 'key' == $aParts[4]) {
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
                    $this->getRequest()->attributes->set('view', $sView);
                    $this->getRequest()->attributes->set('key', $sKey);
                }
            }
        }

        return $iPageId;
    }
}
