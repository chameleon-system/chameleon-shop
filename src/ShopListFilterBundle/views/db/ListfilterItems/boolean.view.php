<?php
/** @var $oListItem TdbPkgShopListfilterItem */
$oLocal = TCMSLocal::GetActive();
$sSelectedActive = '';
$sSelectedInactive = '';
$sSelectedNotSelected = '';
if ('1' == $oListItem->GetActiveValue()) {
    $sSelectedActive = 'checked="checked"';
} elseif ('0' == $oListItem->GetActiveValue()) {
    $sSelectedInactive = 'checked="checked"';
} else {
    $sSelectedNotSelected = 'checked="checked"';
}

$aValues = $oListItem->GetOptions();
$iActiveItemCount = 0;
$iInactiveItemCount = 0;
if (array_key_exists(1, $aValues)) {
    $iActiveItemCount = $aValues[1];
}
if (array_key_exists(0, $aValues)) {
    $iInactiveItemCount = $aValues[0];
}
?>
<div class="TPkgShopListfilterItem <?php echo get_class($oListItem); ?>">
    <div class="boolean">
        <div class="listFilterName"><?php echo TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="valueitems">
            <label><input type="radio" name="<?php echo TGlobal::OutHTML($oListItem->GetURLInputName()); ?>"
                          value="1" <?php echo $sSelectedActive; ?> /> Ja
                (<?php echo TGlobal::OutHTML($oLocal->FormatNumber($iActiveItemCount, 0)); ?>)</label>
            <label><input type="radio" name="<?php echo TGlobal::OutHTML($oListItem->GetURLInputName()); ?>"
                          value="0" <?php echo $sSelectedInactive; ?> /> Nein
                (<?php echo TGlobal::OutHTML($oLocal->FormatNumber($iInactiveItemCount, 0)); ?>)</label>
            <label><input type="radio" name="<?php echo TGlobal::OutHTML($oListItem->GetURLInputName()); ?>"
                          value="" <?php echo $sSelectedNotSelected; ?> /> Egal
                (<?php echo TGlobal::OutHTML($oLocal->FormatNumber($iInactiveItemCount + $iActiveItemCount, 0)); ?>)</label>
        </div>
    </div>
</div>