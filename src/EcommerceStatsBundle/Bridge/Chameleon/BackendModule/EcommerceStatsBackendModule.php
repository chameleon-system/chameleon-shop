<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule;

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\EcommerceStatsBundle\DataModel\CsvResponse;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsTableCsvExportServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\TopSellerServiceInterface;
use Doctrine\DBAL\Connection;
use IMapperCacheTriggerRestricted;
use IMapperVisitorRestricted;
use MTPkgViewRendererAbstractModuleMapper;
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
     * @var StatsTableCsvExportServiceInterface
     */
    private $csvExportService;

    /**
     * @var TopSellerServiceInterface
     */
    private $topSellerService;

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
        StatsTableCsvExportServiceInterface $csvExportService,
        TopSellerServiceInterface $topSellerService,
        Connection $connection,
        TranslatorInterface $translator,
        InputFilterUtilInterface $inputFilterUtil,
        UrlUtil $urlUtil
    ) {
        parent::__construct();

        $this->stats = $stats;
        $this->csvExportService = $csvExportService;
        $this->topSellerService = $topSellerService;
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
     *
     * @uses getAsCsv as module_fnc
     * @uses downloadTopsellers as module_fnc
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
        // header
        $data = [[
            $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_article_number'),
            $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_article_name'),
            $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_order_count'),
            $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_value'),
            $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_category'),
        ]];

        $topSellerItems = $this->topSellerService->getTopsellers(
            $this->startDate,
            $this->endDate,
            $this->selectedPortalId
        );

        foreach ($topSellerItems as $topSellerItem) {
            $data[] = [
                $topSellerItem->getArticlenumber(),
                $topSellerItem->getName(),
                $this->local->FormatNumber($topSellerItem->getTotalOrdered(), 0),
                $this->local->FormatNumber($topSellerItem->getTotalOrderedValue(), 2),
                $topSellerItem->getCategoryPath(),
            ];
        }

        $filename = $this->getCsvFilename('topseller');
        CsvResponse::fromRows($filename, $data)->sendAndExit();
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

        CsvResponse::fromRows($filename, $data)->sendAndExit();
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
