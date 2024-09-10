<?php

namespace ChameleonSystem\ShopBundle\Bridge\Chameleon\Breadcrumb;

use ChameleonSystem\BreadcrumbBundle\Interfaces\BreadcrumbGeneratorInterface;
use ChameleonSystem\BreadcrumbBundle\Interfaces\BreadcrumbGeneratorUtilsInterface;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbDataModel;
use ChameleonSystem\BreadcrumbBundle\Library\DataModel\BreadcrumbItemDataModel;
use Symfony\Component\HttpFoundation\RequestStack;

final class BreadcrumbShopCategoryListGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct(
        private readonly BreadcrumbGeneratorUtilsInterface $breadcrumbGeneratorUtils,
        private readonly RequestStack $requestStack,
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
        $breadcrumb = new BreadcrumbDataModel();

        $activeShopCategory = $this->requestStack->getCurrentRequest()->attributes->get('activeShopCategory');
        if (!$activeShopCategory) {
            return $breadcrumb;
        }

        $breadcrumbItem = new BreadcrumbItemDataModel($activeShopCategory->GetName(), $activeShopCategory->getLink(true));
        $breadcrumb->add($breadcrumbItem);

        $rootCategory = $activeShopCategory->getRootCategory();
        if (null === $rootCategory) {
            return $breadcrumb;
        }
        $breadcrumbItem = new BreadcrumbItemDataModel($rootCategory->fieldName, $rootCategory->getUrlPath(true));
        $breadcrumb->add($breadcrumbItem);

        $this->breadcrumbGeneratorUtils->attachHomePageBreadcrumbItem($breadcrumb);

        return $breadcrumb;
    }
}
