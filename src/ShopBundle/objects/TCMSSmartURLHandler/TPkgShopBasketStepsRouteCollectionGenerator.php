<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\CoreBundle\Util\RoutingUtilInterface;
use Doctrine\DBAL\Connection;
use esono\pkgCmsRouting\CollectionGeneratorInterface;

class TPkgShopBasketStepsRouteCollectionGenerator implements CollectionGeneratorInterface
{
    /**
     * @var SystemPageServiceInterface
     */
    private $systemPageService;
    /**
     * @var Connection
     */
    private $databaseConnection;
    /**
     * @var RoutingUtilInterface
     */
    private $routingUtil;

    public function __construct(SystemPageServiceInterface $systemPageService, Connection $databaseConnection, RoutingUtilInterface $routingUtil)
    {
        $this->systemPageService = $systemPageService;
        $this->databaseConnection = $databaseConnection;
        $this->routingUtil = $routingUtil;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection($config, TdbCmsPortal $portal, TdbCmsLanguage $language)
    {
        $collection = new Symfony\Component\Routing\RouteCollection();

        $checkoutSystemPage = $this->systemPageService->getSystemPage('checkout', $portal, $language);

        if (null === $checkoutSystemPage) {
            throw new TPkgCmsException_Log('Failed to create routing definition for basket steps because there is no system page "checkout" for the requested portal.', ['portal' => $portal->id]);
        }

        $defaultCheckoutNodeId = $checkoutSystemPage->fieldCmsTreeId;
        $node = TdbCmsTree::GetNewInstance($defaultCheckoutNodeId);
        if (false === $node->sqlData) {
            throw new TPkgCmsException_Log('Failed to create routing definition for basket steps because there is no tree object assigned to the checkout page.');
        } else {
            $checkoutBaseUrl = $this->routingUtil->getLinkForTreeNode($node, $language);
        }

        if ('/' === substr($checkoutBaseUrl, -1)) {
            $checkoutBaseUrl = substr($checkoutBaseUrl, 0, -1);
        }
        $basketSteps = $this->getShopOrderStepList($language->id);

        $firstStep = true;
        foreach ($basketSteps as $basketStep) {
            $route = $this->getRouteForBasketStep(
                $basketStep,
                $checkoutBaseUrl,
                $checkoutSystemPage->id,
                $defaultCheckoutNodeId,
                true === $firstStep
            );
            $collection->add('shop_checkout_'.$basketStep->fieldSystemname, $route);
            $firstStep = false;
        }

        return $collection;
    }

    /**
     * Retrieves the list of basket steps manually to avoid the magic in the TdbShopOrderstep::GetNewInstance() method
     * (which doesn't work if there is no request).
     *
     * @param string $languageId
     *
     * @return array
     */
    private function getShopOrderStepList($languageId)
    {
        $query = 'SELECT * FROM `shop_order_step` ORDER BY `position`';
        $result = $this->databaseConnection->executeQuery($query)->fetchAllAssociative();
        $steps = [];
        foreach ($result as $row) {
            $step = new TdbShopOrderStep();
            $step->SetLanguage($languageId);
            $step->LoadFromRow($row);
            $steps[] = $step;
        }

        return $steps;
    }

    /**
     * @param false|string|null $checkoutBaseUrl
     * @param string $defaultCheckoutPageId
     * @param string $defaultCheckoutNodeId
     * @param bool $isFirstStep
     *
     * @return Symfony\Component\Routing\Route
     */
    private function getRouteForBasketStep(
        TdbShopOrderStep $orderStep,
        $checkoutBaseUrl,
        $defaultCheckoutPageId,
        $defaultCheckoutNodeId,
        $isFirstStep = false
    ) {
        $stepCheckoutNodeId = ('' !== $orderStep->fieldTemplateNodeCmsTreeId) ? $orderStep->fieldTemplateNodeCmsTreeId : $defaultCheckoutNodeId;

        $node = TdbCmsTree::GetNewInstance($stepCheckoutNodeId);
        $linkedPage = $node->GetLinkedPageObject();
        $schemes = [];
        if ($linkedPage->fieldUsessl) {
            $schemes[] = 'https';
        }

        $stepCheckoutPageId = $linkedPage->id;
        $basketStepPattern = "(?i:/{$orderStep->fieldUrlName})/?";
        if (true === $isFirstStep) {
            $basketStepPattern = '|/';
        }

        return new Symfony\Component\Routing\Route("/{$checkoutBaseUrl}{basketStep}",
            [
                '_controller' => 'chameleon_system_shop.basket_step_controller::basketStep',
                'basketStepId' => $orderStep->id,
                'basketStepSystemName' => $orderStep->fieldSystemname,
                'defaultCheckoutPageId' => $defaultCheckoutPageId,
                'defaultCheckoutNodeId' => $defaultCheckoutNodeId,
                'stepCheckoutPageId' => $stepCheckoutPageId,
                'stepCheckoutNodeId' => $stepCheckoutNodeId,
                'containsPortalAndLanguagePrefix' => true,
            ],
            [
                'basketStep' => $basketStepPattern,
            ],
            [],
            '',
            $schemes
        );
    }
}
