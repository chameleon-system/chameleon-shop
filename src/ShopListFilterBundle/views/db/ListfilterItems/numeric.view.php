<?php
/** @var $oListItem TdbPkgShopListfilterItem */
$oLocal = TCMSLocal::GetActive();
?>
<div class="TPkgShopListfilterItem <?php echo get_class($oListItem); ?>">
    <div class="numeric">
        <div class="listFilterName"><?php echo TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="valueitems">
            <?php
            $aValues = $oListItem->GetOptions();

echo 'Start: <select name="'.$oListItem->GetURLInputName().'[dStartValue]">';
echo '<option value="">keine Einschränkung</option>';
foreach ($aValues as $sValue => $iCount) {
    $sSelected = '';
    if ($sValue == $oListItem->GetActiveStartValue()) {
        $sSelected = 'selected="selected"';
    }
    echo '<option value="'.TGlobal::OutHTML($sValue).'" '.$sSelected.'>'.TGlobal::OutHTML($oLocal->FormatNumber($sValue, 2).'€ ('.$iCount.')').'</option>';
}
echo '</select>';

reset($aValues);
echo 'End: <select name="'.$oListItem->GetURLInputName().'[dEndValue]">';
echo '<option value="">keine Einschränkung</option>';
$aValues = array_reverse($aValues);
foreach ($aValues as $sValue => $iCount) {
    $sSelected = '';
    if ($sValue == $oListItem->GetActiveEndValue()) {
        $sSelected = 'selected="selected"';
    }
    echo '<option value="'.TGlobal::OutHTML($sValue).'" '.$sSelected.'>'.TGlobal::OutHTML($oLocal->FormatNumber($sValue, 2).'€ ('.$iCount.')').'</option>';
}
echo '</select>';
?>
        </div>


    </div>
</div>