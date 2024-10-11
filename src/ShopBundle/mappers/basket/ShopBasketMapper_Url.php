<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class ShopBasketMapper_Url extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $requirements): void
    {
        $requirements->NeedsSourceObject('useRedirect', 'boolean', true, true);
        $requirements->NeedsSourceObject('removeModuleMethodCalls', 'boolean', true, true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $visitor,
        $cachingEnabled,
        IMapperCacheTriggerRestricted $cacheTriggerManager
    ): void {
        $useRedirect = $visitor->GetSourceObject('useRedirect');
        /** @var ShopServiceInterface $shopService */
        $shopService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
        $url = $shopService->getBasketLink($useRedirect);

        $removeModuleMethodCalls = $visitor->GetSourceObject('removeModuleMethodCalls');
        if ($removeModuleMethodCalls) {
            $url = $this->removeModuleMethodCalls($url);
        }

        $visitor->SetMappedValue('basketUrl', $url);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function removeModuleMethodCalls($url)
    {
        $url_params = explode('?', $url);
        if (1 === count($url_params)) {
            return $url;
        }
        $parameterList = explode('&', $url_params[1]);
        $newParameterList = array();
        $hasModuleFnc = false;
        foreach ($parameterList as $parameter) {
            if (0 === strpos($parameter, 'module_fnc')) {
                $hasModuleFnc = true;
            } else {
                $newParameterList[] = $parameter;
            }
        }
        if ($hasModuleFnc) {
            $retValue = $url_params[0];
            if (count($newParameterList) > 0) {
                $retValue .= '?'.join('&', $newParameterList);
            }
        } else {
            $retValue = $url;
        }

        return $retValue;
    }
}
