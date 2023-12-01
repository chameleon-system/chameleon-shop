<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;

class pkgShopArticlePreorder {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldExtendedLookup
/** @var shopArticle|null - Preordered product */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldEmail
/** @var string - Email address */
private string $PreorderUserEmail = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Date */
private ?\DateTime $PreorderDate = null, 
    // TCMSFieldLookup
/** @var cmsPortal|null - Belongs to portal */
private ?cmsPortal $CmsPortal = null
  ) {}

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
    // TCMSFieldExtendedLookup
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
public function getpreorderUserEmail(): string
{
    return $this->PreorderUserEmail;
}
public function setpreorderUserEmail(string $PreorderUserEmail): self
{
    $this->PreorderUserEmail = $PreorderUserEmail;

    return $this;
}


  
    // TCMSFieldDateTime
public function getpreorderDate(): ?\DateTime
{
    return $this->PreorderDate;
}
public function setpreorderDate(?\DateTime $PreorderDate): self
{
    $this->PreorderDate = $PreorderDate;

    return $this;
}


  
    // TCMSFieldLookup
public function getCmsPortal(): ?cmsPortal
{
    return $this->CmsPortal;
}

public function setCmsPortal(?cmsPortal $CmsPortal): self
{
    $this->CmsPortal = $CmsPortal;

    return $this;
}


  
}
