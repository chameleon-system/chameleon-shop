Chameleon System ShopProductExportBundle
========================================

Calling the module \ChameleonSystem\ShopProductExportBundle\Modules\ShopProductExportModule with ?reset=1 will 
write the result into a cache file. Calling the module without the reset will always return the contents of that file.

IMPORTANT: This means you need to configure the production server in such a way, that the export is called once
every night with the reset=1 parameter!