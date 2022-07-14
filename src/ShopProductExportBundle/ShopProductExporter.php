<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopProductExportBundle;

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateFactoryInterface;
use ChameleonSystem\ShopProductExportBundle\Interfaces\ShopProductExporterInterface;
use ChameleonSystem\ShopProductExportBundle\Interfaces\ShopProductExportHandlerInterface;

class ShopProductExporter implements ShopProductExporterInterface
{
    /**
     * @var \ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultFactoryInterface
     */
    private $resultFactory;

    /**
     * @var ShopProductExportHandlerInterface[]
     */
    private $exportHandler = array();

    /**
     * @var string
     */
    private $validShopExportKey;

    /**
     * @var StateFactoryInterface
     */
    private $stateFactory;

    /**
     * @param ResultFactoryInterface $resultFactory
     * @param ShopServiceInterface   $activeShopService
     * @param StateFactoryInterface  $stateFactory
     */
    public function __construct(
        ResultFactoryInterface $resultFactory,
        ShopServiceInterface $activeShopService,
        StateFactoryInterface $stateFactory
    ) {
        $this->resultFactory = $resultFactory;
        $shopConfig = $activeShopService->getConfiguration();
        $this->validShopExportKey = $shopConfig['export_key'];
        $this->stateFactory = $stateFactory;
    }

    public function registerHandler($alias, ShopProductExportHandlerInterface $exportHandler)
    {
        $this->exportHandler[$alias] = $exportHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidExportKey($exportKey)
    {
        return $exportKey === $this->validShopExportKey;
    }

    /**
     * {@inheritdoc}
     */
    public function export(ConfigurationInterface $moduleConfiguration, $alias)
    {
        if (false === $this->aliasExists($alias)) {
            throw new \ErrorException("alias [{$alias}] is not registered. registered handlers are: ".implode(', ', array_keys($this->exportHandler)), 0, E_USER_ERROR, __FILE__, __LINE__);
        }

        $exportHandler = $this->getExportHandler($alias);
        $exportHandler->Init();

        $exportHandler->SetArticleList($this->getResultList($moduleConfiguration));

        ob_start();
        $bSuccess = $exportHandler->Run();
        $exportedData = ob_get_contents();
        ob_end_clean();

        return $exportedData;
    }

    /**
     * {@inheritdoc}
     */
    public function aliasExists($alias)
    {
        return array_key_exists($alias, $this->exportHandler);
    }

    /**
     * @param string $alias
     *
     * @return ShopProductExportHandlerInterface
     */
    private function getExportHandler($alias)
    {
        return $this->exportHandler[$alias];
    }

    /**
     * @param ConfigurationInterface $moduleConfiguration
     *
     * @return \TIterator
     */
    private function getResultList(ConfigurationInterface $moduleConfiguration)
    {
        $state = $this->stateFactory->createState();
        $enrichState = $this->stateFactory->createStateEnrichedWithDefaults($state, $moduleConfiguration);
        $result = $this->resultFactory->createResult($moduleConfiguration, $enrichState);
        $resultArray = $result->asArray();
        $iterator = new \TIterator();
        foreach ($resultArray as $article) {
            $iterator->AddItem($article);
        }

        return $iterator;
    }
}
