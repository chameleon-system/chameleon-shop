<?php
// @todo we need to fix the positioning of the tooltip
/** @var $oSpot TdbPkgImageHotspotItemSpot */
/** @var $oItem TdbShopArticle */
$oItem = $oSpot->GetSpotObject();
$iMarkerWidth = 21;
$iMarkerHeight = 21;
?>
<script type="text/javascript">
    $(document).ready(function () {
        var sTop = $("#spot<?php echo $oSpot->id; ?>").offset().top;
        var sLeft = $("#spot<?php echo $oSpot->id; ?>").offset().left;

        $('#objectDisplayBlock<?php echo $oSpot->id; ?>').css({'top':sTop + 'px', 'left':sLeft + 'px'});
    });
</script>

<area shape="poly" coords="<?php echo $oSpot->fieldPolygonArea; ?>" id="spot<?php echo $oSpot->id; ?>"
      onmouseover="$('#objectDisplayBlock<?php echo $oSpot->id; ?>').css({'display': 'block'});"
      onmouseout="$('#objectDisplayBlock<?php echo $oSpot->id; ?>').css({'display': 'none'});" href="http://www.esono.de/"
      alt="esono" title="esono">

<div class="objectDisplayBlock" id="objectDisplayBlock<?php echo $oSpot->id; ?>">
    <img src="<?php echo URL_USER_CMS_PUBLIC; ?>/blackbox/pkgImageHotspot/nose.png" alt="" class="blockNose"/>

    <div class="objectDisplayBlockContent">
        <?php if (method_exists($oItem, 'Render')) {
            echo $oItem->Render('hotspot', 'Customer');
        } ?>
    </div>
</div>