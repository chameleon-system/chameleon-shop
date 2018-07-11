<div class="CMSInterfacePopup">
    <form name="indexer" method="post" action="<?=PATH_CMS_CONTROLLER; ?>" accept-charset="UTF-8">
        <input type="hidden" name="pagedef" value="<?=TGlobal::OutHTML($data['pagedef']); ?>"/>
        <input type="hidden" name="_pagedefType" value="<?= $data['_pagedefType']; ?>"/>
        <input type="hidden" name="module_fnc[<?=TGlobal::OutHTML($data['sModuleSpotName']); ?>]"
               value="TickerIndexGeneration"/>


        <?php if (!$data['bIndexIsRunning'] || $data['bIndexCompleted']) {
    ?>
        <input type="submit" value="generate"/>
        <?php
} ?>
    </form>
    <?php
    $oLocal = &TCMSLocal::GetActive();
    ?>
    Completed: <?=$oLocal->FormatNumber($data['bPercentDone'], 2); ?>%<br/>

    <?php if (!$data['bIndexCompleted'] && $data['bIndexIsRunning']) {
        ?>
    <script>
        function SubmitCMSIndex() {
            document.indexer.submit();
        }
        setTimeout("SubmitCMSIndex()", 1000);
    </script>
    <?php
    } ?>
</div>