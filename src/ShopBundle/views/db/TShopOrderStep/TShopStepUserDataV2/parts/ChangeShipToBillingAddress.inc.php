<?php
/** @var $oUserData TdbDataExtranetUser */
/** @var $oShippingAddress TdbDataExtranetUserAddress */
/** @var $oBillingAddress TdbDataExtranetUserAddress */
/** @var $bShipToBillingAddress tinyint */

// allows the user to select if he wants to ship to the billing address
$sChecked = '';
if ('0' == $bShipToBillingAddress) {
    $sChecked = 'checked="checked"';
}
?>
<script type="text/javascript">
    document.write('<input type="hidden" name="ChangeShipToBillingState" value="" />');
    document.write('<label><input type="checkbox" name="bShipToBillingAddress" value="0" <?=$sChecked; ?> onclick="document.user.ChangeShipToBillingState.value=\'1\'; document.user.submit()"/> <?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_checkout.user_different_billing_address')); ?></label>');
</script>
<noscript>
    <?php
    $sChangeShippingButtonText = '';
    if ($bShipToBillingAddress) {
        $sChangeShippingButtonText = 'chameleon_system_shop.module_checkout.user_different_billing_address';
    } else {
        $sChangeShippingButtonText = 'chameleon_system_shop.module_checkout.user_ship_to_billing_address';
    }
    ?>
    <input type="hidden" name="bShipToBillingAddress"
           value="<?php if ($bShipToBillingAddress) {
        echo '0';
    } else {
        echo '1';
    } ?>"/>
    <input type="submit" name="ChangeShipToBillingState"
           value="<?=TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans($sChangeShippingButtonText)); ?>"/>
</noscript>
