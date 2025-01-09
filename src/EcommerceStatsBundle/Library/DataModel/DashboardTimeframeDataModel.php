<?php

namespace ChameleonSystem\EcommerceStatsBundle\Library\DataModel;

use DateTime;

class DashboardTimeframeDataModel
{
    private DateTime $startDate;
    private DateTime $endDate;

    public function __construct
    (
        DateTime $startDate,
        DateTime $endDate,
    )
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function setEndDate(DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function setStartDate(DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }
}