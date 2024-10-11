<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PageServiceInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class MTShopCategoryDetails extends MTPkgViewRendererAbstractModuleMapper
{
    /**
     * @var TdbShopCategory
     */
    private $oActiveCategory = null;

    /**
     * {@inheritdoc}
     */
    public function Init()
    {
        parent::Init();
        $this->oActiveCategory = $this->getShopService()->getActiveCategory();
        if (null === $this->oActiveCategory) {
            $homeUrl = $this->getPageService()->getLinkToPortalHomePageRelative();
            $this->getRedirectService()->redirect($homeUrl, Response::HTTP_MOVED_PERMANENTLY);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopCategory', $this->oActiveCategory);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        $oActiveCategory = $oVisitor->GetSourceObject('oObject');
        if ($oActiveCategory && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oActiveCategory->table, $oActiveCategory->id);
        }
        $oVisitor->SetMappedValue('oObject', $oActiveCategory);
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
        $parameter = parent::_GetCacheParameters();

        if ($this->oActiveCategory) {
            $parameter['sCategoryId'] = $this->oActiveCategory->id;
        }

        return $parameter;
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return PageServiceInterface
     */
    private function getPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.page_service');
    }

    /**
     * @return cmsCoreRedirect
     */
    private function getRedirectService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
