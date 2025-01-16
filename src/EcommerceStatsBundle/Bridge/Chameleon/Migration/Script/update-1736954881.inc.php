<h1>Build #1736954881</h1>
<h2>Date: 2025-01-15</h2>
<div class="changelog">
    - ref #65487: add right "ecommerce_stats_show_module" to admin role
</div>
<?php

// add right "ecommerce_stats_show_module" to admin role
$data = TCMSLogChange::createMigrationQueryData('cms_role_cms_right_mlt', 'de')
    ->setFields([
        'source_id' => '1',
        'target_id' => '7dca3b88-ab6f-7a22-ef56-7b628cd98145',
        'entry_sort' => '16',
    ])
;
TCMSLogChange::insert(__LINE__, $data);

TCMSLogChange::addInfoMessage('There is a new right "ecommerce_stats_show_module" that needs to be set to all user roles, that should have access to the e-commerce stats and the dashboard widgets');