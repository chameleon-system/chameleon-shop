<?php

namespace ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidget;
use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class DashboardBaseWidget extends DashboardWidget
{
    private array $statsCache = [];


    public function __construct(
        protected readonly CacheInterface $cache,
        protected readonly \ViewRenderer $renderer,
        protected readonly StatsTableServiceInterface $stats,
        protected readonly TranslatorInterface $translator,
        protected readonly StatsCurrencyServiceInterface $statsCurrencyService)
    {
        parent::__construct($cache);
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getDropdownItems(): array
    {
        return [];
    }

    protected function generateBodyHtml(): string
    {
        return '';
    }

    protected function getStatsGroup(string $statsSystemName)
    {
        if (array_key_exists($statsSystemName, $this->statsCache)) {
            return $this->statsCache[$statsSystemName];
        }

        $endDate = new \DateTime('now');

        $startDate = clone $endDate;
        $startDate->modify(DashboardWidget::DEFAULT_TIMEFRAME_FOR_STATS);

        $statistic = $this->stats->evaluate(
            $startDate,
            $endDate,
            'day',
            false,
            '',
            $this->statsCurrencyService->getCurrencyIdByIsoCode(EcommerceStatsBackendModule::STANDARD_CURRENCY_ISO_CODE),
            $statsSystemName
        );

        $statisticBlocks = $statistic->getBlocks();

        $this->statsCache[$statsSystemName] = $statisticBlocks[$statsSystemName] ?? null;

        return $this->statsCache[$statsSystemName];
    }

    public function getFooterIncludes(): array
    {
        $includes = parent::getFooterIncludes();
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart.4.4.7.js"></script>';
        $includes[] = '<script type="text/javascript" src="/bundles/chameleonsystemecommercestats/ecommerce_stats/js/chart-init.4.4.7.js"></script>';

        return $includes;
    }
}
