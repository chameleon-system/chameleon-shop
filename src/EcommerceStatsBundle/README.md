# eCommerce Statistics Bundle

This CMS bundle serves chameleon's shop statistic stuff.

After installation, ensure to create these sym links:

    $ composer run-script post-install-cmd
    (Installs sym links)
    $ ls -l web/bundles/chameleonsystemecommercestats
    (Output)  web/bundles/chameleonsystemecommercestats -> ../../../vendor/chameleon-system/chameleon-shop/src/EcommerceStatsBundle/Resources/public/


And

    $ cd src/extensions/snippets-cms
    $ ln -s ../../../vendor/chameleon-system/chameleon-shop/src/EcommerceStatsBundle/Resources/views/snippets-cms/ecommerceStats/


## Data Structure

This bundle uses a deeply nested stats data structure in order to allow 'drilling down' into
statistics if needed. At the center of this drillin
