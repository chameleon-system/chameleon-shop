<h1>Build #1604649540</h1>
<h2>Date: 2020-11-06</h2>
<div class="changelog">
    - Add Post render variable for basket URL to prevent accidental caching of it
</div>
<?php

TCMSLogChange::addInfoMessage('If you are using a custom theme you need replace the {{sBasketUrl}} template variable with the [{basketUrl}] post render variable in the shopBasketMiniBasket and shopBasketMiniBasketMobile templates', TCMSLogChange::INFO_MESSAGE_LEVEL_INFO);
