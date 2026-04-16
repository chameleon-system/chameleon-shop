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

use ChameleonSystem\CoreBundle\Util\InputFilterUtil;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatisticEvaluationRequestDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EcommerceStatsBackendModule extends \MTPkgViewRendererAbstractModuleMapper
{
    public const string STANDARD_CURRENCY_ISO_CODE = 'EUR';
    public const string ALL_STATS_FILTER_NAME = 'allStats';
    public const string CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE = 'CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE';

    public function __construct(
        private readonly StatsTableServiceInterface $stats,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly StatsCurrencyServiceInterface $statsCurrencyService,
        private readonly SecurityHelperAccess $securityHelperAccess,
        private readonly ShopServiceInterface $shopService,
        private readonly InputFilterUtil $inputFilterUtil
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function Accept(\IMapperVisitorRestricted $oVisitor, $bCachingEnabled, \IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        if (false === $this->securityHelperAccess->isGranted(self::CMS_RIGHT_ECOMMERCE_STATS_SHOW_MODULE)) {
            $oVisitor->SetMappedValue('accessDenied', true);

            return;
        }

        $viewName = $this->inputFilterUtil->getFilteredInput('viewName');
        $statsEvaluationRequest = $this->createStatisticEvaluationRequest();
        $customFilters = $statsEvaluationRequest->getCustomFilters();

        $urlParameters = [
            'startDate' => $statsEvaluationRequest->getStartDate()->format('Y-m-d'),
            'endDate' => $statsEvaluationRequest->getEndDate()->format('Y-m-d'),
            'dateGroup' => $statsEvaluationRequest->getDateGroup(),
            'showChange' => $statsEvaluationRequest->isShowDiffColumn() ? '1' : '0',
            'portalId' => $statsEvaluationRequest->getPortalId(),
            'shopId' => $statsEvaluationRequest->getShopId(),
            'currencyId' => $statsEvaluationRequest->getCurrencyId(),
            'statsGroup' => $statsEvaluationRequest->getSelectedStatsGroupSystemName(),
        ];
        $urlParameters = array_merge($urlParameters, $this->getCustomFilterUrlParameters($customFilters));

        $shopStatisticGroupOptions = array_merge(
            [self::ALL_STATS_FILTER_NAME => $this->translator->trans('chameleon_system_ecommerce_stats.form_all_stats_label')],
            $this->getStatisticOptions()
        );

        $tableData = null;
        if (null !== $viewName) {
            $tableData = $this->stats->evaluate($statsEvaluationRequest);
        }

        $oVisitor->SetMappedValueFromArray([
            'csvDownloadUrl' => $this->urlGenerator->generate(
                'chameleon_system_ecommerce_stats.export_csv.stats',
                $urlParameters
            ),
            'topSellerDownloadUrl' => $this->urlGenerator->generate(
                'chameleon_system_ecommerce_stats.export_csv.topsellers',
                $urlParameters
            ),
            'activeViewName' => $viewName,
            'viewOptions' => $this->getViewList(),
            'dateGroupOptions' => $this->getDateGroupOptions(),
            'portalOptions' => $this->getActivePortalOptions(),
            'shopOptions' => $this->shopService->getAllShops(),
            'shopStatisticGroupOptions' => $shopStatisticGroupOptions,
            'currencyOptions' => $this->statsCurrencyService->getCurrencyOptions(),
            'startDate' => $statsEvaluationRequest->getStartDate()->format('Y-m-d'),
            'endDate' => $statsEvaluationRequest->getEndDate()->format('Y-m-d'),
            'showChange' => $statsEvaluationRequest->isShowDiffColumn(),
            'selectedPortalId' => $statsEvaluationRequest->getPortalId(),
            'selectedShopId' => $statsEvaluationRequest->getShopId(),
            'selectedCurrencyId' => $statsEvaluationRequest->getCurrencyId(),
            'selectedShopStatisticGroupName' => $statsEvaluationRequest->getSelectedStatsGroupSystemName(),
            'selectedDateGroup' => $statsEvaluationRequest->getDateGroup(),
            'filterTemplates' => array_merge(
                $this->getDefaultFilterTemplates(),
                $this->getCustomFilterTemplates()
            ),
            'customFilters' => $customFilters,
            'displayGraphLabels' => true,
            'tableData' => $tableData,
        ]);
    }

    protected function createStatisticEvaluationRequest(): StatisticEvaluationRequestDataModel
    {
        $startDate = \DateTime::createFromFormat(
            'Y-m-d',
            (string) $this->inputFilterUtil->getFilteredInput('startDate', date('Y-m-01'))
        );
        $startDate->setTime(0, 0, 0);

        $endDate = \DateTime::createFromFormat(
            'Y-m-d',
            (string) $this->inputFilterUtil->getFilteredInput('endDate', date('Y-m-d'))
        );
        $endDate->setTime(23, 59, 59);

        return new StatisticEvaluationRequestDataModel(
            $startDate,
            $endDate,
            $this->inputFilterUtil->getFilteredInput(
                'dateGroup',
                StatsProviderInterface::DATE_GROUP_DATE
            ),
            '1' === $this->inputFilterUtil->getFilteredInput(
                'showChange',
                '0'
            ),
            $this->inputFilterUtil->getFilteredInput(
                'portalId',
                ''
            ),
            $this->inputFilterUtil->getFilteredInput(
                'currencyId',
                $this->statsCurrencyService->getCurrencyIdByIsoCode(self::STANDARD_CURRENCY_ISO_CODE)
            ),
            $this->inputFilterUtil->getFilteredInput(
                'statsGroup',
                self::ALL_STATS_FILTER_NAME
            ),
            $this->inputFilterUtil->getFilteredInput(
                'shopId',
                ''
            ),
            // @TODO handle custom filter extension
        );
    }

    private function getStatisticOptions(): array
    {
        $groupNames = [];
        $groupList = \TdbPkgShopStatisticGroupList::GetList();
        while ($group = $groupList->Next()) {
            $groupNames[$group->fieldSystemName] = $group->fieldName;
        }

        return $groupNames;
    }

    /**
     * @return array<string, string> id => name
     */
    private function getActivePortalOptions(): array
    {
        $portalIdList = [];
        $portalList = \TdbCmsPortalList::GetList();
        $portalList->AddFilterString("`deactive_portal` = '0'");

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
    private function getDateGroupOptions(): array
    {
        return [
            StatsProviderInterface::DATE_GROUP_YEAR => $this->translator->trans('chameleon_system_ecommerce_stats.date_year'),
            StatsProviderInterface::DATE_GROUP_MONTH => $this->translator->trans('chameleon_system_ecommerce_stats.date_month'),
            StatsProviderInterface::DATE_GROUP_WEEK => $this->translator->trans('chameleon_system_ecommerce_stats.date_week'),
            StatsProviderInterface::DATE_GROUP_DATE => $this->translator->trans('chameleon_system_ecommerce_stats.date_day'),
        ];
    }

    /**
     * @return string[]
     */
    protected function getDefaultFilterTemplates(): array
    {
        return [
            '@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/filter/standard/show-change.html.twig',
            '@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/filter/standard/portal-shop.html.twig',
            '@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/filter/standard/output-row.html.twig',
            '@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/filter/standard/date-row.html.twig',
            '@ChameleonSystemEcommerceStats/snippets-cms/ecommerceStats/filter/standard/details-row.html.twig',
        ];
    }

    /**
     * Override in project-specific subclasses to register additional filter templates.
     *
     * @return string[]
     */
    protected function getCustomFilterTemplates(): array
    {
        return [];
    }

    /**
     * Override in project-specific subclasses to add custom filter values to the stats request.
     *
     * @return array<string, mixed>
     */
    protected function getCustomFilterValues(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $customFilters
     *
     * @return array<string, scalar|null>
     */
    protected function getCustomFilterUrlParameters(array $customFilters): array
    {
        $urlParameters = [];
        foreach ($customFilters as $name => $value) {
            if (null === $value || is_scalar($value)) {
                $urlParameters[$name] = $value;
            }
        }

        return $urlParameters;
    }

    /**
     * @return string[]
     */
    public function GetHtmlHeadIncludes(): array
    {
        $includes = parent::GetHtmlHeadIncludes();

        $jsPath = $this->global->GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/js/ecommerce-stats.js', false);
        $includes[] = sprintf('<script type="text/javascript" src="%s"></script>', $jsPath);
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemcmsdashboard/js/chart.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemcmsdashboard/js/chart-init.4.4.7.js"></script>';
        $cssPath = \TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats.css');
        $printCssPath = \TGlobal::GetStaticURL('/bundles/chameleonsystemecommercestats/ecommerce_stats/css/ecommerce-stats-print.css');
        $includes[] = sprintf('<link href="%s" rel="stylesheet" type="text/css">', $cssPath);
        $includes[] = sprintf('<link href="%s" rel="stylesheet" type="text/css" media="print">', $printCssPath);

        return $includes;
    }
}
