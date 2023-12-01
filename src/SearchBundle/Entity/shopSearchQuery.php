<?php
namespace ChameleonSystem\SearchBundle\Entity;


class shopSearchQuery {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name / title of query */
private string $Name = '', 
    // TCMSFieldText
/** @var string - Query */
private string $Query = '', 
    // TCMSFieldBoolean
/** @var bool - Index is running */
private bool $IndexRunning = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Index started on */
private ?\DateTime $IndexStarted = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Index completed on */
private ?\DateTime $IndexCompleted = null  ) {}

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
    // TCMSFieldVarchar
public function getname(): string
{
    return $this->Name;
}
public function setname(string $Name): self
{
    $this->Name = $Name;

    return $this;
}


  
    // TCMSFieldText
public function getquery(): string
{
    return $this->Query;
}
public function setquery(string $Query): self
{
    $this->Query = $Query;

    return $this;
}


  
    // TCMSFieldBoolean
public function isindexRunning(): bool
{
    return $this->IndexRunning;
}
public function setindexRunning(bool $IndexRunning): self
{
    $this->IndexRunning = $IndexRunning;

    return $this;
}


  
    // TCMSFieldDateTime
public function getindexStarted(): ?\DateTime
{
    return $this->IndexStarted;
}
public function setindexStarted(?\DateTime $IndexStarted): self
{
    $this->IndexStarted = $IndexStarted;

    return $this;
}


  
    // TCMSFieldDateTime
public function getindexCompleted(): ?\DateTime
{
    return $this->IndexCompleted;
}
public function setindexCompleted(?\DateTime $IndexCompleted): self
{
    $this->IndexCompleted = $IndexCompleted;

    return $this;
}


  
}
