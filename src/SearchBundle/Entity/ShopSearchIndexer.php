<?php

namespace ChameleonSystem\SearchBundle\Entity;


use DateTime;

class ShopSearchIndexer
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldDateTime
        /** @var DateTime|null - Started on */
        private ?DateTime $started = null,
        // TCMSFieldDateTime
        /** @var DateTime|null - Completed */
        private ?DateTime $completed = null,
        // TCMSFieldNumber
        /** @var int - Number of lines to process */
        private int $totalRowsToProcess = 0,
        // TCMSFieldText
        /** @var string - Data */
        private string $processdata = ''
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCmsident(): ?int
    {
        return $this->cmsident;
    }

    public function setCmsident(int $cmsident): self
    {
        $this->cmsident = $cmsident;

        return $this;
    }

    // TCMSFieldDateTime
    public function getStarted(): ?DateTime
    {
        return $this->started;
    }

    public function setStarted(?DateTime $started): self
    {
        $this->started = $started;

        return $this;
    }


    // TCMSFieldDateTime
    public function getCompleted(): ?DateTime
    {
        return $this->completed;
    }

    public function setCompleted(?DateTime $completed): self
    {
        $this->completed = $completed;

        return $this;
    }


    // TCMSFieldNumber
    public function getTotalRowsToProcess(): int
    {
        return $this->totalRowsToProcess;
    }

    public function setTotalRowsToProcess(int $totalRowsToProcess): self
    {
        $this->totalRowsToProcess = $totalRowsToProcess;

        return $this;
    }


    // TCMSFieldText
    public function getProcessdata(): string
    {
        return $this->processdata;
    }

    public function setProcessdata(string $processdata): self
    {
        $this->processdata = $processdata;

        return $this;
    }


}
