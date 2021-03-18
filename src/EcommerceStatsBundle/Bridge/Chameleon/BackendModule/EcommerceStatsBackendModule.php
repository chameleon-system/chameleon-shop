<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule;

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use Doctrine\DBAL\Connection;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\EcommerceStatsTableInterface;
use IMapperCacheTriggerRestricted;
use IMapperVisitorRestricted;
use MTPkgViewRendererAbstractModuleMapper;
use Symfony\Component\Translation\TranslatorInterface;

class EcommerceStatsBackendModule extends MTPkgViewRendererAbstractModuleMapper
{

    const SEPARATOR = ';';

    protected ?string $startDate = null;
    protected ?string $endDate = null;
    protected string $dateGroupType = EcommerceStatsTableInterface::DATA_GROUP_TYPE_DAY;
    protected bool $showChange = false;
    protected ?string $viewName = 'html.table';
    protected string $selectedPortalId = '';

    /**
     * @var EcommerceStatsTableInterface
     */
    private EcommerceStatsTableInterface $stats;

    private TranslatorInterface $translator;
    private InputFilterUtilInterface $inputFilterUtil;
    private UrlUtil $urlUtil;
    private Connection $connection;

    /**
     * @var false|object|\TCMSLocal|\TdbCmsLocals|null
     */
    private $local;

    public function __construct(
        EcommerceStatsTableInterface $stats,
        Connection $connection,
        TranslatorInterface $translator,
        InputFilterUtilInterface $inputFilterUtil,
        UrlUtil $urlUtil)
    {
        $this->stats = $stats;
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
        $this->local = \TCMSLocal::GetActive();

        $startDate = $this->GetUserInput('startDate');
        if (null === $startDate) {
            $this->startDate = date('Y-m-01');
        } else {
            $this->startDate = $this->local->StringToDate($startDate);
        }

        $endDate = $this->GetUserInput('endDate');
        if (null === $endDate) {
            $this->endDate = date('Y-m-d');
        } else {
            $this->endDate = $this->local->StringToDate($endDate);
        }

        $this->dateGroupType = $this->GetUserInput('dateGroupType', EcommerceStatsTableInterface::DATA_GROUP_TYPE_DAY);
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
            $this->stats->evaluate($this->startDate, $this->endDate, $this->dateGroupType, $this->showChange, $this->selectedPortalId);
            $oVisitor->SetMappedValue('tableData', $this->stats->getTableData());
        }

        static $moduleUrlParameter = ['pagedef', '_pagedefType', 'contentmodule'];
        $filteredRequestData = $this->getFilteredRequestParameterList($moduleUrlParameter);

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
        $portalList = \TdbCmsPortalList::GetList();
        while ($portal = $portalList->Next()) {
            $portalIdList[$portal->id] = $portal->GetName();
        }

        return $portalIdList;
    }

    private function getViewList(): array
    {
        return [
            'html.table' => $this->translator->trans('chameleon_system_ecommerce_stats.form_output_type_table'),
            'html.barchart' => $this->translator->trans('chameleon_system_ecommerce_stats.form_output_type_chart')
        ];
    }

    private function getDateGroupTypeList(): array
    {
        return [
            EcommerceStatsTableInterface::DATA_GROUP_TYPE_YEAR => $this->translator->trans('chameleon_system_ecommerce_stats.date_year'),
            EcommerceStatsTableInterface::DATA_GROUP_TYPE_MONTH => $this->translator->trans('chameleon_system_ecommerce_stats.date_month'),
            EcommerceStatsTableInterface::DATA_GROUP_TYPE_WEEK => $this->translator->trans('chameleon_system_ecommerce_stats.date_week'),
            EcommerceStatsTableInterface::DATA_GROUP_TYPE_DAY => $this->translator->trans('chameleon_system_ecommerce_stats.date_day')
        ];
    }

    protected function downloadTopsellers(): void
    {
        $topSellerOrderItems = $this->getTopsellers();
        $topSellerOrderItems->GoToStart();

        $columnHeaders = [
            'articlenumber' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_article_number'),
            'name' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_article_name'),
            'totalordered' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_order_count'),
            'totalorderedvalue' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_value'),
            'categorypath' => $this->translator->trans('chameleon_system_shop.cms_module_shop_statistic.field_category')
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
        $this->generateCsv($data, $filename, self::SEPARATOR);
    }

    protected function getTopsellers(int $limit = 50): \TdbShopOrderItemList
    {
        $query = 'SELECT SUM(`shop_order_item`.`order_amount`) AS totalordered,
                       SUM(`shop_order_item`.`order_price_after_discounts`) AS totalorderedvalue,
                       `shop_category`.`url_path` AS categorypath, `shop_order_item`.*
                  FROM `shop_order_item`
             LEFT JOIN `shop_order` ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
             LEFT JOIN `shop_article_shop_category_mlt` ON `shop_order_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
             LEFT JOIN `shop_category` ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`

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

        return \TdbShopOrderItemList::GetList($query);
    }

    protected function getCsvFilename(string $basename): string
    {
        return sprintf('%s-%s-%s.csv',
            $basename,
            $this->local->FormatDate($this->startDate, \TCMSLocal::DATEFORMAT_SHOW_DATE),
            $this->local->FormatDate($this->endDate, \TCMSLocal::DATEFORMAT_SHOW_DATE)
        );
    }

    protected function getAsCsv(): void
    {
        $this->stats->evaluate($this->startDate, $this->endDate, $this->dateGroupType, $this->showChange, $this->selectedPortalId);
        $data = $this->stats->getCSVData();
        $filename = $this->getCsvFilename('stats');
        $this->generateCsv($data, $filename, self::SEPARATOR);
    }

    /**
     * @param string[][] $data array of string rows
     * @param string $filename csv file name
     * @param string $separator cell delimiter
     */
    protected function generateCsv(array $data, string $filename, string $separator = self::SEPARATOR): void
    {
        $csv = fopen('php://temp/maxmemory:'. 1024*1024, 'r+');

        foreach ($data as $row) {
            fputcsv($csv, $row, $separator);
        }

        rewind($csv);
        $this->outputAsDownload(stream_get_contents($csv), $filename);
        exit(0);
    }

    protected function outputAsDownload(string $content, string $fileName)
    {
        header('Pragma: public'); // required
        header('Expires: 0'); // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.strlen($content)); // provided file size
        echo $content;
        exit(0);
    }

    /**
     * {@inheritdoc}
     */
    public function GetHtmlHeadIncludes()
    {
        $includes = parent::GetHtmlHeadIncludes();
        $includes[] = '<link href="'.\TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats.css').'" rel="stylesheet" type="text/css" /> ';
        $includes[] = '<link href="'.\TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats-print.css').'" rel="stylesheet" type="text/css" media="print" /> ';

        return $includes;
    }
}
