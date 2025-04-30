<?php
use ChameleonSystem\CoreBundle\ServiceLocator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @var TranslatorInterface $translator
 */
$translator = ServiceLocator::get('translator');
?>

<div class="card">
    <div class="card-header">
        <?php echo TGlobal::OutHTML($translator->trans('chameleon_system_shop.product_search_index.headline')); ?>
    </div>
    <div class="card-body">
        <form name="indexer" method="post" action="<?php echo PATH_CMS_CONTROLLER; ?>" accept-charset="UTF-8">
            <input type="hidden" name="pagedef" value="<?php echo TGlobal::OutHTML($data['pagedef']); ?>"/>
            <input type="hidden" name="_pagedefType" value="<?php echo $data['_pagedefType']; ?>"/>
            <input type="hidden" name="module_fnc[<?php echo TGlobal::OutHTML($data['sModuleSpotName']); ?>]"
                   value="TickerIndexGeneration"/>

            <?php if (!$data['bIndexIsRunning'] || $data['bIndexCompleted']) {
                ?>
                <input class="btn btn-primary" type="submit" value="<?php echo TGlobal::OutHTML($translator->trans('chameleon_system_shop.product_search_index.generate')); ?>"/>
                <?php
            } ?>
        </form>
        <?php
                    $oLocal = TCMSLocal::GetActive();
?>
        <div class="mt-4"><?php echo TGlobal::OutHTML($translator->trans('chameleon_system_shop.product_search_index.completed')); ?>: <?php echo $oLocal->FormatNumber($data['bPercentDone'], 2); ?>%</div>

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
</div>
