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

class TPkgShopMapper_OrderStep extends AbstractViewMapper
{
    /**
     * @var InputFilterUtilInterface
     */
    private $inputFilterUtil;

    /**
     * @param InputFilterUtilInterface|null $inputFilterUtil
     */
    public function __construct(InputFilterUtilInterface $inputFilterUtil = null)
    {
        if (null === $inputFilterUtil) {
            $this->inputFilterUtil = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.input_filter');
        } else {
            $this->inputFilterUtil = $inputFilterUtil;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('sBackLink');
        $oRequirements->NeedsSourceObject('shop', 'TdbShop', TShop::GetInstance());
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
