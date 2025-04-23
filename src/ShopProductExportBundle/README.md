Chameleon System ShopProductExportBundle
========================================

Calling the module \ChameleonSystem\ShopProductExportBundle\Modules\ShopProductExportModule with ?reset=1 will 
write the result into a cache file. Calling the module without the reset will always return the contents of that file.

IMPORTANT: This means you need to configure the production server in such a way, that the export is called once
every night with the reset=1 parameter!

Export is called as follows:

`/URL-TO-EXPORT-PAGE/sModuleSpotName/SPOTNAME/view/EXPORT-ALIAS/key/IM-SHOP-HINTERLEGETER-EXPORT-KEY.TXT`

where

- SPOTNAME is the spot name in which the export module was placed
- EXPORT-ALIAS the export service to use (see below)

New product exports are registered as follows:

```xml
<service id="HANDLER-SERVICE-ID" class="class implementing ShopProductExportHandlerInterface" public="false" shared="false">
    <tag name="chameleon_system_shop_product_export.export_handler" alias="EXPORT-ALIAS" />
</service>
```