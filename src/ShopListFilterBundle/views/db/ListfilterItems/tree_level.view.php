<?php
/*@var $oListItem TdbPkgShopListfilterItem */
$oLocal = TCMSLocal::GetActive();
?>
<div class="TPkgShopListfilterItem <?=get_class($oListItem); ?> <?=$aCallTimeVars['CountClass']; ?>">
    <div class="tree">
        <?=$oListItem->GetRenderedCategoryTree(); ?>
    </div>
</div>
