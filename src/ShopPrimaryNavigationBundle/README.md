Chameleon System ShopPrimaryNavigationBundle
============================================
  
Overview
--------
The ShopPrimaryNavigationBundle provides a reusable primary navigation component for the Chameleon System shop. It allows you to define custom navigation items (pages or categories) in the CMS and renders them as a structured tree with support for child menus, CSS classes, and cacheable output.
  
Features
--------
- Define navigation items via the `pkg_shop_primary_navi` table in the CMS backend.
- Support for linking to CMS pages or shop categories, with optional root-category tree expansion.
- CSS class customization for each navigation item.
- Automatic caching with invalidation on portal, category, page or navigation changes.
- Extendable mapping logic: customize the node objects or rendering behavior.
  
Installation
------------
This bundle is included by default. To register manually, add to your AppKernel or bundles.php:
```php
new ChameleonSystem\\ShopPrimaryNavigationBundle\\ChameleonSystemShopPrimaryNavigationBundle(),
```
  
Configuration
-------------
1. In the CMS admin interface, navigate to **Shop > Primary Navigation**.
2. Add new navigation entries, choosing the **Target Object Type**:
   - **CMS Page**: link to an existing CMS page (`TdbCmsTree`).
   - **Shop Category**: link directly to a shop category (`TdbShopCategory`).
3. For page targets, you can enable **Show Root Category Tree** to include a submenu of all top-level categories.
4. Set a **Name**, **CSS Class**, and **Priority** (sort order) for each item.
5. Assign the navigation module to your page layout (see Usage).
  
Usage
-----
In your Twig (or PHP) template, render the primary navigation module. For example, in Twig:
  
```twig
{# Render the navigation from the default site spot #}
{{ chameleon_system_render_module('PkgShopPrimaryNavigation') }}
```
  
The mapper will expose an `aTree` variable containing an array of navigation node objects. Example manual rendering:
  
```twig
{% set nodes = aTree %}
<ul class="primary-navigation">
    {% for node in nodes %}
        <li class="{{ node.sCssClass }}">
            <a href="{{ node.GetLinkUrl() }}">{{ node.sTitle }}</a>
            {% if node.getChildren()|length > 0 %}
                <ul class="sub-navigation">
                    {% for child in node.getChildren() %}
                        <li class="{{ child.sCssClass }}">
                            <a href="{{ child.GetLinkUrl() }}">{{ child.sTitle }}</a>
                        </li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>
    {% endfor %}
</ul>
```
  
API Reference
-------------
```php
// Retrieve items for a given portal
TPkgShopPrimaryNaviList::GetListForCmsPortalId(string $portalId, int $iLanguageId = null): TPkgShopPrimaryNaviList

// Get the navigation node object (extends AbstractPkgCmsNavigationNode)
TPkgShopPrimaryNavi::getPkgCmsNavigationNodeObject(): AbstractPkgCmsNavigationNode|null

// Module mapper for auto-rendering in templates
MTPkgShopPrimaryNavigation

// Standard tree mapper class
TPkgShopPrimaryNavigationMapper_StandardNavi
```
  
License
-------
This bundle is released under the MIT License. See the LICENSE file in the project root for details.
