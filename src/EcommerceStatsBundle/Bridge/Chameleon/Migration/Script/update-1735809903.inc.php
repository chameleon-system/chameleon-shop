<h1>Build #1735809903</h1>
<h2>Date: 2025-01-02</h2>
<div class="changelog">
    - #65182: change order of statistic fields, place name first
</div>
<?php

TCMSLogChange::SetFieldPosition(TCMSLogChange::GetTableId('pkg_shop_statistic_group'),'portal_restriction_field','name');
TCMSLogChange::SetFieldPosition(TCMSLogChange::GetTableId('pkg_shop_statistic_group'),'query','name');
TCMSLogChange::SetFieldPosition(TCMSLogChange::GetTableId('pkg_shop_statistic_group'),'groups','name');
TCMSLogChange::SetFieldPosition(TCMSLogChange::GetTableId('pkg_shop_statistic_group'),'date_restriction_field','name');