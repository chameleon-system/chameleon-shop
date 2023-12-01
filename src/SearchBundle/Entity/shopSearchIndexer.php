<?php
namespace ChameleonSystem\SearchBundle\Entity;


class shopSearchIndexer {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldDateTime
/** @var \DateTime|null - Started on */
private ?\DateTime $Started = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Completed */
private ?\DateTime $Completed = null, 
    // TCMSFieldNumber
/** @var int - Number of lines to process */
private int $TotalRowsToProcess = 0, 
    // TCMSFieldText
/** @var string - Data */
private string $Processdata = ''  ) {}

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
public function getstarted(): ?\DateTime
{
    return $this->Started;
}
public function setstarted(?\DateTime $Started): self
{
    $this->Started = $Started;

    return $this;
}


  
    // TCMSFieldDateTime
public function getcompleted(): ?\DateTime
{
    return $this->Completed;
}
public function setcompleted(?\DateTime $Completed): self
{
    $this->Completed = $Completed;

    return $this;
}


  
    // TCMSFieldNumber
public function gettotalRowsToProcess(): int
{
    return $this->TotalRowsToProcess;
}
public function settotalRowsToProcess(int $TotalRowsToProcess): self
{
    $this->TotalRowsToProcess = $TotalRowsToProcess;

    return $this;
}


  
    // TCMSFieldText
public function getprocessdata(): string
{
    return $this->Processdata;
}
public function setprocessdata(string $Processdata): self
{
    $this->Processdata = $Processdata;

    return $this;
}


  
}
