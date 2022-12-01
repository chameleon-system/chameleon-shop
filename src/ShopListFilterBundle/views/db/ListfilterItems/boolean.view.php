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
<div class="TPkgShopListfilterItem <?=get_class($oListItem); ?>">
    <div class="boolean">
        <div class="listFilterName"><?=TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="valueitems">
            <label><input type="radio" name="<?=TGlobal::OutHTML($oListItem->GetURLInputName()); ?>"
                          value="1" <?=$sSelectedActive; ?> /> Ja
                (<?=TGlobal::OutHTML($oLocal->FormatNumber($iActiveItemCount, 0)); ?>)</label>
            <label><input type="radio" name="<?=TGlobal::OutHTML($oListItem->GetURLInputName()); ?>"
                          value="0" <?=$sSelectedInactive; ?> /> Nein
                (<?=TGlobal::OutHTML($oLocal->FormatNumber($iInactiveItemCount, 0)); ?>)</label>
            <label><input type="radio" name="<?=TGlobal::OutHTML($oListItem->GetURLInputName()); ?>"
                          value="" <?=$sSelectedNotSelected; ?> /> Egal
                (<?=TGlobal::OutHTML($oLocal->FormatNumber($iInactiveItemCount + $iActiveItemCount, 0)); ?>)</label>
        </div>
    </div>
</div>