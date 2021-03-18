<h1>Build #1601029512</h1>
<h2>Date: 2020-09-25</h2>
<div class="changelog">
    - ref #50493: New backend module for statistics
</div>
<?php

function createOrUpdate(\ChameleonSystem\DatabaseMigration\Query\MigrationQueryData $data) {
    $query = TCMSLogChange::getDatabaseConnection()->createQueryBuilder();
    $conditions = [];
    foreach ($data->getWhereEquals() as $column => $value) {
        $conditions[] = $query->expr()->eq($column, $query->expr()->literal($value));
    }
    $count = (int) $query
        ->select('COUNT(*)')
        ->from($data->getTableName())
        ->where($query->expr()->andX(...$conditions))
        ->execute()
        ->fetchColumn(0);

    if (0 === $count) {
        TCMSLogChange::insert(__LINE__, $data);
    } else {
        TCMSLogChange::update(__LINE__, $data);
    }
}

createOrUpdate(
    TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'de')
        ->setFields([
            'classname' => 'chameleon_system_ecommerce_stats.backend_module.ecommerce_stats',
            'name' => 'Shop Umsatzstatistiken Backendmodul',
            'description' => '',
            'icon_font_css_class' => 'fas fa-chart-pie',
            'view_mapper_config' => 'standard=ecommerceStats/module/standard.html.twig',
            'mapper_chain' => '',
            'view_mapping' => '',
            'revision_management_active' => '0',
            'is_copy_allowed' => '0',
            'show_in_template_engine' => '0',
            'is_restricted' => '1',
            'id' => '45e3813d-0aee-2c22-f18a-5c571eb30371',
        ])->setWhereEquals([ 'id' => '45e3813d-0aee-2c22-f18a-5c571eb30371' ])
);

createOrUpdate(
    TCMSLogChange::createMigrationQueryData('cms_tpl_module_cms_usergroup_mlt', 'de')
        ->setFields([
            'source_id' => '45e3813d-0aee-2c22-f18a-5c571eb30371',
            'target_id' => '11',
            'entry_sort' => '0',
        ])->setWhereEquals([
            'source_id' => '45e3813d-0aee-2c22-f18a-5c571eb30371',
            'target_id' => '11',
            'entry_sort' => '0',
        ])
);

createOrUpdate(
    TCMSLogChange::createMigrationQueryData('cms_menu_custom_item', 'de')
        ->setFields([
            'name' => 'Umsatzstatistiken',
            'url' => '/cms?pagedef=ecommerceStats&_pagedefType=@ChameleonSystemEcommerceStatsBundle',
            'id' => 'd5ab4158-98db-8944-75c5-4af914a4a0c5',
        ])->setWhereEquals([ 'id' => 'd5ab4158-98db-8944-75c5-4af914a4a0c5' ])
);

createOrUpdate(
    TCMSLogChange::createMigrationQueryData('cms_menu_item', 'de')
        ->setFields([
            'name' => 'Umsätze',
            'target' => 'd5ab4158-98db-8944-75c5-4af914a4a0c5',
            'icon_font_css_class' => 'fas fa-chart-pie',
            'position' => '22',
            'cms_menu_category_id' => 'ce1c9b6f-fcb7-1934-fed8-8bc825ad37eb',
            'target_table_name' => 'cms_menu_custom_item',
        ])
        ->setWhereEquals([ 'id' => '101fafdf-7a01-aac1-14ee-8275dc18667a' ])
);
