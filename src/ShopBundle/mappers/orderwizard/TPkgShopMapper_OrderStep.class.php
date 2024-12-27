<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class TPkgShopMapper_OrderStep extends AbstractViewMapper
{
    public function __construct(
        protected readonly InputFilterUtilInterface $inputFilterUtil,
        protected readonly ShopServiceInterface $shopService)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('sBackLink');
        $activeShop = $this->shopService->getActiveShop();
        $oRequirements->NeedsSourceObject('shop', 'TdbShop', $activeShop);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $shop TdbShop */
        $shop = $oVisitor->GetSourceObject('shop');
        $oVisitor->SetMappedValueFromArray($shop->GetSQLWithTablePrefix());
        $oVisitor->SetMappedValue('sSupportMail', $shop->fieldCustomerServiceEmail);
        $sBackLink = $oVisitor->GetSourceObject('sBackLink');
        $sBackLink = $this->inputFilterUtil->filterValue($sBackLink, TCMSUserInput::FILTER_URL_INTERNAL);
        $oVisitor->SetMappedValue('sBackLink', $sBackLink);
    }
}
