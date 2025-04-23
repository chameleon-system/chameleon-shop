# Payment

## Payment Groups

Payments are grouped into payment groups. Every group defines payment methods that share an API. That means, there is generally one entry per meta payment provider \[#\]_, one for payment methods that require no handling (such as manual debit) and one for all payment providers that support only one payment handler (although they often get their own group as well).

This has one important implication: you will need to make sure, that the IPN URL matches the portal of the order. Otherwise the system will not be able to configure the payment handler with the correct parameters.

**Portal Support**

Note that the parameters you define for a payment handler group (such as API keys) can be portal specific. The logic here is, that a parameter that has been defined for the active portal will have precedence over a parameter that has no portal id.

**Environment Support**

Every parameter can be defined either for all environments, for sandbox or production. The environment to be used can be configured in the payment handler group configuration in the Chameleon backend ("System" tab). Note that this is the only place where the environment can be configured - older ways to do this will not work anymore.

## Initializing a Payment Handler

Always use the factory service `chameleon_system_shop.payment.handler_factory` to get a payment handler. This factory implements the interface `ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerFactoryInterface` which defines a method `createPaymentHandler()` that creates a fully configured payment handler. The method requires the ID of the payment handler to initialize (ID field in the `shop_payment_handler` table) as well as a portal ID. You can optionally provide user-defined parameters.

## Configuration Details

The configuration process takes place in the service `chameleon_system_shop.payment.config_loader`. There are multiple sources for configuration:

- payment handler group configuration is loaded from the database
- an optional payment config provider is asked for its configuration data (see below for details). This configuration will be called "additional configuration" in this document.
- payment handler configuration is loaded from the database.

There are multiple "layers" of configuration which can override and extend each other, following this ruleset:

- A parameter either needs to conform to the current environment (e.g. a sandbox parameter is not used in live environment) or be in the pseudo-environment "common". This needs to be one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*

- A parameter either needs to conform to the current portalId (e.g. a parameter for portal 1 is not used in portal 2) or have no portal setting.

A parameter which does not fulfill these requirements will be discarded.

For all fitting parameters the following **source** priority rules apply.

- A parameter in the additional configuration always overrides payment handler group configuration.
- A parameter in the payment handler configuration always overrides payment handler group and additional configuration.

Within the same source, the following **portal** priority rule applies.

- A parameter for the current portal overrides a parameter without portal restrictions.

Within the same portal, the following **environment** priority rule applies.

- A parameter for the current environment overrides a parameter which is defined as "common".

This allows very complex configurations that might get confusing for administrators. So better try keeping things as simple as possible.

## Config Providers

For every payment handler group, an optional payment config provider service can be specified to configure additional database-independent payment parameters (e.g. Symfony container parameters or external config files).

To implement a config provider, create a class that implements `ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigProviderInterface`. This interface defines a single method `getAdditionalConfiguration()` which should return an array of `ChameleonSystem\ShopBundle\Payment\PaymentConfig\ShopPaymentConfigRawValue` objects. These raw values contain all that is needed to apply the ruleset stated above in the `Configuration Details` section. One of the attributes in a raw value is `source` - always set ShopPaymentConfigRawValue::SOURCE_ADDITIONAL in the provider.

To register the provider, create a Symfony service and tag it with `chameleon_system_shop.payment_config_provider`. The tag also needs an attribute `system_name`; the value for this attribute must be exactly the same as the value in the `Systemname` field in the payment handler group configuration in the Chameleon backend.

Example:

```xml
<service id="chameleon_system_amazon_pay.config_provider" class="ChameleonSystem\AmazonPayBundle\Configuration\ConfigProvider">
    <argument />
    <tag name="chameleon_system_shop.pay_config_provider" system_name="amazon" />
</service>
```

Please note:

- Only a single payment config provider can be set per payment handler group. The behaviour on multiple providers is undefined.

- The provider's `getAdditionalConfiguration()` method will be called at runtime, not at container compile time. If possible, prepare the configuration in the container compile process and simply hand it over when called by the system.

- The configuration might be cached for an arbitrary time. Do not expect that the config provider is called on every user request.

\[#\] meta payment providers are payment providers that support more than one payment method (e.g. PAYONE).