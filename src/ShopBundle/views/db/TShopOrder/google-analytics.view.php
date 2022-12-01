<?php
/** @var $oOrder TdbShopOrder */
/** @var $aCallTimeVars array */
$oLocal = TCMSLocal::GetActive();
$portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
$activePortal = $portalDomainService->getActivePortal();

if (!empty($activePortal->fieldGoogleAnalyticNumber)) {
    ?>
<script type="text/javascript">
    $(document).ready(function () {
        <?php
        $oLastOrder = $oOrder;
    //start of trans data?>
        _gaq.push(['_addTrans',
            <?php
            echo "'".$oLastOrder->fieldOrdernumber."',";
    echo "'',";
    echo "'".$oLastOrder->fieldValueTotalFormated."',";
    echo "'".$oLastOrder->fieldValueVatTotalFormated."',";
    echo "'".$oLastOrder->fieldShopShippingGroupPrice."',";
    echo "'".$oLastOrder->fieldAdrBillingCity."',";
    echo '"",';
    $oCountry = TdbDataCountry::GetNewInstance();
    $oCountry->Load($oLastOrder->fieldAdrBillingCountryId);
    echo "'".$oCountry->GetDisplayValue()."'"; ?>
        ]);
        <?php
        //start of basket data
        $oOrderItemsList = $oLastOrder->GetFieldShopOrderItemList();
    $oOrderItemsList->GoToStart();

    while ($oOrderItem = $oOrderItemsList->Next()) {
        ?>
            _gaq.push(['_addItem',
                <?php
                echo "'".$oLastOrder->fieldOrdernumber."',";
        echo "'".$oOrderItem->fieldArticlenumber."',";
        echo "'".$oOrderItem->GetName()."',";
        $oArticle = $oOrderItem->GetFieldShopArticle();
        $oPrimaryCategory = $oArticle->GetPrimaryCategory();
        if (!is_null($oPrimaryCategory)) {
            echo "'".$oPrimaryCategory->GetName()."',";
        } else {
            echo '"",';
        }
        echo "'".$oOrderItem->fieldOrderPriceFormated."',";
        echo "'".$oOrderItem->fieldOrderAmountFormated."'"; ?>
            ]);
            <?php
    } ?>
        _gaq.push(['_trackTrans']);
    });
</script>
<?php
}
?>