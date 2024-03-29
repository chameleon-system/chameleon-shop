<h1>Build #1629872885</h1>
<h2>Date: 2022-01-11</h2>
<div class="changelog">
    - ref #584: Change voucher message if yet unchanged - for all portals
</div>
<?php

$connection = TCMSLogChange::getDatabaseConnection();

$ids = $connection->fetchFirstColumn("SELECT `id` FROM `cms_message_manager_message` WHERE `message` =
'Für die Verwendung dieses Gutscheins ist ein Mindestbestellwert von [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] erforderlich. Bitte beachten Sie, dass buchpreisgebundene Artikel nur bei gesponserten Gutscheinen zum Warenwert gezählt werden.'
OR `message` =
'A minimum order value of [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] is required to use this voucher. Please note that articles with a fixed retail prices such as books are added to the value of goods only when using a sponsored voucher.'
");

if (\count($ids) > 0) {
    foreach ($ids as $id) {
        $data = TCMSLogChange::createMigrationQueryData('cms_message_manager_message', 'de')
            ->setFields([
                'message' => 'Für die Verwendung dieses Gutscheins ist ein Mindestbestellwert von [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] erforderlich. Bitte beachten Sie, dass buchpreisgebundene Artikel nur bei gekauften Gutscheinen zum Warenwert gezählt werden. Für Aktionsgutscheine können buchpreisgebundene Artikel leider nicht berücksichtigt werden.',
            ])
            ->setWhereEquals([
                'id' => $id,
            ]);
        TCMSLogChange::update(__LINE__, $data);

        $data = TCMSLogChange::createMigrationQueryData('cms_message_manager_message', 'en')
            ->setFields([
                'message' => 'For the use of this voucher a minimum order value of [{TdbShopVoucherSeries__fieldRestrictToValueFormated}] is required. Please note that book-price-bound articles are only counted towards the value of goods in the case of purchased vouchers. Unfortunately, book-price related articles cannot be included in promotion vouchers.',
            ])
            ->setWhereEquals([
                'id' => $id,
            ]);
        TCMSLogChange::update(__LINE__, $data);
    }
}
