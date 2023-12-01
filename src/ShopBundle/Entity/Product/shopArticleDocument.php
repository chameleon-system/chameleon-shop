<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleDocumentType;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsDocument;

class shopArticleDocument {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Belongs to article */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldLookup
/** @var shopArticleDocumentType|null - Article document type */
private ?shopArticleDocumentType $ShopArticleDocumentType = null
, 
    // TCMSFieldExtendedLookup
/** @var cmsDocument|null - Document */
private ?cmsDocument $CmsDocument = null
, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0  ) {}

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
public function getShopArticleDocumentType(): ?shopArticleDocumentType
{
    return $this->ShopArticleDocumentType;
}

public function setShopArticleDocumentType(?shopArticleDocumentType $ShopArticleDocumentType): self
{
    $this->ShopArticleDocumentType = $ShopArticleDocumentType;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getCmsDocument(): ?cmsDocument
{
    return $this->CmsDocument;
}

public function setCmsDocument(?cmsDocument $CmsDocument): self
{
    $this->CmsDocument = $CmsDocument;

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


  
}
