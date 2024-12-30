<?php
/** @var $oStep TdbShopOrderStep */
/** @var $sSpotName string */
/** @var $aCallTimeVars array */

/** @var $oStepNext TdbShopOrderStep */
/** @var $oStepPrevious TdbShopOrderStep */
/** @var $sBackLink string */
?>
<div class="vRegisterAsNewCustomer">
    <form name="checkout" accept-charset="utf-8" method="post" action="<?=$oStep->GetStepURL(); ?>">
        <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($sSpotName); ?>]" value="ExecuteStep"/>
        <input type="hidden" name="umode" value="guest"/>

        <input type="submit" value="<?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.login_action_order_as_guest')); ?>"/>
    </form>
</div>