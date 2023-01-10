<?php
/** @var $oListItem TdbPkgShopListfilterItem */
$oLocal = TCMSLocal::GetActive();
$aValues = $oListItem->GetOptions();
if (count($aValues) > 0) {
    $sLongCSS = '';
    if (count($aValues) > 7) {
        $sLongCSS = 'longValueItemList';
    } ?>
<div class="TPkgShopListfilterItem <?=get_class($oListItem); ?>">
    <div class="valuelistSelectOne">
        <div class="listFilterName"><?=TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="<?php if ($oListItem->IsActiveFilter()) {
        echo 'valueitems_high';
    } else {
        echo 'valueitems';
    } ?> <?=$sLongCSS; ?>">
            <?php
            echo '<ul>';
    $bHasSelection = false;
    foreach ($aValues as $sValue => $iCount) {
        $sSelected = '';
        if ($sValue == $oListItem->IsSelected($sValue)) {
            $sSelected = 'active';
            $bHasSelection = true;
        }
        echo '<li class="'.$sSelected.'"><a href="'.$oListItem->GetAddFilterURL(array($sValue)).'" rel="nofollow">'.TGlobal::OutHTML($sValue).' ('.$iCount.')</a></li>';
    }
    if ($bHasSelection) {
        echo '<li><a href="'.$oListItem->GetAddFilterURL(array()).'" rel="nofollow">zurücksetzen</a></li>';
    }
    echo '</ul>'; ?>
        </div>
    </div>
</div>
<?php
}
