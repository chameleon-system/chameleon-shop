<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule;

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Service\StatsTableCsvExportService;
use Doctrine\DBAL\Connection;
use IMapperCacheTriggerRestricted;
use IMapperVisitorRestricted;
use MTPkgViewRendererAbstractModuleMapper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Translation\TranslatorInterface;
use TCMSLocal;
use TdbCmsPortalList;
use TdbShopOrderItemList;
use TGlobal;

class EcommerceStatsBackendModule extends MTPkgViewRendererAbstractModuleMapper
{
    private const SEPARATOR = ';';

    /**
     * Date in format `Y-m-d`.
     *
     * @var string|null
     */
    protected $startDate = null;

    /**
     * Date in format `Y-m-d`.
     *
     * @var string|null
     */
    protected $endDate = null;

    /**
     * @var string
     */
    protected $dateGroupType = StatsTableServiceInterface::DATA_GROUP_TYPE_DAY;

    /**
     * @var bool
     */
    protected $showChange = false;

    /**
     * @var string|null
     */
    protected $viewName = 'html.table';

    /**
     * @var string
     */
    protected $selectedPortalId = '';

    /**
     * @var StatsTableServiceInterface
     */
    private $stats;

    /**
     * @var StatsTableCsvExportService
     */
    private $csvExportService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var InputFilterUtilInterface
     */
    private $inputFilterUtil;

    /**
     * @var UrlUtil
     */
    private $urlUtil;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TCMSLocal
     */
    private $local;

    public function __construct(
        StatsTableServiceInterface $stats,
        StatsTableCsvExportService $csvExportService,
        Connection $connection,
        TranslatorInterface $translator,
        InputFilterUtilInterface $inputFilterUtil,
        UrlUtil $urlUtil)
    {
        parent::__construct();

        $this->stats = $stats;
        $this->csvExportService = $csvExportService;
        $this->connection = $connection;
        $this->translator = $translator;
        $this->inputFilterUtil = $inputFilterUtil;
        $this->urlUtil = $urlUtil;
    }

    /**
     * {@inheritDoc}
     */
    public function Init(): void
    {
        $this->local = TCMSLocal::GetActive();

        $this->startDate = $this->GetUserInput('startDate') ?? date('Y-m-01');
        $this->endDate = $this->GetUserInput('endDate') ?? date('Y-m-d');

        $this->dateGroupType = $this->GetUserInput('dateGroupType', StatsTableServiceInterface::DATA_GROUP_TYPE_DAY);
        $this->showChange = '1' === $this->GetUserInput('showChange', '0');
        $this->viewName = $this->GetUserInput('viewName', null);
        $this->selectedPortalId = $this->GetUserInput('portalId', '');
    }

    /**
     * {@inheritDoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        if (null !== $this->viewName) {
            $tableData = $this->stats->evaluate($this->startDate, $this->endDate, $this->dateGroupType, $this->showChange, $this->selectedPortalId);
            $oVisitor->SetMappedValue('tableData', $tableData);
        }

        $filteredRequestData = $this->getFilteredRequestParameterList(['pagedef', '_pagedefType', 'contentmodule']);

        $csvUrlParameter = ['module_fnc' => [$this->sModuleSpotName => 'getAsCsv']];
        $csvDownloadUrl = $this->urlUtil->getArrayAsUrl(\array_merge($csvUrlParameter, $filteredRequestData));
        $oVisitor->SetMappedValue('csvDownloadUrl', '?'.$csvDownloadUrl);

        $downloadUrlParameter = ['module_fnc' => [$this->sModuleSpotName => 'downloadTopsellers']];
        $topSellerDownloadUrl = $this->urlUtil->getArrayAsUrl(\array_merge($downloadUrlParameter, $filteredRequestData));
        $oVisitor->SetMappedValue('topSellerDownloadUrl', '?'.$topSellerDownloadUrl);

        $oVisitor->SetMappedValue('moduleSpotName', $this->sModuleSpotName);
        $oVisitor->SetMappedValue('startDate', $this->startDate);
        $oVisitor->SetMappedValue('endDate', $this->endDate);
        $oVisitor->SetMappedValue('dateGroupTypeList', $this->getDateGroupTypeList());
        $oVisitor->SetMappedValue('activeDateGroupType', $this->dateGroupType);
        $oVisitor->SetMappedValue('showChange', $this->showChange);
        $oVisitor->SetMappedValue('activeViewName', $this->viewName);
        $oVisitor->SetMappedValue('viewList', $this->getViewList());
        $oVisitor->SetMappedValue('portalList', $this->getPortalList());
        $oVisitor->SetMappedValue('selectedPortalId', $this->selectedPortalId);
    }

    private function getFilteredRequestParameterList(array $parameter = []): array
    {
        $filteredRequestData = [];
        foreach ($parameter as $parameterKey) {
            $filteredRequestData[$parameterKey] = $this->inputFilterUtil->getFilteredInput($parameterKey, null, false, 'TCMSUserInputFilter');
        }

        return $filteredRequestData;
    }

    /**
     * {@inheritdoc}
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'getAsCsv';
        $this->methodCallAllowed[] = 'downloadTopsellers';
    }

    protected function getPortalList(): array
    {
        $portalIdList = [];
        $portalList = TdbCmsPortalList::GetList();
        while ($portal = $portalList->Next()) {
            $portalIdList[$portal->id] = $portal->GetName();
        }

        return $portalIdList;
    }

    private function getViewList(): array
    {
        return [
            'html.table' => $this->translator->trans('chameleon_system_ecommerce_stats.form_output_type_table'),
            'html.barchart' => $this->translator->trans('chameleon_system_ecommerce_stats.form_output_type_chart'),
        ];
    }

    private function getDateGroupTypeList(): array
    {
        return [
            StatsTableServiceInterface::DATA_GROUP_TYPE_YEAR => $this->translator->trans('chameleon_system_ecommerce_stats.date_year'),
            StatsTableServiceInterface::DATA_GROUP_TYPE_MONTH => $this->translator->trans('chameleon_system_ecommerce_stats.date_month'),
            StatsTableServiceInterface::DATA_GROUP_TYPE_WEEK => $this->translator->trans('chameleon_system_ecommerce_stats.date_week'),
            StatsTableServiceInterface::DATA_GROUP_TYPE_DAY => $this->translator->trans('chameleon_system_ecommerce_stats.date_day'),
        ];
    }

    /**
     * @return never-returns - Uses `exit()` to finish the current request
     */
    protected function downloadTopsellers(): void
    {
        $topSellerOrderItems = $this->getTopsellers();
        $topSellerOrderItems->GoToStart();

        $columnHeaders = [
            'articlenumber' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_article_number'),
            'name' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_article_name'),
            'totalordered' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_order_count'),
            'totalorderedvalue' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_value'),
            'categorypath' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_category'),
        ];

        $numbers = ['totalordered' => 0, 'totalorderedvalue' => 2];

        $data = [array_values($columnHeaders)]; // header

        while ($orderItem = $topSellerOrderItems->Next()) {
            $row = [];
            foreach ($columnHeaders as $fieldName => $fieldTitle) {
                $fieldValue = $orderItem->sqlData[$fieldName];
                if (true === array_key_exists($fieldName, $numbers)) {
                    $fieldValue = $this->local->FormatNumber($fieldValue, $numbers[$fieldName]);
                }
                $row[] = str_replace('"', "'", $fieldValue);
            }

            $data[] = $row;
        }

        $filename = $this->getCsvFilename('topseller');
        $csv = $this->generateCsv($data, self::SEPARATOR);
        $this->outputAsDownload($csv, $filename, 'text/csv');
    }

    protected function getTopsellers(int $limit = 50): TdbShopOrderItemList
    {
        $query = '
            SELECT 
                SUM(`shop_order_item`.`order_amount`) AS totalordered,
                SUM(`shop_order_item`.`order_price_after_discounts`) AS totalorderedvalue,
                `shop_category`.`url_path` AS categorypath, 
                `shop_order_item`.*
            FROM `shop_order_item`
                LEFT JOIN `shop_order`
                    ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
                LEFT JOIN `shop_article_shop_category_mlt` 
                    ON `shop_order_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
                LEFT JOIN `shop_category` 
                    ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
               ';
        $baseConditionList = [];
        if (null !== $this->startDate) {
            $baseConditionList[] = '`shop_order`.`datecreated` >= '.$this->connection->quote($this->startDate);
        }
        if (null !== $this->endDate) {
            $baseConditionList[] = '`shop_order`.`datecreated` <= '.$this->connection->quote($this->endDate.' 23:59:59');
        }
        if ('' !== $this->selectedPortalId) {
            $baseConditionList[] = '`shop_order`.`cms_portal_id` = '.$this->connection->quote($this->selectedPortalId);
        }

        if (count($baseConditionList) > 0) {
            $query .= ' WHERE ('.implode(') AND (', $baseConditionList).')';
        }

        $query .= ' GROUP BY `shop_category`.`id`, `shop_order_item`.`shop_article_id`';
        $query .= ' ORDER BY totalordered DESC ';
        $query .= ' LIMIT 0,'.$limit;

        return TdbShopOrderItemList::GetList($query);
    }

    protected function getCsvFilename(string $basename): string
    {
        return sprintf('%s-%s-%s.csv',
            $basename,
            $this->local->FormatDate($this->startDate, TCMSLocal::DATEFORMAT_SHOW_DATE),
            $this->local->FormatDate($this->endDate, TCMSLocal::DATEFORMAT_SHOW_DATE)
        );
    }

    /**
     * @return never-returns - Uses `exit()` to finish the current request
     */
    protected function getAsCsv(): void
    {
        $tableData = $this->stats->evaluate($this->startDate, $this->endDate, $this->dateGroupType, $this->showChange, $this->selectedPortalId);

        $data = $this->csvExportService->getCSVData($tableData);
        $filename = $this->getCsvFilename('stats');
        $csv = $this->generateCsv($data, self::SEPARATOR);
        $this->outputAsDownload($csv, $filename, 'text/csv');
    }

    /**
     * @param string[][] $data      array of string rows
     * @param string     $separator cell delimiter
     */
    protected function generateCsv(array $data, string $separator = self::SEPARATOR): string
    {
        $csv = fopen('php://temp/maxmemory:'. 1024 * 1024, 'r+');

        foreach ($data as $row) {
            fputcsv($csv, $row, $separator);
        }

        rewind($csv);

        return (string) stream_get_contents($csv);
    }

    /**
     * @return never-returns - Uses `exit()` to finish the current request
     */
    protected function outputAsDownload(string $content, string $fileName, string $contentType): void
    {
        $response = new Response($content, 200, [
            'Pragma' => 'public',
            'Expires' => 0,

            'Content-Type' => $contentType,
            'Content-Transfer-Encoding' => 'binary',
        ]);

        // Disable caching
        $response->setPrivate();
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->addCacheControlDirective('post-check', '0');
        $response->headers->addCacheControlDirective('pre-check', '0');
        $response->headers->addCacheControlDirective('private');

        // Force download
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        ));

        $response->send();
        exit(0);
    }

    /**
     * {@inheritdoc}
     */
    public function GetHtmlHeadIncludes()
    {
        $includes = parent::GetHtmlHeadIncludes();

        $cssPath = TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats.css');
        $printCssPath = TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats-print.css');
        $includes[] = sprintf('<link href="%s" rel="stylesheet" type="text/css">', $cssPath);
        $includes[] = sprintf('<link href="%s" rel="stylesheet" type="text/css" media="print">', $printCssPath);

        return $includes;
    }
}
