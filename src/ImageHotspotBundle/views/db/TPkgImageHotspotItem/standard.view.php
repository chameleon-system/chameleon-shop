<?php
/** @var $oItem TdbPkgImageHotspotItem */
$oImage = $oItem->GetImage(0, 'cms_media_id');
$oSpots = $oItem->GetFieldPkgImageHotspotItemSpotList();
$oNextItem = $oItem->GetNextItem();
$oMakers = $oItem->GetFieldPkgImageHotspotItemMarkerList();
// id="key<?=md5($oItem->fieldPkgImageHotspotId)"
?>
<div class="TPkgImageHotspotItem">
    <div class="standard">
        <img src="<?=TGlobal::OutHTML($oImage->GetFullURL()); ?>" alt="<?=TGlobal::OutHTML($oItem->fieldName); ?>"
             width="<?=TGlobal::OutHTML($oImage->aData['width']); ?>"
             height="<?=TGlobal::OutHTML($oImage->aData['height']); ?>" border="0" usemap="#map<?=$oItem->id; ?>"/>
        <?php
        $aArea = array();
        while ($oSpot = $oSpots->Next()) {
            if ($oSpot->fieldShowSpot) {
                echo $oSpot->Render('standard', 'Customer');
            }
        }
        $oSpots->GoToStart();
        ?>
        <map class="image-map" name="map<?=$oItem->id; ?>">
            <?php
            while ($oSpot = $oSpots->Next()) {
                if (!empty($oSpot->fieldPolygonArea)) {
                    echo $oSpot->Render('image-map', 'Customer');
                }
            }
            ?>
        </map>
        <?php
        while ($oMaker = $oMakers->Next()) {
            echo $oMaker->Render('standard', 'Customer');
        }
        if ($oNextItem) {
            $dBottom = round(($oImage->aData['height'] - 32) / 2);
            echo "<a style=\"bottom:{$dBottom}px\" class=\"nextItemLink\" rel=\"{$oNextItem->GetAjaxLink()}\" href=\"{$oNextItem->GetLink()}\" title=\"".TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_image_hotspot.action.page_next')).': '.TGlobal::OutHTML($oNextItem->fieldName).'"><img src="/static/images/icons/arrow-big-right.png" alt="'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_image_hotspot.action.page_next')).'" /></a>';
        }
        ?>
    </div>
</div>