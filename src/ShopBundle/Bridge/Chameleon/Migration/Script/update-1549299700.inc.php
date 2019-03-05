<h1>Build #1549299700</h1>
<h2>Date: 2019-02-04</h2>
<div class="changelog">
    - Add CMS main menu categories.
</div>
<?php
TCMSLogChange::requireBundleUpdates('ChameleonSystemCoreBundle', 1549299689);

$categoryDef = <<<EOT
products             # Products & categories # Produkte & Kategorien # fas fa-cubes
productlists         # Product lists         # Produktlisten         # fas fa-boxes
discounts            # Discounts & vouchers  # Rabatte & Gutscheine  # fas fa-gift
orders               # Orders                # Bestellungen          # fas fa-shopping-basket
checkout             # Checkout              # Checkout              # fas fa-truck-monster
ratings              # Shop ratings          # Shop-Bewertungen      # fas fa-star
EOT;

$categoryLines = \explode(PHP_EOL, $categoryDef);
$position = 0;

foreach ($categoryLines as $categoryLine) {
    $id = TCMSLogChange::createUnusedRecordId('cms_menu_category');
    [$systemName, $nameEn, $nameDe, $iconFontCssClass] = \preg_split('/\s+#\s+/', $categoryLine);

    $data = TCMSLogChange::createMigrationQueryData('cms_menu_category', 'en')
        ->setFields([
            'id' => $id,
            'name' => $nameEn,
            'position' => $position,
            'system_name' => $systemName,
            'icon_font_css_class' => $iconFontCssClass,
        ])
    ;
    TCMSLogChange::insert(__LINE__, $data);

    $data = TCMSLogChange::createMigrationQueryData('cms_menu_category', 'de')
        ->setFields([
            'name' => $nameDe,
        ])
        ->setWhereEquals([
            'id' => $id,
        ])
    ;
    TCMSLogChange::update(__LINE__, $data);

    ++$position;
}
