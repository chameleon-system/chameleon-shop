# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [7.1.6] - 2024-05-03

### Added

* introduces VariantTypeDataModelFactory and VariantTypeValueDataModelFactory which will add variant datamodels to the product detail page views
* The UpdateProductStockListener allows stock based manipulation like enabling/disabling products. The functionality moved from the TableEditor, TShopArticle, TShopOrder and TShopOrderItem to this event

### Interface changes

* ProductInventoryService::addStock, setStock and updateVariantParentStock got the return type bool. The ProductInventoryServiceCacheProxy was changed accordingly