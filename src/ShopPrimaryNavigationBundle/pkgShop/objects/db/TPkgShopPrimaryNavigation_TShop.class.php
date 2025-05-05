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

class TPkgShopPrimaryNavigation_TShop extends TPkgShopPrimaryNavigation_TShopAutoParent
{
    /**
     * @var TdbPkgShopPrimaryNaviList[]
     */
    private $primaryNavigationList = [];

    /**
     * overwrite the method to allow caching.
     *
     * @return TdbPkgShopPrimaryNaviList
     */
    public function GetFieldShopPrimaryNaviList()
    {
        $activePortal = self::getPortalDomainService()->getActivePortal();
        if (null === $activePortal) {
            if (false === isset($this->primaryNavigationList['no-portal'])) {
                $this->primaryNavigationList['no-portal'] = TdbPkgShopPrimaryNaviList::GetList();
            }

            return $this->primaryNavigationList['no-portal'];
        }
        $activePortalId = $activePortal->id;
        if (isset($this->primaryNavigationList[$activePortalId])) {
            return $this->primaryNavigationList[$activePortalId];
        }
        $this->primaryNavigationList[$activePortalId] = TdbPkgShopPrimaryNaviList::GetListForCmsPortalId($activePortalId);

        return $this->primaryNavigationList[$activePortalId];
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private static function getPortalDomainService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
