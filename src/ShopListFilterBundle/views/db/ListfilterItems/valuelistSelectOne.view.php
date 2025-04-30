<?php
/** @var $oListItem TdbPkgShopListfilterItem */
$oLocal = TCMSLocal::GetActive();
$aValues = $oListItem->GetOptions();
if (count($aValues) > 0) {
    $sLongCSS = '';
    if (count($aValues) > 7) {
        $sLongCSS = 'longValueItemList';
    } ?>
<div class="TPkgShopListfilterItem <?php echo get_class($oListItem); ?>">
    <div class="valuelistSelectOne">
        <div class="listFilterName"><?php echo TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="<?php if ($oListItem->IsActiveFilter()) {
            echo 'valueitems_high';
        } else {
            echo 'valueitems';
        } ?> <?php echo $sLongCSS; ?>">
            <?php
                echo '<ul>';
    $bHasSelection = false;
    foreach ($aValues as $sValue => $iCount) {
        $sSelected = '';
        if ($sValue == $oListItem->IsSelected($sValue)) {
            $sSelected = 'active';
            $bHasSelection = true;
        }
        echo '<li class="'.$sSelected.'"><a href="'.$oListItem->GetAddFilterURL([$sValue]).'" rel="nofollow">'.TGlobal::OutHTML($sValue).' ('.$iCount.')</a></li>';
    }
    if ($bHasSelection) {
        echo '<li><a href="'.$oListItem->GetAddFilterURL([]).'" rel="nofollow">zurücksetzen</a></li>';
    }
    echo '</ul>'; ?>
        </div>
    </div>
</div>
<?php
}
