Chameleon System ShopProductExportBundle
========================================

Export is called as follows:
/URL-TO-EXPORT-PAGE/sModuleSpotName/SPOTNAME/view/EXPORT-ALIAS/key/IM-SHOP-HINTERLEGETER-EXPORT-KEY.TXT

where

* SPOTNAME is the spot name in which the export module was placed
* EXPORT-ALIAS the export service to use (see below)

New product exports are registered as follows:

.. configuration-block::
    .. code-block:: xml

        <service id="HANDLER-SERVICE-ID" class="class implementing ShopProductExportHandlerInterface" public="false" shared="false">
            <tag name="chameleon_system_shop_product_export.export_handler" alias="EXPORT-ALIAS" />
        </service>
