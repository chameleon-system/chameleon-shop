<?php

namespace ChameleonSystem\SearchBundle\DataModel;

class ShopSearchStatusDataModel
{
    private \DateTime $started;
    private \DateTime $completed;
    private int $totalRowsProcessed;

    public function __construct(\DateTime $started, \DateTime $completed, int $totalRowsProcessed)
    {
        $this->started = $started;
        $this->completed = $completed;
        $this->totalRowsProcessed = $totalRowsProcessed;
    }

    public function getStarted(): \DateTime
    {
        return $this->started;
    }

    public function getCompleted(): \DateTime
    {
        return $this->completed;
    }

    public function getTotalRowsProcessed(): int
    {
        return $this->totalRowsProcessed;
    }
}
