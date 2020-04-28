<h1>Build #1588073204</h1>
<h2>Date: 2020-04-28</h2>
<div class="changelog">
    - Add message about new placeholder for basket buttons
</div>
<?php

TCMSLogChange::addInfoMessage('If you are using a custom theme you need to add a new placeholder to the twig file(s) rendering an add-to-basket button: [{BASKETHIDDENFIELDS}]. See \ChameleonSystem\ShopBundle\Basket\BasketVariableReplacer for more details.');
