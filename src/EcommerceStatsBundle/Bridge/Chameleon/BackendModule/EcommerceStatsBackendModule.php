<?php

declare(strict_types=1);

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule;

use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EcommerceStatsBackendModule extends \MTPkgViewRendererAbstractModuleMapper
{
    public const STANDARD_CURRENCY_ISO_CODE = 'EUR';
    public const ALL_STATS_FILTER_NAME = 'allStats';

    private StatsTableServiceInterface $stats;
    private TranslatorInterface $translator;
    private UrlUtil $urlUtil;
    private StatsCurrencyServiceInterface $statsCurrencyService;

    public function __construct(
        StatsTableServiceInterface $stats,
        TranslatorInterface $translator,
        UrlUtil $urlUtil,
        StatsCurrencyServiceInterface $statsCurrencyService,
        private readonly StatsProviderInterface $statsProviderCollection
    ) {
        parent::__construct();

        $this->stats = $stats;
        $this->translator = $translator;
        $this->urlUtil = $urlUtil;
        $this->statsCurrencyService = $statsCurrencyService;
    }

    /**
     * {@inheritDoc}
     */
    public function Accept(\IMapperVisitorRestricted $oVisitor, $bCachingEnabled, \IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        $startDate = $this->getDateUserInput('startDate', 'Y-m-01')->setTime(0, 0, 0);
        $endDate = $this->getDateUserInput('endDate', 'Y-m-d')->setTime(23, 59, 59);

        /** @var string $dateGroupType */
        $dateGroupType = $this->GetUserInput('dateGroupType', StatsProviderInterface::DATA_GROUP_TYPE_DAY);
        $showChange = '1' === $this->GetUserInput('showChange', '0');
        $viewName = $this->GetUserInput('viewName', null);
        $currencyId = $this->GetUserInput('currency', $this->statsCurrencyService->getCurrencyIdByIsoCode(self::STANDARD_CURRENCY_ISO_CODE));
        $selectedStatsGroupSystemName = $this->GetUserInput('statsGroup', self::ALL_STATS_FILTER_NAME);

        /** @var string $portalId */
        $portalId = $this->GetUserInput('portalId', '');

        $urlParameters = [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'dateGroupType' => $dateGroupType,
            'showChange' => $showChange ? '1' : '0',
            'portalId' => $portalId,
        ];
        $csvDownloadUrl = $this->urlUtil->getArrayAsUrl($urlParameters, '/cms/chameleon_system_ecommerce_stats/stats.csv?');
        $topSellerDownloadUrl = $this->urlUtil->getArrayAsUrl($urlParameters, '/cms/chameleon_system_ecommerce_stats/topsellers.csv?');

        if (null !== $viewName) {
            $tableData = $this->stats->evaluate(
                $startDate,
                $endDate,
                $dateGroupType,
                $showChange,
                $portalId,
                $currencyId,
                $selectedStatsGroupSystemName
            );
            $oVisitor->SetMappedValue('tableData', $tableData);
        }

        $currencyList = $this->statsCurrencyService->getAllCurrencies();

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
        $oVisitor->SetMappedValue('currencyList', $currencyList);
        $oVisitor->SetMappedValue('currencyId', $currencyId);
        $oVisitor->SetMappedValue('selectedStatsGroupSystemName', $selectedStatsGroupSystemName);
        $oVisitor->SetMappedValue('statsGroupsSelection', $this->statsProviderCollection->fetchAllStatisticGroupsNames());
        $oVisitor->SetMappedValue('displayGraphLabels', true);
    }

    /**
     * @return array<string, string> id => name
     */
    private function getPortalList(): array
    {
        $portalIdList = [];
        $portalList = \TdbCmsPortalList::GetList();

        while ($portal = $portalList->Next()) {
            $portalIdList[(string) $portal->id] = (string) $portal->GetName();
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
            StatsProviderInterface::DATA_GROUP_TYPE_YEAR => $this->translator->trans('chameleon_system_ecommerce_stats.date_year'),
            StatsProviderInterface::DATA_GROUP_TYPE_MONTH => $this->translator->trans('chameleon_system_ecommerce_stats.date_month'),
            StatsProviderInterface::DATA_GROUP_TYPE_WEEK => $this->translator->trans('chameleon_system_ecommerce_stats.date_week'),
            StatsProviderInterface::DATA_GROUP_TYPE_DAY => $this->translator->trans('chameleon_system_ecommerce_stats.date_day'),
        ];
    }

    private function getDateUserInput(string $parameter, string $default): \DateTime
    {
        /** @var string|null $dateString */
        $dateString = $this->GetUserInput($parameter);
        if (null === $dateString) {
            $dateInstance = \DateTime::createFromFormat('Y-m-d', date($default));
            if (false !== $dateInstance) {
                return $dateInstance;
            }
        }

        return \DateTime::createFromFormat('Y-m-d', (string) $dateString)
            ?: \DateTime::createFromFormat('Y-m-d', date($default))
            ?: new \DateTime();
    }

    /**
     * @return string[]
     */
    public function GetHtmlHeadIncludes(): array
    {
        $includes = parent::GetHtmlHeadIncludes();

        $jsPath = $this->global->GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/js/ecommerce-stats.js', false);
        $includes[] = sprintf('<script type="text/javascript" src="%s"></script>', $jsPath);
        $includes[] = '<script type="text/javascript" src="'.$this->global->GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart.4.4.7.js', false).'"></script>';
        $includes[] = '<script type="text/javascript" src="'.$this->global->GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart-init.4.4.7.js', false).'"></script>';
        $cssPath = \TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats.css');
        $printCssPath = \TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats-print.css');
        $includes[] = sprintf('<link href="%s" rel="stylesheet" type="text/css">', $cssPath);
        $includes[] = sprintf('<link href="%s" rel="stylesheet" type="text/css" media="print">', $printCssPath);

        return $includes;
    }
}
