Chameleon System ImageHotspotBundle
===================================
  
The ImageHotspotBundle provides functionality to display interactive images
with navigable hotspots and markers. It allows you to define multiple
images in a hotspot group, navigate between them, and overlay clickable
hotspots (image maps) and markers with hover states.
  
Features
--------
- Define image hotspot groups (`PkgImageHotspot`) with multiple items.
- Each item (`PkgImageHotspotItem`) has a background image, position, and alt text.
- Overlay clickable regions (spots) on the image (polygon areas) that can open
  linked records or external URLs, with optional content layovers.
- Display marker icons (`PkgImageHotspotItemMarker`) with image and hover image,
  positioned relative to the background.
- Navigate to next/previous item, and configure auto-slide timing.
- Extensible via custom renderers and mappers.
  
Installation
------------
Install the bundle in your Symfony application by registering it in `bundles.php`:
  
```php
<?php
return [
    // ...
    ChameleonSystem\ImageHotspotBundle\ChameleonSystemImageHotspotBundle::class => ['all' => true],
];
```
  
Configuration
-------------
- Create a `pkg_image_hotspot` record in the CMS.
- Add one or more `pkg_image_hotspot_item` records and upload images.
- For each item, define polygon spots (`PkgImageHotspotItemSpot`) or markers
  (`PkgImageHotspotItemMarker`) via the admin interface.
- Configure auto-slide time (in seconds) and item order in the hotspot record.
  
Usage
-----
Render a hotspot group using the view renderer:
  
```php
use ViewRenderer;
use TdbPkgImageHotspot;

// Load the hotspot and first item ID
$hotspot = TdbPkgImageHotspot::GetNewInstance($hotspotId);
$firstItemId = $hotspot->GetFieldPkgImageHotspotItemList()->Current()->id;

$viewRenderer = new ViewRenderer();
$viewRenderer->addMapperFromIdentifier('TPkgImageHotspotMapper');
$viewRenderer->AddSourceObject('oPkgImageHotspot', $hotspot);
$viewRenderer->AddSourceObject('sActiveItemId', $firstItemId);
$viewRenderer->AddSourceObject('sMapperConfig', 'standard');
$viewRenderer->AddSourceObject('aObjectRenderConfig', [
    \ChameleonSystem\ImageHotspotBundle\Entity\PkgImageHotspotItemMarker::class => [
        'mapper' => ['YourCustomMarkerMapper'],
        'snippet' => 'standard',
    ],
]);
echo $viewRenderer->Render('standard');
```
  
Customize
---------
- Override `TPkgImageHotspotMapper` to adjust data mapping.
- Provide custom snippets in `views/db/...` to alter HTML structure or styling.
- Extend entities (`PkgImageHotspotItem`, `PkgImageHotspotItemSpot`,
  `PkgImageHotspotItemMarker`) to add custom fields or behavior.
  
Cache
-----
The mapper attaches cache triggers for hotspot records, media, and linked
records to ensure correct invalidation on content changes.
  
File Structure
--------------
- `Entity/` – Doctrine entities for hotspots, items, spots, markers.
- `mappers/` – PHP mappers to transform data for rendering.
- `views/db/` – PHP view snippets for items, spots, markers.
- `Resources/config/doctrine/` – ORM mapping files.
  
License
-------
See the project `LICENSE` file for license details.
