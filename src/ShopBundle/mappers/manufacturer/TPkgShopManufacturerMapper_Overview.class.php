<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Contracts\Translation\TranslatorInterface;

class TPkgShopManufacturerMapper_Overview extends AbstractViewMapper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface|null $translator
     */
    public function __construct(TranslatorInterface $translator = null)
    {
        if (null === $translator) {
            $this->translator = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator');
        } else {
            $this->translator = $translator;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oConfig', 'TdbShopManufacturerModuleConf');
        $oRequirements->NeedsSourceObject('oManufacturerList', 'TdbShopManufacturerList');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oConfig TdbShopManufacturerModuleConf */
        $oConfig = $oVisitor->GetSourceObject('oConfig');
        if ($oConfig && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oConfig->table, $oConfig->id);
        }
        /** @var $oManufacturerList TdbShopManufacturerList */
        $oManufacturerList = $oVisitor->GetSourceObject('oManufacturerList');
        if ($bCachingEnabled) {
            $oCacheTriggerManager->addTrigger('shop_manufacturer', null);
        }

        $oVisitor->SetMappedValue('sTitle', $oConfig->GetName());
        $oVisitor->SetMappedValue('sText', $oConfig->GetTextField('intro'));
        $oVisitor->SetMappedValue('aSearchInput', $this->getSearchInput());
        $oVisitor->SetMappedValue('aManufacturerList', $this->getManufacturerList($oManufacturerList, $oCacheTriggerManager, $bCachingEnabled));
    }

    /**
     * @return array
     */
    protected function getSearchInput()
    {
        $aSearchInput = array();

        $aSearchInput['sInputName'] = 'search';
        $aSearchInput['sIconClass'] = '';
        $aSearchInput['sBoxStyleClass'] = '';
        $aSearchInput['bRequired'] = false;
        $aSearchInput['sPlaceholder'] = $this->translator->trans('chameleon_system_shop.manufacturer');

        return $aSearchInput;
    }

    /**
     * @param TdbShopManufacturerList $oManufacturerList
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function getManufacturerList(TdbShopManufacturerList $oManufacturerList, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aManufacturerList = array();

        while ($oManufacturer = &$oManufacturerList->Next()) {
            $aItem = array();
            $aItem['sTitle'] = $oManufacturer->GetName();
            $sText = $oManufacturer->GetTextField('description_short');
            if (empty($sText)) {
                $sText = $oManufacturer->GetTextField('description');
            }
            $aItem['sText'] = $sText;
            $aItem['sURL'] = $oManufacturer->GetLinkProducts();

            $sMediaLogoId = $oManufacturer->GetImageCMSMediaId(1, 'cms_media_id');
            if (empty($sMediaLogoId) || 1 == $sMediaLogoId) {
                $sMediaLogoId = $oManufacturer->GetImageCMSMediaId(0, 'cms_media_id');
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger('cms_media', $sMediaLogoId);
                }
            }
            $aItem['sImageId'] = $sMediaLogoId;

            $aManufacturerList[] = $aItem;
        }

        $oManufacturerList->GoToStart();

        return $aManufacturerList;
    }
}
