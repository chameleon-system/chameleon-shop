<h1>update - Build #1536149815</h1>
<h2>Date: 2018-09-05</h2>
<div class="changelog">
    - Cleanup old preorder records.
</div>
<?php

$query = "DELETE FROM `pkg_shop_article_preorder` WHERE `shop_article_id` = '' OR `cms_portal_id` = '' OR `preorder_date` < '2017-09-01'";
TCMSLogChange::RunQuery(__LINE__, $query);
