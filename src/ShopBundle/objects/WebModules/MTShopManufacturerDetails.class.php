<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MTShopManufacturerDetails extends MTPkgViewRendererAbstractModuleMapper
{
    /**
     * @var TdbShopManufacturer
     */
    private $oActiveManufacturer = null;

    /**
     * {@inheritdoc}
     */
    public function Init()
    {
        parent::Init();
        $this->oActiveManufacturer = TdbShop::GetActiveManufacturer();
        if (null === $this->oActiveManufacturer) {
            if (null === $this->getPortalDomainService()->getActivePortal()) {
                $this->oActiveManufacturer = TdbShopManufacturer::GetNewInstance();
            } else {
                throw new NotFoundHttpException();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopManufacturer', $this->oActiveManufacturer);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $oManufacturer = $oVisitor->GetSourceObject('oObject');
        $oVisitor->SetMappedValue('oObject', $oManufacturer);
        if ($oManufacturer && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oManufacturer->table, $oManufacturer->id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function _AllowCache()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();

        if ($this->oActiveManufacturer) {
            $parameters['sManufacturerId'] = $this->oActiveManufacturer->id;
        }

        return $parameters;
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
