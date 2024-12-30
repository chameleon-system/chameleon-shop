<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgRunFrontendAction_SendOrderEMail implements IPkgRunFrontendAction
{
    /**
     * @param array $aParameter
     *
     * @return TPkgRunFrontendActionStatus
     */
    public function runAction($aParameter)
    {
        $oStatus = new TPkgRunFrontendActionStatus();
        if (isset($aParameter['email']) && isset($aParameter['order_id']) && !empty($aParameter['email']) && !empty($aParameter['order_id'])) {
            $oOrder = TdbShopOrder::GetNewInstance();
            if ($oOrder->Load($aParameter['order_id'])) {
                $bSuccess = $oOrder->SendOrderNotification($aParameter['email']);
                if ($bSuccess) {
                    $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.orders.msg_order_confirm_sent', array('%mail%' => $aParameter['email']));
                    $oStatus->sMessageType = 'MESSAGE';
                } else {
                    $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.orders.error_sending_confirm_mail', array('%error%' => $bSuccess));
                    $oStatus->sMessageType = 'ERROR';
                }
            } else {
                $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.orders.error_sending_confirm_order_not_found');
                $oStatus->sMessageType = 'ERROR';
            }
        } else {
            $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.orders.error_sending_confirm_order_missing_parameter');
            $oStatus->sMessageType = 'ERROR';
        }

        return $oStatus;
    }
}
