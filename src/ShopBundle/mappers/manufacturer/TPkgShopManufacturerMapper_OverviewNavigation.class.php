<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\UrlNormalization\UrlNormalizationUtil;

class TPkgShopManufacturerMapper_OverviewNavigation extends AbstractViewMapper
{
    /**
     * @var UrlNormalizationUtil
     */
    private $urlNormalizationUtil;

    /**
     * @param UrlNormalizationUtil|null $urlNormalizationUtil
     */
    public function __construct(UrlNormalizationUtil $urlNormalizationUtil = null)
    {
        if (null === $urlNormalizationUtil) {
            $this->urlNormalizationUtil = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.url_normalization');
        } else {
            $this->urlNormalizationUtil = $urlNormalizationUtil;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oManufacturerList', 'TdbShopManufacturerList');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oManufacturerList TdbShopManufacturerList */
        $oManufacturerList = $oVisitor->GetSourceObject('oManufacturerList');
        if ($bCachingEnabled) {
            $oCacheTriggerManager->addTrigger('shop_manufacturer', null);
        }

        $oVisitor->SetMappedValue('aNavigation', $this->getNavigation());
        $oVisitor->SetMappedValue('aManufacturerList', $this->getManufacturerList($oManufacturerList));
    }

    /**
     * @return array
     */
    private function getNavigation()
    {
        $aTree = array();

        $aNavigation = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0-9');
        foreach ($aNavigation as $sNavigationItem) {
            $aItem = array();
            $aItem['bIsActive'] = false;
            $aItem['bIsExpanded'] = false;
            $aItem['sLink'] = '#'.$sNavigationItem;
            $aItem['sTitle'] = $sNavigationItem;
            $aItem['sSeoTitle'] = $this->urlNormalizationUtil->normalizeUrl($sNavigationItem);

            $aTree[] = $aItem;
        }

        return $aTree;
    }

    /**
     * @param TdbShopManufacturerList $oManufacturerList
     *
     * @return array
     */
    private function getManufacturerList(TdbShopManufacturerList $oManufacturerList)
    {
        $navigationKeyList = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0-9');
        $manufacturerList = array();

        foreach ($navigationKeyList as $sNavigationItem) {
            $manufacturerList[$sNavigationItem] = array(
                'sTitle' => $sNavigationItem,
                'bShowMinimizer' => true,
                'aItemList' => array(),
            );
        }

        while ($manufacturer = $oManufacturerList->Next()) {
            $manufacturerName = $manufacturer->GetName();
            $normalizedManufacturerName = $this->urlNormalizationUtil->normalizeUrl($manufacturerName);
            $firstLetter = strtoupper(mb_substr($normalizedManufacturerName, 0, 1));

            $aItem = array();
            $aItem['sLink'] = $manufacturer->GetLinkProducts();
            $aItem['sTitle'] = $manufacturerName;

            $key = $firstLetter;
            if (is_numeric($firstLetter)) {
                $key = '0-9';
            }

            $manufacturerList[$key]['aItemList'][] = $aItem;
        }

        $oManufacturerList->GoToStart();

        return $manufacturerList;
    }
}
