<?php
namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;

class pkgShopRatingService {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - System name */
private string $SystemName = '', 
    // TCMSFieldBoolean
/** @var bool - Ratings contain HTML */
private bool $RatingsContainHtml = false, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Icon */
private ?cmsMedia $IconCmsMedia = null
, 
    // TCMSFieldVarchar
/** @var string - Shop URL */
private string $ShopUrl = '', 
    // TCMSFieldVarchar
/** @var string - Rating URL */
private string $RatingUrl = '', 
    // TCMSFieldVarchar
/** @var string - Rating API ID */
private string $RatingApiId = '', 
    // TCMSFieldVarchar
/** @var string - Affiliate value */
private string $AffiliateValue = '', 
    // TCMSFieldText
/** @var string - Email text */
private string $EmailText = '', 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldDecimal
/** @var float - Weighting */
private float $Weight = 0, 
    // TCMSFieldNumber
/** @var int - Frequency of use */
private int $NumberOfTimesUsed = 0, 
    // TCMSFieldNumber
/** @var int - Last used (calender week) */
private int $LastUsedYearWeek = 0, 
    // TCMSFieldBoolean
/** @var bool - Allow import */
private bool $AllowImport = false, 
    // TCMSFieldBoolean
/** @var bool - Allow sending of emails */
private bool $AllowSendingEmails = true, 
    // TCMSFieldDecimal
/** @var float - Current rating */
private float $CurrentRating = 0, 
    // TCMSFieldEmail
/** @var string - Email provider */
private string $ServiceEmail = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Current date of rating */
private ?\DateTime $CurrentRatingDate = null, 
    // TCMSFieldVarchar
/** @var string - Class */
private string $Class = '', 
    // TCMSFieldVarchar
/** @var string - Subtype */
private string $ClassSubtype = '', 
    // TCMSFieldOption
/** @var string - Class type */
private string $ClassType = 'Customer'  ) {}

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
    // TCMSFieldBoolean
public function isactive(): bool
{
    return $this->Active;
}
public function setactive(bool $Active): self
{
    $this->Active = $Active;

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


  
    // TCMSFieldVarchar
public function getsystemName(): string
{
    return $this->SystemName;
}
public function setsystemName(string $SystemName): self
{
    $this->SystemName = $SystemName;

    return $this;
}


  
    // TCMSFieldBoolean
public function isratingsContainHtml(): bool
{
    return $this->RatingsContainHtml;
}
public function setratingsContainHtml(bool $RatingsContainHtml): self
{
    $this->RatingsContainHtml = $RatingsContainHtml;

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getIconCmsMedia(): ?cmsMedia
{
    return $this->IconCmsMedia;
}

public function setIconCmsMedia(?cmsMedia $IconCmsMedia): self
{
    $this->IconCmsMedia = $IconCmsMedia;

    return $this;
}


  
    // TCMSFieldVarchar
public function getshopUrl(): string
{
    return $this->ShopUrl;
}
public function setshopUrl(string $ShopUrl): self
{
    $this->ShopUrl = $ShopUrl;

    return $this;
}


  
    // TCMSFieldVarchar
public function getratingUrl(): string
{
    return $this->RatingUrl;
}
public function setratingUrl(string $RatingUrl): self
{
    $this->RatingUrl = $RatingUrl;

    return $this;
}


  
    // TCMSFieldVarchar
public function getratingApiId(): string
{
    return $this->RatingApiId;
}
public function setratingApiId(string $RatingApiId): self
{
    $this->RatingApiId = $RatingApiId;

    return $this;
}


  
    // TCMSFieldVarchar
public function getaffiliateValue(): string
{
    return $this->AffiliateValue;
}
public function setaffiliateValue(string $AffiliateValue): self
{
    $this->AffiliateValue = $AffiliateValue;

    return $this;
}


  
    // TCMSFieldText
public function getemailText(): string
{
    return $this->EmailText;
}
public function setemailText(string $EmailText): self
{
    $this->EmailText = $EmailText;

    return $this;
}


  
    // TCMSFieldPosition
public function getposition(): int
{
    return $this->Position;
}
public function setposition(int $Position): self
{
    $this->Position = $Position;

    return $this;
}


  
    // TCMSFieldDecimal
public function getweight(): float
{
    return $this->Weight;
}
public function setweight(float $Weight): self
{
    $this->Weight = $Weight;

    return $this;
}


  
    // TCMSFieldNumber
public function getnumberOfTimesUsed(): int
{
    return $this->NumberOfTimesUsed;
}
public function setnumberOfTimesUsed(int $NumberOfTimesUsed): self
{
    $this->NumberOfTimesUsed = $NumberOfTimesUsed;

    return $this;
}


  
    // TCMSFieldNumber
public function getlastUsedYearWeek(): int
{
    return $this->LastUsedYearWeek;
}
public function setlastUsedYearWeek(int $LastUsedYearWeek): self
{
    $this->LastUsedYearWeek = $LastUsedYearWeek;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowImport(): bool
{
    return $this->AllowImport;
}
public function setallowImport(bool $AllowImport): self
{
    $this->AllowImport = $AllowImport;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowSendingEmails(): bool
{
    return $this->AllowSendingEmails;
}
public function setallowSendingEmails(bool $AllowSendingEmails): self
{
    $this->AllowSendingEmails = $AllowSendingEmails;

    return $this;
}


  
    // TCMSFieldDecimal
public function getcurrentRating(): float
{
    return $this->CurrentRating;
}
public function setcurrentRating(float $CurrentRating): self
{
    $this->CurrentRating = $CurrentRating;

    return $this;
}


  
    // TCMSFieldEmail
public function getserviceEmail(): string
{
    return $this->ServiceEmail;
}
public function setserviceEmail(string $ServiceEmail): self
{
    $this->ServiceEmail = $ServiceEmail;

    return $this;
}


  
    // TCMSFieldDateTime
public function getcurrentRatingDate(): ?\DateTime
{
    return $this->CurrentRatingDate;
}
public function setcurrentRatingDate(?\DateTime $CurrentRatingDate): self
{
    $this->CurrentRatingDate = $CurrentRatingDate;

    return $this;
}


  
    // TCMSFieldVarchar
public function getclass(): string
{
    return $this->Class;
}
public function setclass(string $Class): self
{
    $this->Class = $Class;

    return $this;
}


  
    // TCMSFieldVarchar
public function getclassSubtype(): string
{
    return $this->ClassSubtype;
}
public function setclassSubtype(string $ClassSubtype): self
{
    $this->ClassSubtype = $ClassSubtype;

    return $this;
}


  
    // TCMSFieldOption
public function getclassType(): string
{
    return $this->ClassType;
}
public function setclassType(string $ClassType): self
{
    $this->ClassType = $ClassType;

    return $this;
}


  
}
