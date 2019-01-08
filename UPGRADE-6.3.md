UPGRADE FROM 6.2 TO 6.3
=======================

# Changed Interfaces and Method Signatures

This section contains information on interface and method signature changes which affect backwards compatibility (BC).
Note that ONLY BC breaking changes are listed, according to our backwards compatibility policy.


## \ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject

- Changed method `__construct()` now expects `Psr\Log\LoggerInterface` instead of `\IPkgCmsCoreLog`.

## \ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig

- Changed method `setLogger()` now expects `Psr\Log\LoggerInterface` instead of `\IPkgCmsCoreLog`.

## \ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject

- Changed method `__construct()` is now using `LoggerInterface` as parameter type.

# Deprecated Code Entities

It is recommended that all references to the classes, interfaces, properties, constants, methods and services in the
following list are removed from the project, as they will be removed in Chameleon 7.0. The deprecation notices in the
code will tell if there are replacements for the deprecated entities or if the functionality is to be entirely removed.

To search for deprecated code usage, [SensioLabs deprecation detector](https://github.com/sensiolabs-de/deprecation-detector)
is recommended (although this tool may not find database-related deprecations).

## Services

- chameleon_system_shop.log.order
- chameleon_system_shop.log.order_channel

## Container Parameters

None.

## Constants

- \TShopPaymentHandlerPayPal_PayViaLink::LOG_FILE

## Classes and Interfaces

None.

## Properties

None.

## Methods

- \TPkgShopOrderStatusManagerEndPoint::getLogger()
- \TPkgShopOrderStatusManagerEndPoint::setLogger()

## JavaScript Files and Functions

None.

## Translations

None.

## Database Tables

None.

## Database Fields

None.
