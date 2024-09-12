<?php

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\Breadcrumb;

use ChameleonSystem\BreadcrumbBundle\Bridge\Chameleon\Breadcrumb\AbstractBreadcrumbGenerator;
use ChameleonSystem\BreadcrumbBundle\Interfaces\BreadcrumbGeneratorUtilsInterface;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbDataModel;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbItemDataModel;
use esono\pkgCmsCache\Cache;
use Symfony\Component\HttpFoundation\RequestStack;

final class BreadcrumbShopCategoryListGenerator extends AbstractBreadcrumbGenerator
{
    private const triggerTable = 'shop_category';

    private ?\TdbShopCategory $activeShopCategory = null;

    public function __construct(
        private readonly BreadcrumbGeneratorUtilsInterface $breadcrumbGeneratorUtils,
        private readonly RequestStack $requestStack,
        private readonly Cache $cache
    ) {
    }

    public function isActive(): bool
    {
        if ($this->requestStack->getCurrentRequest()->attributes->has('activeShopCategory')) {
            return true;
        }

        return false;
    }

    public function generate(): BreadcrumbDataModel
    {
        $cacheResult = $this->getFromCache();
        if (null !== $cacheResult) {
            return $cacheResult;
        }

        $breadcrumb = new BreadcrumbDataModel();

        $activeShopCategory = $this->getActiveCategory();
        if (null === $activeShopCategory) {
            return $breadcrumb;
        }

        $breadcrumbItem = new BreadcrumbItemDataModel(
            $activeShopCategory->GetName(), $activeShopCategory->getLink(true)
        );
        $breadcrumb->add($breadcrumbItem);

        $rootCategory = $activeShopCategory->getRootCategory();
        if (null === $rootCategory) {
            $this->setCache($breadcrumb);

            return $breadcrumb;
        }
        $breadcrumbItem = new BreadcrumbItemDataModel($rootCategory->fieldName, $rootCategory->getUrlPath(true));
        $breadcrumb->add($breadcrumbItem);

        $this->breadcrumbGeneratorUtils->attachHomePageBreadcrumbItem($breadcrumb);
        $this->setCache($breadcrumb);

        return $breadcrumb;
    }

    protected function setCache(BreadcrumbDataModel $breadcrumb): void
    {
        $activeCategory = $this->getActiveCategory();
        $cacheParameter = ['table' => self::triggerTable];
        if (null !== $activeCategory) {
            $cacheParameter['id'] = $activeCategory->id;
        }

        $this->cache->set($this->generateCacheKey(), $breadcrumb, $cacheParameter);
    }

    protected function getFromCache(): ?BreadcrumbDataModel
    {
        return $this->cache->get($this->generateCacheKey());
    }

    protected function generateCacheKey(): string
    {
        $activeCategory = $this->getActiveCategory();
        if (null === $activeCategory) {
            return '';
        }

        return 'breadcrumb_'.self::triggerTable.'_'.$activeCategory->id;
    }

    private function getActiveCategory(): ?\TdbShopCategory
    {
        if (null === $this->activeShopCategory) {
            $activeShopCategory = $this->requestStack->getCurrentRequest()->attributes->get('activeShopCategory', null);
            $this->activeShopCategory = $activeShopCategory;
        }

        return $this->activeShopCategory;
    }
}
