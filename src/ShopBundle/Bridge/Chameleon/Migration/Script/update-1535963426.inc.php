<h1>Build #1535963426</h1>
<h2>Date: 2018-09-03</h2>
<div class="changelog">
    - #50: show manufacturer todo for table display
</div>
<?php

TCMSLogChange::addInfoMessage("Check table 'shop_article' if it includes the manufacturer name in the list display (and thus the list query). 
    If not it might be advisable to add it in order to make the name searchable in the backend table.
    1. Change list query: add '`shop_manufacturer`.`name` as manufacturer' and the respective JOIN
    2. Change the existing display field 'Manufacturer id' to show the name instead", TCMSLogChange::INFO_MESSAGE_LEVEL_TODO);
