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
    /**
     * @var string|null
     */
    private $activePortalId;
    /**
     * @var \TdbShop[] - key = portalId
     */
    private $shops = [];
    /**
     * @var Connection
     */
    private $databaseConnection;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var PortalDomainServiceInterface
     */
    private $portalDomainService;
    /**
     * @var ExtranetUserProviderInterface
     */
    private $extranetUserProvider;
    /**
     * set to true if the recalculation of the basket has been called (and set back to false once that is done.
     *
     * @var bool
     */
    private $basketRecalculationRunning = false;

    /**
     * @return void
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->databaseConnection = $connection;
    }

    public function __construct(PortalDomainServiceInterface $portalDomainService, RequestStack $requestStack, ExtranetUserProviderInterface $extranetUserProvider)
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
        if (null === $request || false == $request->hasSession()) {
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
}
