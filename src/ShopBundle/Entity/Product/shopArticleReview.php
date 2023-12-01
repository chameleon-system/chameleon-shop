<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;

class shopArticleReview {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Belongs to product */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldLookupParentID
/** @var dataExtranetUser|null - Written by */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldBoolean
/** @var bool - Published */
private bool $Publish = false, 
    // TCMSFieldVarchar
/** @var string - Author */
private string $AuthorName = '', 
    // TCMSFieldVarchar
/** @var string - Review title */
private string $Title = '', 
    // TCMSFieldEmail
/** @var string - Author's email address */
private string $AuthorEmail = '', 
    // TCMSFieldBoolean
/** @var bool - Send comment notification to the author */
private bool $SendCommentNotification = false, 
    // TCMSFieldNumber
/** @var int - Rating */
private int $Rating = 0, 
    // TCMSFieldNumber
/** @var int - Helpful review */
private int $HelpfulCount = 0, 
    // TCMSFieldNumber
/** @var int - Review is not helpful */
private int $NotHelpfulCount = 0, 
    // TCMSFieldVarchar
/** @var string - Action ID */
private string $ActionId = '', 
    // TCMSFieldText
/** @var string - Review */
private string $Comment = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = null, 
    // TCMSFieldVarchar
/** @var string - IP address */
private string $UserIp = ''  ) {}

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
    // TCMSFieldLookupParentID
public function getShopArticle(): ?shopArticle
{
    return $this->ShopArticle;
}

public function setShopArticle(?shopArticle $ShopArticle): self
{
    $this->ShopArticle = $ShopArticle;

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getDataExtranetUser(): ?dataExtranetUser
{
    return $this->DataExtranetUser;
}

public function setDataExtranetUser(?dataExtranetUser $DataExtranetUser): self
{
    $this->DataExtranetUser = $DataExtranetUser;

    return $this;
}


  
    // TCMSFieldBoolean
public function ispublish(): bool
{
    return $this->Publish;
}
public function setpublish(bool $Publish): self
{
    $this->Publish = $Publish;

    return $this;
}


  
    // TCMSFieldVarchar
public function getauthorName(): string
{
    return $this->AuthorName;
}
public function setauthorName(string $AuthorName): self
{
    $this->AuthorName = $AuthorName;

    return $this;
}


  
    // TCMSFieldVarchar
public function gettitle(): string
{
    return $this->Title;
}
public function settitle(string $Title): self
{
    $this->Title = $Title;

    return $this;
}


  
    // TCMSFieldEmail
public function getauthorEmail(): string
{
    return $this->AuthorEmail;
}
public function setauthorEmail(string $AuthorEmail): self
{
    $this->AuthorEmail = $AuthorEmail;

    return $this;
}


  
    // TCMSFieldBoolean
public function issendCommentNotification(): bool
{
    return $this->SendCommentNotification;
}
public function setsendCommentNotification(bool $SendCommentNotification): self
{
    $this->SendCommentNotification = $SendCommentNotification;

    return $this;
}


  
    // TCMSFieldNumber
public function getrating(): int
{
    return $this->Rating;
}
public function setrating(int $Rating): self
{
    $this->Rating = $Rating;

    return $this;
}


  
    // TCMSFieldNumber
public function gethelpfulCount(): int
{
    return $this->HelpfulCount;
}
public function sethelpfulCount(int $HelpfulCount): self
{
    $this->HelpfulCount = $HelpfulCount;

    return $this;
}


  
    // TCMSFieldNumber
public function getnotHelpfulCount(): int
{
    return $this->NotHelpfulCount;
}
public function setnotHelpfulCount(int $NotHelpfulCount): self
{
    $this->NotHelpfulCount = $NotHelpfulCount;

    return $this;
}


  
    // TCMSFieldVarchar
public function getactionId(): string
{
    return $this->ActionId;
}
public function setactionId(string $ActionId): self
{
    $this->ActionId = $ActionId;

    return $this;
}


  
    // TCMSFieldText
public function getcomment(): string
{
    return $this->Comment;
}
public function setcomment(string $Comment): self
{
    $this->Comment = $Comment;

    return $this;
}


  
    // TCMSFieldDateTime
public function getdatecreated(): ?\DateTime
{
    return $this->Datecreated;
}
public function setdatecreated(?\DateTime $Datecreated): self
{
    $this->Datecreated = $Datecreated;

    return $this;
}


  
    // TCMSFieldVarchar
public function getuserIp(): string
{
    return $this->UserIp;
}
public function setuserIp(string $UserIp): self
{
    $this->UserIp = $UserIp;

    return $this;
}


  
}
