<?php
namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\ShopRatingServiceBundle\Entity\pkgShopRatingService;

class pkgShopRatingServiceRating {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookup
/** @var pkgShopRatingService|null - Rating service */
private ?pkgShopRatingService $PkgShopRatingService = null
, 
    // TCMSFieldVarchar
/** @var string - Remote key */
private string $RemoteKey = '', 
    // TCMSFieldDecimal
/** @var float - Rating */
private float $Score = 0, 
    // TCMSFieldText
/** @var string - Raw data */
private string $Rawdata = '', 
    // TCMSFieldVarchar
/** @var string - User who rates */
private string $RatingUser = '', 
    // TCMSFieldText
/** @var string - Rating text */
private string $RatingText = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Date of rating */
private ?\DateTime $RatingDate = null  ) {}

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
    // TCMSFieldLookup
public function getPkgShopRatingService(): ?pkgShopRatingService
{
    return $this->PkgShopRatingService;
}

public function setPkgShopRatingService(?pkgShopRatingService $PkgShopRatingService): self
{
    $this->PkgShopRatingService = $PkgShopRatingService;

    return $this;
}


  
    // TCMSFieldVarchar
public function getremoteKey(): string
{
    return $this->RemoteKey;
}
public function setremoteKey(string $RemoteKey): self
{
    $this->RemoteKey = $RemoteKey;

    return $this;
}


  
    // TCMSFieldDecimal
public function getscore(): float
{
    return $this->Score;
}
public function setscore(float $Score): self
{
    $this->Score = $Score;

    return $this;
}


  
    // TCMSFieldText
public function getrawdata(): string
{
    return $this->Rawdata;
}
public function setrawdata(string $Rawdata): self
{
    $this->Rawdata = $Rawdata;

    return $this;
}


  
    // TCMSFieldVarchar
public function getratingUser(): string
{
    return $this->RatingUser;
}
public function setratingUser(string $RatingUser): self
{
    $this->RatingUser = $RatingUser;

    return $this;
}


  
    // TCMSFieldText
public function getratingText(): string
{
    return $this->RatingText;
}
public function setratingText(string $RatingText): self
{
    $this->RatingText = $RatingText;

    return $this;
}


  
    // TCMSFieldDateTime
public function getratingDate(): ?\DateTime
{
    return $this->RatingDate;
}
public function setratingDate(?\DateTime $RatingDate): self
{
    $this->RatingDate = $RatingDate;

    return $this;
}


  
}
