<?php
/** @var $oStep TdbCmsWizardStep */
$oUser = TdbDataExtranetUser::GetInstance();
$oExtranetConfig = TdbDataExtranet::GetInstance();

?>
<div class="step">
    <?php
    if (!empty($oStep->fieldName)) {
        echo '<h2>'.TGlobal::OutHTML($oStep->fieldName).'</h2>';
    }
echo $oStep->GetTextField('description');
?>
    <form name="checkout" accept-charset="utf-8" method="post" action="">
        <input type="hidden" name="module_fnc[<?php echo TGlobal::OutHTML($sSpotName); ?>]" value="ExecuteStep"/>
        <input type="hidden" name="<?php echo TGlobal::OutHTML(MTCMSWizardCore::URL_PARAM_STEP_METHOD); ?>" value=""/>

        <div class="stepnavibuttons">
            <?php if (!is_null($oStepNext)) {
                ?>
            <div class="formButtonNext"><input type="submit" value="weiter"/></div><?php
            } ?>
            <?php if ($sBackLink) {
                ?>
            <div class="formButtonBack"><a href="<?php echo $sBackLink; ?>" class="backLink"><?php echo ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.module_wizard_step.action_previous_step'); ?></a>
            </div><?php
            } ?>
        </div>
    </form>

</div>