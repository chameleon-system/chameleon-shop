<?php
/** @var $oListItem TdbPkgShopListfilterItem */
$oLocal = &TCMSLocal::GetActive();
?>
<div class="TPkgShopListfilterItem <?=get_class($oListItem); ?>">
    <div class="tree">
        <div class="listFilterName"><?=TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="valueitems">
            <?php
            $aValues = $oListItem->GetOptions();

            foreach ($aValues as $sValue => $iCount) {
                $sSelected = '';
                if ($sValue == $oListItem->IsSelected($sValue)) {
                    $sSelected = 'checked="checked"';
                }
                echo '<label><input type="checkbox" name="'.$oListItem->GetURLInputName().'[]" value="'.TGlobal::OutHTML($sValue).'" '.$sSelected.'/>'.TGlobal::OutHTML($sValue.'('.$iCount.')').'</label><br />';
            }
            ?>
        </div>
    </div>
</div>