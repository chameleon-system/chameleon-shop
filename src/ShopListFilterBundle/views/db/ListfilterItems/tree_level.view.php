<?php
/* @var $oListItem TdbPkgShopListfilterItem */
$oLocal = TCMSLocal::GetActive();
?>
<div class="TPkgShopListfilterItem <?php echo get_class($oListItem); ?> <?php echo $aCallTimeVars['CountClass']; ?>">
    <div class="tree">
        <?php echo $oListItem->GetRenderedCategoryTree(); ?>
    </div>
</div>
