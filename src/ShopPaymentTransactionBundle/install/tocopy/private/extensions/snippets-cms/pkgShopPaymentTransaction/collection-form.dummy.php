<?php

$oDummy = new TPkgViewRendererSnippetDummyData();

$aData = array(
    'items' => array(
        array(
            'id' => '1',
            'articlenumber' => '1',
            'name' => 'test',
            'totalQuantityOrdered' => 10,
            'totalQuantityPaid' => 1,
            'totalQuantityCanceled' => 4,
            'totalQuantityForTransaction' => 5,
            'price' => 10.99,
            'itemTotal' => 54.95,
        ),
        array(
            'id' => '2',
            'articlenumber' => '2',
            'name' => 'test 2',
            'totalQuantityOrdered' => 3,
            'totalQuantityPaid' => 0,
            'totalQuantityCanceled' => 1,
            'totalQuantityForTransaction' => 2,
            'price' => 5,
            'itemTotal' => 10,
        ),
        array(
            'id' => '3',
            'articlenumber' => '3',
            'name' => 'test 3',
            'totalQuantityOrdered' => 1,
            'totalQuantityPaid' => 1,
            'totalQuantityCanceled' => 0,
            'totalQuantityForTransaction' => 0,
            'price' => 5,
            'itemTotal' => 10,
        ),
        array(
            'id' => '4',
            'articlenumber' => '4',
            'name' => 'test 4',
            'totalQuantityOrdered' => 2,
            'totalQuantityPaid' => 1,
            'totalQuantityCanceled' => 0,
            'totalQuantityForTransaction' => 1,
            'price' => 3,
            'itemTotal' => 3,
        ),
    ),
    'valueProducts' => 0,
    'valueDiscount' => -5,
    'valueDiscountVouchers' => -10.54,
    'valueShipping' => 2.5,
    'valuePayment' => 5,
    'valueOther' => 2,
    'valueVoucher' => -20,
    'valueGrandTotal' => '',
    'bHasSponsoredVouchers' => true,
);

$dPriceTotal = 0;
foreach ($aData['items'] as $item) {
    $dPriceTotal += $item['itemTotal'];
}
$aData['valueProducts'] = $dPriceTotal;
$aData['valueGrandTotal'] = $aData['valueProducts'] + $aData['valueDiscount'] + $aData['valueDiscountVouchers'] + $aData['valueShipping'] + $aData['valuePayment'] + $aData['valueOther'] + $aData['valueVoucher'];

$oDummy->addDummyDataArray($aData);

return $oDummy;
