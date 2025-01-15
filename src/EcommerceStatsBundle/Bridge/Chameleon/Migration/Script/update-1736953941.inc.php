<h1>Build #1736953941</h1>
<h2>Date: 2025-01-15</h2>
<div class="changelog">
    - ref #65487: add right "ecommerce_stats_show_module" for the ecommerce stats menu item
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_menu_custom_item_cms_right_mlt', 'de')
  ->setFields([
      'source_id' => '9c06702f-7ffb-426d-afe9-5ffd5a9cd122',
      'target_id' => '7dca3b88-ab6f-7a22-ef56-7b628cd98145',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);
