<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Service;

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

class ShopService implements ShopServiceInterface
{
    private ?string $activePortalId = null;
    /**
     * @var \TdbShop[] - key = portalId
     */
    private array $shops = [];
    private Connection $databaseConnection;
    private RequestStack $requestStack;
    private PortalDomainServiceInterface $portalDomainService;
    private ExtranetUserProviderInterface $extranetUserProvider;
    /**
     * set to true if the recalculation of the basket has been called (and set back to false once that is done.
     */
    private bool $basketRecalculationRunning = false;

    /**
     * @return void
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->databaseConnection = $connection;
    }

    public function __construct(
        PortalDomainServiceInterface $portalDomainService,
        RequestStack $requestStack,
        ExtranetUserProviderInterface $extranetUserProvider)
    {
        $this->requestStack = $requestStack;
        $this->portalDomainService = $portalDomainService;
        $this->extranetUserProvider = $extranetUserProvider;
    }

    /**
     * @return string|null
     */
    private function getActivePortalId()
    {
        if (null !== $this->activePortalId) {
            return $this->activePortalId;
        }
        $portal = $this->portalDomainService->getActivePortal();
        if (null === $portal) {
            $portal = $this->portalDomainService->getDefaultPortal();
        }
        if (null !== $portal) {
            $this->activePortalId = $portal->id;
        }

        return $this->activePortalId;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveShop()
    {
        return $this->getShopForPortalId($this->getActivePortalId());
    }

    /**
     * {@inheritdoc}
     *
     * @param string|null $cmsPortalId
     */
    public function getShopForPortalId($cmsPortalId)
    {
        if (isset($this->shops[$cmsPortalId])) {
            return $this->shops[$cmsPortalId];
        }

        $query = 'SELECT DISTINCT `shop`.*
                  FROM `shop`
            INNER JOIN `shop_cms_portal_mlt` ON `shop`.`id` = `shop_cms_portal_mlt`.`source_id`
                 WHERE `shop_cms_portal_mlt`.`target_id` = :portalId';

        $shopData = $this->databaseConnection->fetchAssociative($query, ['portalId' => $cmsPortalId]);
        if (false === $shopData) {
            throw new \ErrorException("no shop configured for portal {$cmsPortalId}", 0, E_USER_ERROR, __FILE__, __LINE__);
        }
        $this->shops[$cmsPortalId] = \TdbShop::GetNewInstance($shopData);

        return $this->shops[$cmsPortalId];
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getActiveShop()->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->getActiveShop()->sqlData;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveProduct()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        return $request->attributes->get('activeShopArticle');
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveCategory()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        return $request->attributes->get('activeShopCategory');
    }

    public function getActiveRootCategory(): ?\TdbShopCategory
    {
        static $activeRootCategory = false;
        if (false === $activeRootCategory) {
            $activeRootCategory = null;
            $oActiveCategory = $this->getActiveCategory();
            $activeRootCategory = $oActiveCategory?->GetRootCategory();
        }

        return $activeRootCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveBasket()
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        if (false === $request->hasSession()) {
            return null;
        }

        $session = $request->getSession();

        if (false === $session->isStarted()) {
            return null;
        }

        if (false === $session->has(\TShopBasket::SESSION_KEY_NAME)) {
            $session->set(\TShopBasket::SESSION_KEY_NAME, new \TShopBasket());
        }

        $oInstance = $session->get(\TShopBasket::SESSION_KEY_NAME);

        if (true === $oInstance->BasketRequiresRecalculation() && false === $this->basketRecalculationRunning) {
            $this->basketRecalculationRunning = true;
            $oInstance->RecalculateBasket();
            $this->basketRecalculationRunning = false;
        }

        return $oInstance;
    }

    public function resetBasket()
    {
        $this->extranetUserProvider->getActiveUser()->ObserverUnregister('oUserBasket');

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || false === $request->hasSession()) {
            return;
        }
        $session = $request->getSession();

        if (true === $session->has(\TShopBasket::SESSION_KEY_NAME)) {
            $session->remove(\TShopBasket::SESSION_KEY_NAME);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBasketLink($useRedirect = true)
    {
        $bTargetBasketPageWithoutRedirect = (false === $useRedirect);

        return $this->getActiveShop()->GetBasketLink(false, $bTargetBasketPageWithoutRedirect);
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckoutLink($useRedirect = true)
    {
        $bTargetBasketPageWithoutRedirect = (false === $useRedirect);

        return $this->getActiveShop()->GetBasketLink(true, $bTargetBasketPageWithoutRedirect);
    }

    public function getProductCountForShop(
        string $shopId,
        bool $onlyActive = false,
        bool $onlyMainProducts = false,
        bool $onlyVariants = false,
        bool $isVirtualProduct = false,
        bool $isNew = false,
        bool $isSearchable = false
    ): int {
        $query = 'SELECT COUNT(*) AS count
              FROM `shop_article`
              LEFT JOIN `shop_article_shop_mlt`
              ON `shop_article`.`id` = `shop_article_shop_mlt`.`source_id`
              WHERE (`shop_article_shop_mlt`.`target_id` = :shopId
                     OR `shop_article_shop_mlt`.`target_id` IS NULL)';

        if (true === $onlyActive) {
            $query .= " AND `shop_article`.`active` = '1'";
        }

        if (true === $onlyMainProducts && false === $onlyVariants) {
            $query .= " AND (`shop_article`.`variant_parent_id` = '' OR `shop_article`.`variant_parent_id` IS NULL)";
        }

        if (true === $onlyVariants && false === $onlyMainProducts) {
            $query .= " AND `shop_article`.`variant_parent_id` IS NOT NULL AND `shop_article`.`variant_parent_id` != ''";
        }

        if (true === $isVirtualProduct) {
            $query .= " AND `shop_article`.`virtual_article` = '1'";
        }

        if (true === $isNew) {
            $query .= " AND `shop_article`.`is_new` = '1'";
        }

        if (true === $isSearchable) {
            $query .= " AND `shop_article`.`is_searchable` = '1'";
        }

        $result = $this->databaseConnection->fetchAssociative($query, ['shopId' => $shopId]);

        // Extract and return the count as an integer
        return isset($result['count']) ? (int) $result['count'] : 0;
    }

    /**
     * @return array - key = id, value = name
     */
    public function getAllShops(): array
    {
        $shopData = [];
        $shopList = \TdbShopList::GetList('SELECT `shop`.`id`, `shop`.`name` FROM `shop`');
        while ($shop = $shopList->Next()) {
            $shopData[$shop->id] = $shop->GetName();
        }

        return $shopData;
    }

    public function getCategoryCountForShop(string $shopId): int
    {
        $connection = $this->databaseConnection; // Using DBAL connection

        // Step 1: Get all root nodes for the given shopId
        $rootNodesSql = "
        SELECT `pkg_shop_primary_navi`.`target` as id,
               `pkg_shop_primary_navi`.`cms_portal_id`
        FROM `pkg_shop_primary_navi`
        INNER JOIN `shop_cms_portal_mlt` ON `pkg_shop_primary_navi`.`cms_portal_id` = `shop_cms_portal_mlt`.`target_id`
        WHERE `pkg_shop_primary_navi`.`target_table_name` = 'shop_category'
          AND `pkg_shop_primary_navi`.`active` = '1'
          AND `shop_cms_portal_mlt`.`source_id` = ?
    ";

        $rootNodes = $connection->fetchAllAssociative($rootNodesSql, [$shopId], [\PDO::PARAM_STR]);

        $categoryCount = 0;

        // Step 2: For each root node, count categories recursively
        foreach ($rootNodes as $rootNode) {
            $categoryCount += $this->countActiveCategoriesRecursively($rootNode['id']);
        }

        return $categoryCount;
    }

    private function countActiveCategoriesRecursively(string $categoryId): int
    {
        $connection = $this->databaseConnection;

        // Query to fetch active child categories
        $childCategoriesSql = "
        SELECT `shop_category`.`id`
        FROM `shop_category`
        WHERE `shop_category`.`shop_category_id` = ?
          AND `shop_category`.`active` = '1'
    ";

        $childCategories = $connection->fetchAllAssociative($childCategoriesSql, [$categoryId], [\PDO::PARAM_STR]);

        $count = 1; // Include the current category

        foreach ($childCategories as $childCategory) {
            $count += $this->countActiveCategoriesRecursively($childCategory['id']);
        }

        return $count;
    }
}
