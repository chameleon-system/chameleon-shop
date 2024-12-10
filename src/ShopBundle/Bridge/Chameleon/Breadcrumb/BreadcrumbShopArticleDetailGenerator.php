<?php

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\Breadcrumb;

use ChameleonSystem\BreadcrumbBundle\Bridge\Chameleon\Breadcrumb\AbstractBreadcrumbGenerator;
use ChameleonSystem\BreadcrumbBundle\Interfaces\BreadcrumbGeneratorUtilsInterface;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbDataModel;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbItemDataModel;
use esono\pkgCmsCache\Cache;
use Symfony\Component\HttpFoundation\RequestStack;

final class BreadcrumbShopArticleDetailGenerator extends AbstractBreadcrumbGenerator
{
    private const triggerTable = 'shop_article';

    private ?\TdbShopArticle $activeShopArticle = null;

    public function __construct(
        private readonly BreadcrumbGeneratorUtilsInterface $breadcrumbGeneratorUtils,
        private readonly RequestStack $requestStack,
        private readonly Cache $cache
    ) {
    }

    public function isActive(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->has('activeShopArticle');
    }

    public function generate(): BreadcrumbDataModel
    {
        $cacheResult = $this->getFromCache();
        if (null !== $cacheResult) {
            return $cacheResult;
        }

        $breadcrumb = new BreadcrumbDataModel();

        $article = $this->getActiveArticle();
        if (null === $article) {
            return $breadcrumb;
        }

        $breadcrumbItem = new BreadcrumbItemDataModel($article->GetName(), $article->getLink(true));
        $breadcrumb->add($breadcrumbItem);

        $articleCategory = $article->GetFieldShopCategory();
        if (null === $articleCategory) {
            $this->setCache($breadcrumb);

            return $breadcrumb;
        }
        $breadcrumbItem = new BreadcrumbItemDataModel($articleCategory->fieldName, $articleCategory->getUrlPath(true));
        $breadcrumb->add($breadcrumbItem);

        $rootCategory = $articleCategory->getRootCategory();
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
        $activeArticle = $this->getActiveArticle();
        $cacheParameter = ['table' => self::triggerTable];
        if (null !== $activeArticle) {
            $cacheParameter['id'] = $activeArticle->id;
        }
        $this->cache->set($this->generateCacheKey(), $breadcrumb, $cacheParameter);
    }

    protected function getFromCache(): ?BreadcrumbDataModel
    {
        return $this->cache->get($this->generateCacheKey());

    }

    protected function generateCacheKey(): string
    {
        $activeArticle = $this->requestStack->getCurrentRequest()->attributes->get('activeShopArticle', null);
        if (null === $activeArticle) {
            return '';
        }

        return 'breadcrumb_'.self::triggerTable.'_'.$activeArticle->id;
    }

    protected function getActiveArticle(): ?\TdbShopArticle
    {
        if (null === $this->activeShopArticle) {
            $activeShopArticle = $this->requestStack->getCurrentRequest()->attributes->get('activeShopArticle', null);
            $this->activeShopArticle = $activeShopArticle;
        }

        return $this->activeShopArticle;
    }
}
