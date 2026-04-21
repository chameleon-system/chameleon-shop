<?php

declare(strict_types=1);

namespace ChameleonSystem\EcommerceStatsBundle\Library\DataModel;

readonly class StatisticEvaluationRequestDataModel
{
    /**
     * @param array<string, mixed> $customFilters
     */
    public function __construct(
        private \DateTime $startDate,
        private \DateTime $endDate,
        private string $dateGroup,
        private bool $showDiffColumn,
        private string $portalId = '',
        private string $currencyId = '',
        private string $selectedStatsGroupSystemName = '',
        private string $shopId = '',
        private array $customFilters = []
    ) {
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function getDateGroup(): string
    {
        return $this->dateGroup;
    }

    public function isShowDiffColumn(): bool
    {
        return $this->showDiffColumn;
    }

    public function getPortalId(): string
    {
        return $this->portalId;
    }

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function getSelectedStatsGroupSystemName(): string
    {
        return $this->selectedStatsGroupSystemName;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomFilters(): array
    {
        return $this->customFilters;
    }
}
