<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentIPN_TCMSTableEditor_PkgShopPaymentIpnMessage extends TCMSTableEditor
{
    /**
     * adds table-specific buttons to the editor (add them directly to $this->oMenuItems).
     */
    protected function GetCustomMenuItems()
    {
        parent::GetCustomMenuItems();

        $aParam = TGlobal::instance()->GetUserData(null, array('module_fnc', '_noModuleFunction'));

        $aParam['module_fnc'] = array(TGlobal::instance()->GetExecutingModulePointer()->sModuleSpotName => 'ReplayIPN');
        $aParam['_noModuleFunction'] = 'true';

        $oMenuItem = new TCMSTableEditorMenuItem();
        $oMenuItem->sItemKey = 'ReplayIPN';
        $oMenuItem->sDisplayName = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_payment_ipn.action.replay_ipn');
        $oMenuItem->sIcon = 'fas fa-redo-alt';
        $oMenuItem->sOnClick = "document.location.href='?".TTools::GetArrayAsURL($aParam)."'";
        $this->oMenuItems->AddItem($oMenuItem);
    }

    /**
     * @return void
     */
    public function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'ReplayIPN';
    }

    /**
     * @return string|true
     */
    public function ReplayIPN()
    {
        /** @var TdbPkgShopPaymentIpnMessage $oMsg */
        $oMsg = $this->oTable;

        return $oMsg->replayIPN();
    }
}
