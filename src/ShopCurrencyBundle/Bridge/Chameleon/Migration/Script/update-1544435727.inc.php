<h1>Build #1544435727</h1>
<h2>Date: 2018-12-10</h2>
<div class="changelog">
    -#232 remove not needed currency extensions + add basket currency extension
</div>
<?php

TCMSLogChange::AddVirtualNonDbExtension(__LINE__, 'TShopBasket', 'ChameleonSystem\ShopCurrencyBundle\Bridge\Chameleon\Objects\CurrencyBasket');

TCMSLogChange::deleteExtensionAutoParentFromTable('shop_discount', 'TPkgShopCurrency_ShopDiscount');
TCMSLogChange::deleteExtensionAutoParentFromTable('shop_payment_method', 'TPkgShopCurrency_ShopPaymentMethod');
TCMSLogChange::deleteExtensionAutoParentFromTable('shop_shipping_type', 'TPkgShopCurrency_ShopShippingType');
