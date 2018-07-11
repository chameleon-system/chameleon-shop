Chameleon System AmazonPaymentBundle
====================================

Description, capabilities and restrictions
------------------------------------------
* Amazon only supports EUR, GBP and USD. Any other currencies must be converted to one of these
* There are a couple of rules that need to be followed for the frontend integration. These are
    * Wherever there is a link to the normal checkout there must also be one to the amazon checkout (checkout, not cart overview)
    * The “Pay with Amazon” button has to appear as the top or right-most alternative checkout button.
    * Wherever an Amazon Payments button appears, it should come with an explanation what Amazon Payments is about, either as a descriptive text next to the button or as a mouse-over text on the button.
    * The Amazon Payments button design is the button design that has to be used. It is not allowed to use a different button graphic.
* Language handling: Currently, all Amazon Payments widgets and other buyer-facing content are presented in the language of the seller’s account. Implementing a switching mechanism is not supported as of now.
* There are two payment options: payment on order completion or payment on shipment. If payment on order completion is selected, then the complete order will be captured on order completion. In payment on shipment is active, then only downloads will be captured on order completion. All other products must be either captured via wawi status update or via the cms backend.
* If you enable payment on shipping and a payment is soft declined (Invalid Payment Method) then the seller will be informed via email. The seller will then need to contact the buyer to ask them to change their payment method via payments.amazon.com. Once that has been done the seller will need to manually run the payment collection again via the cms backend.


Installation
------------
* you need to include the mapper AmazonButtonWidgetMapper in
    * customer/private/extensions/library/classes/pkgShop/views/db/TShopOrderStep/TShopStepBasket/standardSnippetBridge.view.php
    * customer/private/extensions/objectviews/TCMSMessageManagerMessage/layover.view.php
    * customer/private/framework/modules/MTShopBasket/views/mini.view.php
```php
    $viewRenderer->AddMapper(new \ChameleonSystem\AmazonPaymentBundle\mappers\AmazonButtonWidgetMapper());
```
* add the following to your extension in your custom theme
    * customer/vendor/chameleon-system/themeshopstandard/snippets/pkgShop/shopBasket/shopBasketCheckoutBasketStep.html.twig
    * customer/vendor/chameleon-system/themeshopstandard/snippets/pkgShop/shopBasket/shopBasketAddedToBasket.html.twig  There is a sample in the resource folder of this package
    * customer/vendor/chameleon-system/themeshopstandard/snippets/pkgShop/shopBasket/shopBasketArticleLayover.html.twig  There is a sample in the resource folder of this package
```twig
    {% include 'pkgshoppaymentamazon/amazonbutton.html.twig' with amazonPayment %}
```
* link pkgshoppaymentamazon/Resources/views/pkgShop/views/pkgShop/views/db/TShopOrderStep/TShopStepLogin/userSnippetBridge_Amazon.inc.php in following directory and include it at the bottom of
/var/www/chameleon_shop_demo/customer/private/extensions/objectviews/pkgShop/views/db/TShopOrderStep/TShopStepLogin/userSnippetBridge.view.php
```php
    require_once(__DIR__."/userSnippetBridge_Amazon.inc.php");
```
* link pkgshoppaymentamazon/Resources/snippets/pkgShop/OrderSteps/loginOptionsRow.html.twig in your custom theme
* assets (CSS/JS/Less) are available in web/bundles/chameleonsystemamazonpayment/

* any page from which the amazon payment can be called (usually the basket) needs to include the action plugin AmazonShopActionPlugin (this can be set via the portal or via the pagedef) - the name of the plugin is expected to be amazonActionPluginSpotName
* you need to create a view for the amazon payment step in private/extensions/library/classes/pkgShop/views/db/TShopOrderStep/ChameleonSystemAmazonPaymentBundlepkgShopOrderStepsAmazonAddressStep. There is a sample in the resource folder of this package
* you need to create a view for the amazon payment step in private/extensions/library/classes/pkgShop/views/db/TShopOrderStep/ChameleonSystemAmazonPaymentBundlepkgShopOrderStepsAmazonShippingStep. There is a sample in the resource folder of this package. note that this step is a copy of the original (nothing needs to change), so you can also link in your original step
* you need to create a view for the amazon payment handler in private/extensions/library/classes/pkgShop/views/db/TShopPaymentHandler/ChameleonSystemAmazonPaymentBundlepkgShopAmazonPaymentHandler. There is a sample in the resource folder of this package.
* the order confirm step needs some changes - you can find a sample confirm page in the resource folder of this package
** the payment handler must render the read only widget of the amazon wallet instead of just displaying the payment name
** amazon does not provide the billing address until after the order is confirmed. so we can not display a billing address on the confirm step.
* you should add the amazon payment logo to your list of payment logos. The images you can find here  https://payments.amazon.de/business/material#payment-marks-graphics
* if you include an amazon button in your mini basket and the mini basket was displayed at the basket step set following variable in your twig template before the amazon button include. {% set amazonIgnoreMessagesInSnippet = true %}
* bootstrap-tooltip.js must be loaded on any page where a payment button is placed
* after running the package update the configuration values for merchantId, accessKey and secretKey need to be set (in the Amazon payment handler group configuration in the backend)

Additional information
----------------------
Some of the recommendations from amazon where skipped in the initial version - and can be implemented as custom changes if a customer would like them. These are
* #26417: Digital good do not require a shipping address. Baskets holding only digital goods could skip that step

*Important*
The URL that is used as IPN Response is used to identify the portal to which the request belongs. That means, that the order must belong to the portal associated with
the IPN URL
