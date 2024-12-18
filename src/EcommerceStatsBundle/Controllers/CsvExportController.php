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

use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\CsvExportServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsProviderInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\StatsTableServiceInterface;
use ChameleonSystem\EcommerceStatsBundle\Library\Interfaces\TopSellerServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CsvExportController
{
    private CsvExportServiceInterface $csvExportService;
    private StatsTableServiceInterface $statsTableService;
    private TopSellerServiceInterface $topSellerService;

    public function __construct(
        CsvExportServiceInterface $csvExportService,
        StatsTableServiceInterface $statsTableService,
        TopSellerServiceInterface $topSellerService
    ) {
        $this->csvExportService = $csvExportService;
        $this->statsTableService = $statsTableService;
        $this->topSellerService = $topSellerService;
    }

    public function exportStatistics(Request $request): Response
    {
        $this->throwIfNoBackendUserLoggedIn();

        $dateGroupType = $request->get('dateGroupType', StatsProviderInterface::DATA_GROUP_TYPE_DAY);
        $showChange = $request->request->getBoolean('showChange');
        $selectedPortalId = $request->get('portalId', '');
        $startDate = $this->getRequiredDate($request, 'startDate')->setTime(0, 0, 0);
        $endDate = $this->getRequiredDate($request, 'endDate')->setTime(23, 59, 59);

        $statsTable = $this->statsTableService->evaluate($startDate, $endDate, $dateGroupType, $showChange, $selectedPortalId);
        $csvData = $this->csvExportService->getCsvDataFromStatsTable($statsTable);
        $fileName = $this->getCsvFilename('stats', $startDate, $endDate);

        return CsvResponse::fromRows($fileName, $csvData);
    }

    public function exportTopSellers(Request $request): Response
    {
        $this->throwIfNoBackendUserLoggedIn();

        $startDate = $this->getRequiredDate($request, 'startDate')->setTime(0, 0, 0);
        $endDate = $this->getRequiredDate($request, 'endDate')->setTime(23, 59, 59);
        $selectedPortalId = $request->get('portalId', '');
        $limit = $request->request->getInt('limit', 50);

        $topSellers = $this->topSellerService->getTopsellers($startDate, $endDate, $selectedPortalId, $limit);
        $csvData = $this->csvExportService->getCsvDataFromTopsellers($topSellers);
        $fileName = $this->getCsvFilename('topsellers', $startDate, $endDate);

        return CsvResponse::fromRows($fileName, $csvData);
    }

    private function getRequiredDate(Request $request, string $parameter): \DateTime
    {
        $dateString = $request->get($parameter);
        if (null === $dateString) {
            throw new BadRequestHttpException(sprintf(
                'Request argument `%s` is required.',
                $parameter
            ));
        }

        $dateInstance = \DateTime::createFromFormat('Y-m-d', $dateString);
        if (false === $dateInstance) {
            throw new BadRequestHttpException(sprintf(
                'Date in argument `%s` must be in format `Y-m-d` - `%s` is not.',
                $parameter,
                $dateString
            ));
        }

        return $dateInstance;
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
        $user = \TCMSUser::GetActiveUser();

        if (null !== $user && null !== $user->id) {
            return;
        }

        throw new AccessDeniedHttpException();
    }
}
