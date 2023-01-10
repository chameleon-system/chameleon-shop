<?php
//@todo we need to fix the positioning of the tooltip
/** @var $oSpot TdbPkgImageHotspotItemSpot */
/** @var $oItem TdbShopArticle */
$oItem = $oSpot->GetSpotObject();
$iMarkerWidth = 21;
$iMarkerHeight = 21;
?>
<script type="text/javascript">
    $(document).ready(function () {
        var sTop = $("#spot<?=$oSpot->id; ?>").offset().top;
        var sLeft = $("#spot<?=$oSpot->id; ?>").offset().left;

        $('#objectDisplayBlock<?=$oSpot->id; ?>').css({'top':sTop + 'px', 'left':sLeft + 'px'});
    });
</script>

<area shape="poly" coords="<?=$oSpot->fieldPolygonArea; ?>" id="spot<?=$oSpot->id; ?>"
      onmouseover="$('#objectDisplayBlock<?=$oSpot->id; ?>').css({'display': 'block'});"
      onmouseout="$('#objectDisplayBlock<?=$oSpot->id; ?>').css({'display': 'none'});" href="http://www.esono.de/"
      alt="esono" title="esono">

<div class="objectDisplayBlock" id="objectDisplayBlock<?=$oSpot->id; ?>">
    <img src="<?=URL_USER_CMS_PUBLIC; ?>/blackbox/pkgImageHotspot/nose.png" alt="" class="blockNose"/>

    <div class="objectDisplayBlockContent">
        <?php if (method_exists($oItem, 'Render')) {
    echo $oItem->Render('hotspot', 'Customer');
} ?>
    </div>
</div>