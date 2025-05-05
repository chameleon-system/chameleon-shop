<?php
/** @var $oListItem TdbPkgShopListfilterItem */
$oLocal = TCMSLocal::GetActive();
$bNoActive = false;
if ('1' == $oListItem->GetActiveValue()) {
    $sSelectedActive = 'checked="checked"';
} elseif ('0' == $oListItem->GetActiveValue()) {
    $sSelectedInactive = 'checked="checked"';
} else {
    $sSelectedNotSelected = 'checked="checked"';
    $bNoActive = true;
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

$bHasSelection = false;
?>
<?php if (0 != $iActiveItemCount && 0 != $iInactiveItemCount) {
    ?>
<div class="TPkgShopListfilterItem <?php echo get_class($oListItem); ?>">
    <div class="booleanLink">
        <div class="listFilterName"><?php echo TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="<?php if ($bNoActive) {
            ?>valueitems<?php
        } else {
            ?>valueitems_high<?php
        } ?> ">
            <ul>
                <?php
                    $sActive = '';
    if ('1' == $oListItem->GetActiveValue()) {
        $bHasSelection = true;
        $sActive = 'active';
    }
    echo '<li class="'.$sActive.'"><a href="'.$oListItem->GetAddFilterURL(1).'" rel="nofollow">Ja ('.$iActiveItemCount.')</a></li>';

    $sActive = '';
    if ('0' == $oListItem->GetActiveValue()) {
        $bHasSelection = true;
        $sActive = 'active';
    }
    echo '<li class="'.$sActive.'"><a href="'.$oListItem->GetAddFilterURL(0).'" rel="nofollow">Nein ('.$iInactiveItemCount.')</a></li>';
    if ($bHasSelection) {
        echo '<li><a href="'.$oListItem->GetAddFilterURL('').'" rel="nofollow">zurücksetzen</a></li>';
    } ?>
            </ul>
        </div>
    </div>
</div>
<?php
} ?>