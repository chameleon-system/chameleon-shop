<?php
/** @var $oListfilter TdbPkgShopListfilter */
$oActivePage = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service')->getActivePage();
$oGlobal = TGlobal::instance();

?>
<div class="TPkgShopListfilter">
    <div class="standard">
        <form name="TdbPkgShopListfilter" method="get" accept-charset="utf8"
              action="<?php echo $oActivePage->GetRealURLPlain(); ?>">
            <input type="hidden" name="<?php echo TdbPkgShopListfilter::URL_PARAMETER_IS_NEW_REQUEST; ?>" value="1"/>
            <input type="hidden" name="<?php echo TShopModuleArticlelistFilterSearch::PARAM_QUERY; ?>"
                   value="<?php echo TGlobal::OutHTML($oGlobal->GetUserData(TShopModuleArticlelistFilterSearch::PARAM_QUERY)); ?>"/>

            <?php
            if (!empty($oListfilter->fieldTitle)) {
                echo '<div class="smallHeadline">'.TGlobal::OutHTML($oListfilter->fieldTitle).'</div>';
            }
$sText = $oListfilter->GetTextField('introtext');
if (!empty($sText)) {
    echo '<div class="introText">'.$sText.'</div>';
}

$oFilterListItems = $oListfilter->GetFieldPkgShopListfilterItemList();
$oFilterListItems->GoToStart();
while ($oFilterItem = $oFilterListItems->Next()) {
    if ($oListfilter->isStaticFilter($oFilterItem)) {
        continue;
    }
    echo $oFilterItem->Render($oFilterItem->fieldView, $oFilterItem->fieldViewClassType);
}
?>
        </form>
    </div>
</div>