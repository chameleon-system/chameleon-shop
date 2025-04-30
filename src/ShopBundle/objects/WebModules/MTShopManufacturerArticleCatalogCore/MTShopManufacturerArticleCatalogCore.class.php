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

/**
 * module is used to show the products for one manufacturer.
 */
class MTShopManufacturerArticleCatalogCore extends MTShopArticleCatalogCore
{
    public const URL_MANUFACTURER_ID = 'manufacturerid';

    /**
     * @var string|null
     */
    protected $iActiveManufacturerId;

    /**
     * current active manufacturer.
     *
     * @var TdbShopManufacturer|null
     */
    protected $oActiveManufacturer;

    protected $bAllowHTMLDivWrapping = true;

    public function Init()
    {
        parent::Init();

        if ($this->global->UserDataExists(self::URL_MANUFACTURER_ID)) {
            $this->iActiveManufacturerId = $this->global->GetUserData(self::URL_MANUFACTURER_ID);
        }
    }

    /**
     * load manufacturer and related data.
     *
     * @return void
     */
    protected function LoadManufacturer()
    {
        if (is_null($this->oActiveManufacturer) && !is_null($this->iActiveManufacturerId)) {
            $this->oActiveManufacturer = TdbShopManufacturer::GetNewInstance();
            if (!$this->oActiveManufacturer->Load($this->iActiveManufacturerId)) {
                $this->oActiveManufacturer = null;
                // unable to find manufacturer - redirect to not found page
                $this->getRedirect()->redirect($this->getPortalDomainService()->getActivePortal()->GetFieldPageNotFoundNodePageURL());
            }
        }
    }

    public function Execute()
    {
        parent::Execute();
        $this->LoadManufacturer();
        $this->data['oManufacturer'] = $this->oActiveManufacturer;

        if (is_null($this->oActiveManufacturer)) {
            $this->ManufacturerNotFoundHook();
        }

        return $this->data;
    }

    /**
     * method is called when the module is unable to find the requested manufacturer.
     *
     * @return void
     */
    protected function ManufacturerNotFoundHook()
    {
    }

    /**
     * return an assoc array of parameters that describe the state of the module.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();
        $parameters['iActiveManufacturerId'] = $this->iActiveManufacturerId;

        return $parameters;
    }

    /**
     * define any head includes the step needs.
     *
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        if (!is_array($aIncludes)) {
            $aIncludes = [];
        }

        $this->LoadManufacturer();
        if (!is_null($this->oActiveManufacturer) && !empty($this->oActiveManufacturer->fieldCss)) {
            $aIncludes[] = '<link href="'.TGlobal::OutHTML($this->oActiveManufacturer->fieldCss).'" rel="stylesheet" type="text/css" />';
        }

        return $aIncludes;
    }

    /**
     * @param string $sOrderListBy
     * @param array $filter
     *
     * @return TdbShopArticleList
     */
    protected function getListWhenNoCategoryDefined($sOrderListBy, $filter)
    {
        return TdbShopArticleList::LoadArticleList($sOrderListBy, -1, $filter);
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
