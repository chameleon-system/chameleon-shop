<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgRunFrontendAction_SendOrderStatusEMail implements IPkgRunFrontendAction
{
    /**
     * @param array $aParameter
     *
     * @return TPkgRunFrontendActionStatus|TdbShopOrderStatus
     *
     * @psalm-suppress UndefinedPropertyAssignment
     * @FIXME Properties `sMessage` and `sMessageType` do not exist on `TdbShopOrderStatus`
     */
    public function runAction($aParameter)
    {
        $oStatus = new TPkgRunFrontendActionStatus();
        if (isset($aParameter['order_status_id']) && isset($aParameter['order_id']) && !empty($aParameter['order_status_id']) && !empty($aParameter['order_id'])) {
            $oStatus = TdbShopOrderStatus::GetNewInstance();
            if ($oStatus->LoadFromFields(
                array('shop_order_id' => $aParameter['order_id'], 'id' => $aParameter['order_status_id'])
            )
            ) {
                $oStatusManager = new TPkgShopOrderStatusManager();
                $bSuccess = $oStatusManager->sendStatusMailToCustomer($oStatus);
                if ($bSuccess) {
                    $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_order_status.msg.mail_success');
                    $oStatus->sMessageType = 'MESSAGE';
                } else {
                    $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_order_status.error.mail_error', array('%error%' => $bSuccess));
                    $oStatus->sMessageType = 'ERROR';
                }
            } else {
                $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_order_status.error.order_not_found');
                $oStatus->sMessageType = 'ERROR';
            }
        } else {
            $oStatus->sMessage = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_order_status.error.missing_parameter');
            $oStatus->sMessageType = 'ERROR';
        }

        return $oStatus;
    }
}
