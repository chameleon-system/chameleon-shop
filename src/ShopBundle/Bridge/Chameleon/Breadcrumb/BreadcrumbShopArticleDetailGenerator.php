<?php

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\Breadcrumb;

use ChameleonSystem\BreadcrumbBundle\Interfaces\BreadcrumbGeneratorInterface;
use ChameleonSystem\BreadcrumbBundle\Interfaces\BreadcrumbGeneratorUtilsInterface;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbDataModel;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbItemDataModel;
use Symfony\Component\HttpFoundation\RequestStack;

final class BreadcrumbShopArticleDetailGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct(
        private readonly BreadcrumbGeneratorUtilsInterface $breadcrumbGeneratorUtils,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function isActive(): bool
    {
        if ($this->requestStack->getCurrentRequest()->attributes->has('activeShopArticle')) {
            return true;
        }

        return false;
    }

    public function generate(): BreadcrumbDataModel
    {
        $breadcrumb = new BreadcrumbDataModel();

        $article = $this->requestStack->getCurrentRequest()->attributes->get('activeShopArticle');
        if (!$article) {
            return $breadcrumb;
        }

        $breadcrumbItem = new BreadcrumbItemDataModel($article->GetName(), $article->getLink(true));
        $breadcrumb->add($breadcrumbItem);

        $articleCategory = $article->GetFieldShopCategory();
        if (null === $articleCategory) {
            return $breadcrumb;
        }
        $breadcrumbItem = new BreadcrumbItemDataModel($articleCategory->fieldName, $articleCategory->getUrlPath(true));
        $breadcrumb->add($breadcrumbItem);

        $rootCategory = $articleCategory->getRootCategory();
        if (null === $rootCategory) {
            return $breadcrumb;
        }
        $breadcrumbItem = new BreadcrumbItemDataModel($rootCategory->fieldName, $rootCategory->getUrlPath(true));
        $breadcrumb->add($breadcrumbItem);

        $this->breadcrumbGeneratorUtils->attachHomePageBreadcrumbItem($breadcrumb);

        return $breadcrumb;
    }
}
