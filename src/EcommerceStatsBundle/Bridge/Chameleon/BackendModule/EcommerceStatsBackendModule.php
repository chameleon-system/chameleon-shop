<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule;

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\EcommerceStatsBundle\Interfaces\StatsTableServiceInterface;
use IMapperCacheTriggerRestricted;
use IMapperVisitorRestricted;
use MTPkgViewRendererAbstractModuleMapper;
use Symfony\Component\Translation\TranslatorInterface;
use TdbCmsPortalList;
use TdbShopOrderItemList;
use TGlobal;

class EcommerceStatsBackendModule extends MTPkgViewRendererAbstractModuleMapper
{

    /**
     * @var StatsTableServiceInterface
     */
    private $stats;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UrlUtil
     */
    private $urlUtil;

    public function __construct(
        StatsTableServiceInterface $stats,
        TranslatorInterface $translator,
        UrlUtil $urlUtil
    ) {
        parent::__construct();

        $this->stats = $stats;
        $this->translator = $translator;
        $this->urlUtil = $urlUtil;
    }

    /**
     * {@inheritDoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $startDate = $this->getDateUserInput('startDate', 'Y-m-01');
        $endDate = $this->getDateUserInput('endDate', 'Y-m-d');
        $dateGroupType = $this->GetUserInput('dateGroupType', StatsTableServiceInterface::DATA_GROUP_TYPE_DAY);
        $showChange = '1' === $this->GetUserInput('showChange', '0');
        $viewName = $this->GetUserInput('viewName', null);
        $portalId = $this->GetUserInput('portalId', '');

        $urlParameters = [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $startDate->format('Y-m-d'),
            'dateGroupType' => $dateGroupType,
            'showChange' => $showChange ? '1' : '0',
            'portalId' => $portalId,
        ];
        $csvDownloadUrl = $this->urlUtil->getArrayAsUrl($urlParameters, '/cms/chameleon_system_ecommerce_stats/stats.csv?');
        $topSellerDownloadUrl = $this->urlUtil->getArrayAsUrl($urlParameters, '/cms/chameleon_system_ecommerce_stats/topsellers.csv?');

        if (null !== $viewName) {
            $tableData = $this->stats->evaluate($startDate, $endDate, $dateGroupType, $showChange, $portalId);
            $oVisitor->SetMappedValue('tableData', $tableData);
        }

        $oVisitor->SetMappedValue('csvDownloadUrl', $csvDownloadUrl);
        $oVisitor->SetMappedValue('topSellerDownloadUrl', $topSellerDownloadUrl);
        $oVisitor->SetMappedValue('moduleSpotName', $this->sModuleSpotName);
        $oVisitor->SetMappedValue('startDate', $startDate->format('Y-m-d'));
        $oVisitor->SetMappedValue('endDate', $endDate->format('Y-m-d'));
        $oVisitor->SetMappedValue('dateGroupTypeList', $this->getDateGroupTypeList());
        $oVisitor->SetMappedValue('activeDateGroupType', $dateGroupType);
        $oVisitor->SetMappedValue('showChange', $showChange);
        $oVisitor->SetMappedValue('activeViewName', $viewName);
        $oVisitor->SetMappedValue('viewList', $this->getViewList());
        $oVisitor->SetMappedValue('portalList', $this->getPortalList());
        $oVisitor->SetMappedValue('selectedPortalId', $portalId);
    }

    /**
     * @return array<string, string> id => name
     */
    protected function getPortalList(): array
    {
        $portalIdList = [];
        $portalList = TdbCmsPortalList::GetList();

        while ($portal = $portalList->Next()) {
            $portalIdList[$portal->id] = $portal->GetName();
        }

        return $portalIdList;
    }

    /**
     * @return array<string, string>
     */
    private function getViewList(): array
    {
        return [
            'html.table' => $this->translator->trans('chameleon_system_ecommerce_stats.form_output_type_table'),
            'html.barchart' => $this->translator->trans('chameleon_system_ecommerce_stats.form_output_type_chart'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getDateGroupTypeList(): array
    {
        return [
            StatsTableServiceInterface::DATA_GROUP_TYPE_YEAR => $this->translator->trans('chameleon_system_ecommerce_stats.date_year'),
            StatsTableServiceInterface::DATA_GROUP_TYPE_MONTH => $this->translator->trans('chameleon_system_ecommerce_stats.date_month'),
            StatsTableServiceInterface::DATA_GROUP_TYPE_WEEK => $this->translator->trans('chameleon_system_ecommerce_stats.date_week'),
            StatsTableServiceInterface::DATA_GROUP_TYPE_DAY => $this->translator->trans('chameleon_system_ecommerce_stats.date_day'),
        ];
    }

    private function getDateUserInput(string $parameter, string $default): \DateTime
    {
        $dateString = $this->GetUserInput($parameter);
        if (null === $dateString) {
            return \DateTime::createFromFormat('Y-m-d', date($default));
        }

        $dateInstance = \DateTime::createFromFormat('Y-m-d', $dateString);
        if (false === $dateInstance) {
            return \DateTime::createFromFormat('Y-m-d', date($default));
        }

        return $dateInstance;
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
