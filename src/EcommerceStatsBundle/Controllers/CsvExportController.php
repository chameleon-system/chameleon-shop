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

namespace ChameleonSystem\EcommerceStatsBundle\Controllers;

use ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\BackendModule\EcommerceStatsBackendModule;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\CsvResponse;
use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\StatisticEvaluationRequestDataModel;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\CsvExportServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsCurrencyServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\TopSellerServiceInterface;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use ChameleonSystem\SecurityBundle\Voter\CmsUserRoleConstants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

readonly class CsvExportController
{
    public function __construct(
        private CsvExportServiceInterface $csvExportService,
        private StatsTableServiceInterface $statsTableService,
        private TopSellerServiceInterface $topSellerService,
        private SecurityHelperAccess $securityHelperAccess,
        private StatsCurrencyServiceInterface $statsCurrencyService,
    ) {}

    public function exportStatistics(Request $request): Response
    {
        $this->throwIfNoBackendUserLoggedIn();

        $statisticEvaluationRequest = $this->createStatisticEvaluationRequest($request);
        $statsTable = $this->statsTableService->evaluate($statisticEvaluationRequest);
        $csvData = $this->csvExportService->getCsvDataFromStatsTable($statsTable);
        $fileName = $this->getCsvFilename('stats',
            $statisticEvaluationRequest->getStartDate(),
            $statisticEvaluationRequest->getEndDate()
        );

        return CsvResponse::fromRows($fileName, $csvData);
    }

    public function exportTopSellers(Request $request): Response
    {
        $this->throwIfNoBackendUserLoggedIn();

        $startDate = \DateTime::createFromFormat(
            'Y-m-d',
            (string) $request->get('startDate', date('Y-m-01'))
        );
        $startDate->setTime(0, 0, 0);

        $endDate = \DateTime::createFromFormat(
            'Y-m-d',
            (string) $request->get('endDate', date('Y-m-d'))
        );
        $endDate->setTime(23, 59, 59);

        $selectedPortalId = $request->get('portalId', '');
        $selectedShopId = $request->get('shopId', '');

        $limit = $request->request->getInt('limit', 50);

        $topSellers = $this->topSellerService->getTopsellers($startDate, $endDate, $selectedPortalId, $selectedShopId, $limit);
        $csvData = $this->csvExportService->getCsvDataFromTopsellers($topSellers);
        $fileName = $this->getCsvFilename('topsellers', $startDate, $endDate);

        return CsvResponse::fromRows($fileName, $csvData);
    }

    private function createStatisticEvaluationRequest(Request $request): StatisticEvaluationRequestDataModel
    {
        $startDate = \DateTime::createFromFormat(
            'Y-m-d',
            (string) $request->get('startDate', date('Y-m-01'))
        );
        $startDate->setTime(0, 0, 0);

        $endDate = \DateTime::createFromFormat(
            'Y-m-d',
            (string) $request->get('endDate', date('Y-m-d'))
        );
        $endDate->setTime(23, 59, 59);

        return new StatisticEvaluationRequestDataModel(
            $startDate,
            $endDate,
            $request->get(
                'dateGroup',
                $request->get('dateGroup', StatsProviderInterface::DATE_GROUP_DAY)
            ),
            filter_var(
                $request->get('showChange', false),
                FILTER_VALIDATE_BOOLEAN
            ),
            $request->get('portalId', ''),
            $request->get(
                'currencyId',
                $this->statsCurrencyService->getCurrencyIdByIsoCode(EcommerceStatsBackendModule::STANDARD_CURRENCY_ISO_CODE)
            ),
            $request->get(
                'statsGroup',
                EcommerceStatsBackendModule::ALL_STATS_FILTER_NAME
            ),
            $request->get('shopId', ''),
            // @TODO get custom filters
        );
    }

    private function getCsvFilename(string $basename, \DateTime $startDate, \DateTime $endDate): string
    {
        return sprintf(
            '%s-%s-%s.csv',
            $basename,
            $startDate->format('d.m.Y'),
            $endDate->format('d.m.Y')
        );
    }

    private function throwIfNoBackendUserLoggedIn(): void
    {
        if (false === $this->securityHelperAccess->isGranted(CmsUserRoleConstants::CMS_USER)) {
            throw new AccessDeniedHttpException();
        }
    }
}
