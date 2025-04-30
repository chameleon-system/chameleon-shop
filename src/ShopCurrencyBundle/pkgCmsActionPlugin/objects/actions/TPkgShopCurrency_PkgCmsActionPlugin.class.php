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

class TPkgShopCurrency_PkgCmsActionPlugin extends AbstractPkgActionPlugin
{
    /**
     * @param array<string, mixed> $aData
     * @param bool $bRedirect
     *
     * @return void
     */
    public function ChangeCurrency($aData, $bRedirect = true)
    {
        $sId = (isset($aData['sPkgShopCurrencyId'])) ? ($aData['sPkgShopCurrencyId']) : ('');
        if (empty($sId)) {
            return;
        }
        $oCurrency = TdbPkgShopCurrency::GetNewInstance($sId);
        if (false === is_array($oCurrency->sqlData)) {
            return;
        }
        $oCurrency->SetAsActive();
        if ($bRedirect) {
            $oActivePage = $this->getActivePageService()->getActivePage();
            $this->getRedirect()->redirect($oActivePage->GetRealURLPlain());
        }
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
