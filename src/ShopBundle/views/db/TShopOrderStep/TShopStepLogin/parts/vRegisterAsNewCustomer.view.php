<?php
/** @var $oStep TdbShopOrderStep */
/* @var $sSpotName string */
/* @var $aCallTimeVars array */

/* @var $oStepNext TdbShopOrderStep */
/* @var $oStepPrevious TdbShopOrderStep */
/* @var $sBackLink string */
?>
<div class="vRegisterAsNewCustomer">
    <form name="checkout" accept-charset="utf-8" method="post" action="<?php echo $oStep->GetStepURL(); ?>">
        <input type="hidden" name="module_fnc[<?php echo TGlobal::OutHTML($sSpotName); ?>]" value="ExecuteStep"/>
        <input type="hidden" name="umode" value="register"/>

        <input type="submit" value="<?php echo TGlobal::OutHTML(ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.login_action_register')); ?>"/>
    </form>
</div>