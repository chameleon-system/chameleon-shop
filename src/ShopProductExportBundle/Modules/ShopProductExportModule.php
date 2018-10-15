<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopProductExportBundle\Modules;

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\DbAdapterInterface;
use ChameleonSystem\ShopProductExportBundle\Interfaces\ShopProductExporterInterface;
use ErrorException;
use IMapperCacheTriggerRestricted;
use IMapperVisitorRestricted;

class ShopProductExportModule extends \MTPkgViewRendererAbstractModuleMapper
{
    const PARAM_RESET_CACHE = 'reset';
    private $exportKey;
    private $exportView;
    /**
     * @var \ChameleonSystem\ShopProductExportBundle\Interfaces\ShopProductExporterInterface
     */
    private $productExporter;
    /**
     * @var \ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\DbAdapterInterface
     */
    private $dbAdapter;
    /**
     * @var ConfigurationInterface
     */
    private $configuration;
    /**
     * @var InputFilterUtilInterface
     */
    private $inputFilterUtil;
    /**
     * @var
     */
    private $cacheDir;

    public function __construct(ShopProductExporterInterface $productExporter, DbAdapterInterface $dbAdapter, InputFilterUtilInterface $inputFilterUtil, $cacheDir)
    {
        parent::__construct();

        $this->productExporter = $productExporter;
        $this->dbAdapter = $dbAdapter;
        $this->inputFilterUtil = $inputFilterUtil;
        $this->cacheDir = $cacheDir;
    }

    public function Init()
    {
        parent::Init();

        $this->loadConfiguration();
        $this->getStateDataFromRequest();

        if (false === is_dir($this->cacheDir)) {
            if (!@mkdir($this->cacheDir, 0777, true) && !is_dir($this->cacheDir)) {
                throw new ErrorException(sprintf('failed to create cache folder: %s', $this->cacheDir));
            }
        }
    }

    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapperVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ) {
        $responseData = array(
            'exportData' => null,
            'error' => false,
            'spotName' => $this->sModuleSpotName,
        );

        if (false === $this->productExporter->aliasExists($this->exportView)) {
            $responseData['error'] = 'no-view';
        }

        if (false === $this->isValidExportKey($this->exportKey)) {
            $responseData['error'] = 'invalid-export-key';
        }

        $oVisitor->SetMappedValueFromArray($responseData);

        if (false !== $responseData['error']) {
            return;
        }

        $reset = $this->inputFilterUtil->getFilteredGetInput(self::PARAM_RESET_CACHE);
        $reset = ('true' === $reset || '1' === $reset);

        $cacheFile = $this->cacheDir.'/'.$this->configuration->getId().'.'.$this->exportView;

        if (true === $reset || false === file_exists($cacheFile)) {
            $exportData = $this->productExporter->export($this->configuration, $this->exportView);
            file_put_contents($cacheFile, $exportData);
        }

        if (file_exists($cacheFile)) {
            $exportData = file_get_contents($cacheFile);
        } else {
            $exportData = '';
        }
        $oVisitor->SetMappedValue('exportData', $exportData);
    }

    protected function configureCacheTrigger(IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $oCacheTriggerManager->addTrigger('shop_article');
        $oCacheTriggerManager->addTrigger('shop_module_article_list', $this->configuration->getId());
        $oCacheTriggerManager->addTrigger('shop');
        $oCacheTriggerManager->addTrigger('shop_category');
        $oCacheTriggerManager->addTrigger('shop_manufacturer');
    }

    public function _AllowCache()
    {
        return false;
    }

    private function getStateDataFromRequest()
    {
        $this->getExportKeyFromRequest();
        $this->getViewFromRequest();
    }

    private function getExportKeyFromRequest()
    {
        $this->exportKey = $this->inputFilterUtil->getFilteredInput('key');
    }

    private function getViewFromRequest()
    {
        $this->exportView = $this->inputFilterUtil->getFilteredInput('view');
    }

    private function isValidExportKey($exportKey)
    {
        return $this->productExporter->isValidExportKey($exportKey);
    }

    private function loadConfiguration()
    {
        $this->configuration = $this->dbAdapter->getConfigurationFromInstanceId($this->instanceID);
    }
}
