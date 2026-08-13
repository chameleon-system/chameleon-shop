<h1>Build #1785313357</h1>
<h2>Date: 2026-07-29</h2>
<div class="changelog">
    - ref #70900: make customer_service_telephone_info of shop translatable
</div>
<?php

//this isn't a field from chameleon_shop and I didn't find it in any core code.
//This update actually belongs in a client project

if (TCMSLogChange::FieldExists('shop', 'customer_service_telephone_info')) {
    TCMSLogChange::makeFieldMultilingual('shop', 'customer_service_telephone_info');
}
