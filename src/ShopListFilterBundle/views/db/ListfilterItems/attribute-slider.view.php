<?php
/*@var $oListItem TdbPkgShopListfilterItem */
$oListFilter = TdbPkgShopListfilter::GetActiveInstance();
$aData = $oListFilter->GetCurrentFilterAsArray();
$oLocal = &TCMSLocal::GetActive();

$sStyle = '';

$dStartAmount = 0;
$dEndAmount = 500;

if (count($aData) > 0) {
    $dStartAmount = $oListItem->GetActiveStartValue();
    $dEndAmount = $oListItem->GetActiveEndValue();
    if ((0 != $dStartAmount) || (500 != $dEndAmount)) {
        $sStyle = '_high';
    }
}
?>

<style type="text/css">
    #slider {
        height: 10px;
        position: relative;
    }

    #slider .ui-slider-range {
        margin: 2px 0;
        height: 4px;
        position: absolute;
    }

    #slider .selectorLeft,
    #slider .selectorRight {
        display: block;
        height: 10px;
        width: 10px;
        position: absolute;
    }

    #input {
        margin-top: 10px;
    }

    #input #amountStart,
    #input #amountEnd {
        border: 0;
        background-color: #FFFFCC;
        color: #000000;
        font-weight: bold;
        display: block;
        width: 60px;
    }

    #input #amountStart {
        float: left;
        text-align: left;
    }

    #input #amountEnd {
        float: right;
        text-align: right;
    }
</style>

<div class="TPkgShopListfilterItem <?=get_class($oListItem); ?>">
    <div class="valuelist">
        <div class="listFilterName"><?=TGlobal::OutHTML($oListItem->fieldName); ?></div>
        <div class="valueitems_high">
            <div id="slider"></div>
            <div class="cleardiv">&nbsp;</div>
            <div id="input">
                <input type="text" name="<?=$oListItem->GetURLInputName().'[dStartValue]'; ?>" id="amountStart" style=""
                       readonly="readonly"/>
                <input type="text" name="<?=$oListItem->GetURLInputName().'[dEndValue]'; ?>" id="amountEnd" style=""
                       readonly="readonly"/>
            </div>
            <div class="cleardiv">&nbsp;</div>
        </div>

        <script type="text/javascript">
            /* <![CDATA[ */
            $(document).ready(function () {
                $("#slider").slider({
                    range:true,
                    values:[<?=$dStartAmount.', '.$dEndAmount; ?>],
                    min: <?=$oListItem->GetMinValue(); ?>,
                    max: <?=$oListItem->GetMaxValue();?>,
                    slide:function (event, ui) {
                        $("#amountStart").val(ui.values[0]);
                        $("#amountEnd").val(ui.values[1]);
                    },
                    stop:function (event, ui) {
                        document.TdbPkgShopListfilter.submit();
                    }
                });
                $("#slider a:first").addClass("selectorLeft");
                $("#slider a:last").addClass("selectorRight");

                $("#amountStart").val($("#slider").slider("values", 0));
                $("#amountEnd").val($("#slider").slider("values", 1));
            });
            /* ]]> */
        </script>

    </div>
</div>