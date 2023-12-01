<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleImageSize;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;

class shopArticlePreviewImage {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Belongs to article */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldLookup
/** @var shopArticleImageSize|null - Preview image size / type */
private ?shopArticleImageSize $ShopArticleImageSize = null
, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Preview image */
private ?cmsMedia $CmsMedia = null
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


  
    // TCMSFieldLookup
public function getShopArticleImageSize(): ?shopArticleImageSize
{
    return $this->ShopArticleImageSize;
}

public function setShopArticleImageSize(?shopArticleImageSize $ShopArticleImageSize): self
{
    $this->ShopArticleImageSize = $ShopArticleImageSize;

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getCmsMedia(): ?cmsMedia
{
    return $this->CmsMedia;
}

public function setCmsMedia(?cmsMedia $CmsMedia): self
{
    $this->CmsMedia = $CmsMedia;

    return $this;
}


  
}
