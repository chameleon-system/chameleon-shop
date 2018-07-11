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

class TPkgShopBasketMapper_VoucherInput extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oMessageManager', 'TCMSMessageManager', TCMSMessageManager::GetInstance());
        $oRequirements->NeedsSourceObject('oActivePage', 'TCMSActivePage', $this->getActivePageService()->getActivePage(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $sMessageConsumerParameterName = MTShopBasketCore::URL_REQUEST_PARAMETER.'['.MTShopBasket::URL_MESSAGE_CONSUMER_NAME.']';

        $sFormInputName = MTShopBasket::URL_REQUEST_PARAMETER.'['.MTShopBasket::URL_VOUCHER_CODE.']';
        $oMessageManager = $oVisitor->GetSourceObject('oMessageManager');
        /** @var $oMessageManager TCMSMessageManager* */
        if ($oMessageManager->ConsumerHasMessages('sAddVoucherField')) {
            $oVisitor->SetMappedValue('sErrorMessage', $oMessageManager->RenderMessages('sAddVoucherField'));
        }
        $oActivePage = $oVisitor->GetSourceObject('oActivePage');
        if (!is_null($oActivePage)) {
            $oVisitor->SetMappedValue('sAction', $oActivePage->GetRealURLPlain());
        }
        $oVisitor->SetMappedValue('sFormInputName', $sFormInputName);
        $oVisitor->SetMappedValue('sMessageConsumerParameterName', $sMessageConsumerParameterName);
        $oVisitor->SetMappedValue('sMessageConsumerName', 'sAddVoucherField');
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
