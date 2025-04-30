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
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;

class MTPkgShopPrimaryNavigation extends MTPkgViewRendererAbstractModuleMapper
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $activePortal = $this->getPortalDomainService()->getActivePortal();
        if ($bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($activePortal->table, $activePortal->id);
        }
        $oVisitor->SetMappedValue('oPortal', $activePortal);
    }

    public function _AllowCache()
    {
        return true;
    }

    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();

        $parameters['sActivePageId'] = $this->getActivePageId();

        $parameters['activeUserGroups'] = implode(',', $this->getActiveUserGroups());
        $parameters['activeCategoryId'] = $this->getActiveCategoryId();

        return $parameters;
    }

    /**
     * @return string|null
     */
    private function getActivePageId()
    {
        /** @var ActivePageServiceInterface $activePageService */
        $activePageService = ServiceLocator::get('chameleon_system_core.active_page_service');
        $activePage = $activePageService->getActivePage();

        return $activePage->id;
    }

    /**
     * @return string[]
     */
    private function getActiveUserGroups()
    {
        $user = $this->getExtranetUserProvider()->getActiveUser();
        if (null === $user) {
            return [];
        }

        return $user->GetUserGroupIds();
    }

    /**
     * @return string|null
     */
    private function getActiveCategoryId()
    {
        $activeCategory = $this->getActiveCategory();
        if (null === $activeCategory) {
            return null;
        }

        return $activeCategory->id;
    }

    /**
     * @return TdbShopCategory
     */
    private function getActiveCategory()
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory();
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    public function getExtranetUserProvider(): ExtranetUserProviderInterface
    {
        return ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
