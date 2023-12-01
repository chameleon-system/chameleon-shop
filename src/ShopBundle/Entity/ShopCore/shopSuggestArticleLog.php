<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopSuggestArticleLog {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldDateTime
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = null, 
    // TCMSFieldLookupParentID
/** @var dataExtranetUser|null - Shop customer */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldLookup
/** @var shopArticle|null - Product / item */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldEmail
/** @var string - From (email) */
private string $FromEmail = '', 
    // TCMSFieldVarchar
/** @var string - From (name) */
private string $FromName = '', 
    // TCMSFieldEmail
/** @var string - Feedback recipient (email address) */
private string $ToEmail = '', 
    // TCMSFieldVarchar
/** @var string - To (name) */
private string $ToName = '', 
    // TCMSFieldText
/** @var string - Comment */
private string $Comment = ''  ) {}

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
public function getdatecreated(): ?\DateTime
{
    return $this->Datecreated;
}
public function setdatecreated(?\DateTime $Datecreated): self
{
    $this->Datecreated = $Datecreated;

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


  
    // TCMSFieldLookup
public function getShopArticle(): ?shopArticle
{
    return $this->ShopArticle;
}

public function setShopArticle(?shopArticle $ShopArticle): self
{
    $this->ShopArticle = $ShopArticle;

    return $this;
}


  
    // TCMSFieldEmail
public function getfromEmail(): string
{
    return $this->FromEmail;
}
public function setfromEmail(string $FromEmail): self
{
    $this->FromEmail = $FromEmail;

    return $this;
}


  
    // TCMSFieldVarchar
public function getfromName(): string
{
    return $this->FromName;
}
public function setfromName(string $FromName): self
{
    $this->FromName = $FromName;

    return $this;
}


  
    // TCMSFieldEmail
public function gettoEmail(): string
{
    return $this->ToEmail;
}
public function settoEmail(string $ToEmail): self
{
    $this->ToEmail = $ToEmail;

    return $this;
}


  
    // TCMSFieldVarchar
public function gettoName(): string
{
    return $this->ToName;
}
public function settoName(string $ToName): self
{
    $this->ToName = $ToName;

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


  
}
