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
 */
class TCMSSmartURLHandler_ShopManufacturerProducts extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = TCMSSmartURLData::GetActive();

        $oShop = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId($oURLData->iPortalId);

        $sProductPath = $oShop->GetLinkToSystemPage('manufacturer');
        if ('.html' == substr($sProductPath, -5)) {
            $sProductPath = substr($sProductPath, 0, -5);
        }
        if ('http://' == substr($sProductPath, 0, 7) || 'https://' == substr($sProductPath, 0, 8)) {
            $sProductPath = substr($sProductPath, strpos($sProductPath, '/', 8));
        }

        if (strlen($sProductPath) < strlen($oURLData->sRelativeFullURL) && substr($oURLData->sRelativeFullURL, 0, strlen($sProductPath)) == $sProductPath) {
            $articlePath = substr($oURLData->sRelativeFullURL, strlen($sProductPath));
            // now we should have a path like this: /manufacturer-name/id/manufactuererId
            $aParts = explode('/', $articlePath);
            $aParts = $this->CleanPath($aParts);

            $manufacturerId = null;
            if (count($aParts) > 2 && 'id' == $aParts[1]) {
                $manufacturerId = $aParts[2];
            } elseif (1 == count($aParts)) {
                // URL without ID, only the brand name
                $oManufacturer = TdbShopManufacturer::GetNewInstance();
                if ($oManufacturer->LoadFromField('name', $aParts[0])) {
                    $manufacturerId = $oManufacturer->id;
                }
            }

            if (null !== $manufacturerId) {
                if (!array_key_exists(MTShopArticleCatalogCore::URL_FILTER, $this->aCustomURLParameters)) {
                    $this->aCustomURLParameters[MTShopArticleCatalogCore::URL_FILTER] = array();
                }
                $this->aCustomURLParameters[MTShopArticleCatalogCore::URL_FILTER]['shop_manufacturer_id'] = $manufacturerId;
                $this->aCustomURLParameters[MTShopManufacturerArticleCatalogCore::URL_MANUFACTURER_ID] = $manufacturerId;

                $iNode = $oShop->GetSystemPageNodeId('manufacturer');
                $oNode = new TCMSTreeNode();
                /** @var $oNode TCMSTreeNode */
                $oNode->Load($iNode);
                $iPageId = $oNode->GetLinkedPage();
            }
        }

        return $iPageId;
    }
}
