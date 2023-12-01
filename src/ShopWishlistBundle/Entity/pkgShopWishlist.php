<?php
namespace ChameleonSystem\ShopWishlistBundle\Entity;

use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ShopWishlistBundle\Entity\pkgShopWishlistArticle;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopWishlistBundle\Entity\pkgShopWishlistMailHistory;

class pkgShopWishlist {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var dataExtranetUser|null - Belongs to user */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldText
/** @var string - Description stored by the user */
private string $Description = '', 
    // TCMSFieldBoolean
/** @var bool - Public */
private bool $IsPublic = false, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopWishlistArticle> - Wishlist articles */
private Collection $PkgShopWishlistArticleCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopWishlistMailHistory> - Wishlist mail history */
private Collection $PkgShopWishlistMailHistoryCollection = new ArrayCollection()
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
public function getDataExtranetUser(): ?dataExtranetUser
{
    return $this->DataExtranetUser;
}

public function setDataExtranetUser(?dataExtranetUser $DataExtranetUser): self
{
    $this->DataExtranetUser = $DataExtranetUser;

    return $this;
}


  
    // TCMSFieldText
public function getdescription(): string
{
    return $this->Description;
}
public function setdescription(string $Description): self
{
    $this->Description = $Description;

    return $this;
}


  
    // TCMSFieldBoolean
public function isisPublic(): bool
{
    return $this->IsPublic;
}
public function setisPublic(bool $IsPublic): self
{
    $this->IsPublic = $IsPublic;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopWishlistArticle>
*/
public function getPkgShopWishlistArticleCollection(): Collection
{
    return $this->PkgShopWishlistArticleCollection;
}

public function addPkgShopWishlistArticleCollection(pkgShopWishlistArticle $PkgShopWishlistArticle): self
{
    if (!$this->PkgShopWishlistArticleCollection->contains($PkgShopWishlistArticle)) {
        $this->PkgShopWishlistArticleCollection->add($PkgShopWishlistArticle);
        $PkgShopWishlistArticle->setPkgShopWishlist($this);
    }

    return $this;
}

public function removePkgShopWishlistArticleCollection(pkgShopWishlistArticle $PkgShopWishlistArticle): self
{
    if ($this->PkgShopWishlistArticleCollection->removeElement($PkgShopWishlistArticle)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopWishlistArticle->getPkgShopWishlist() === $this) {
            $PkgShopWishlistArticle->setPkgShopWishlist(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopWishlistMailHistory>
*/
public function getPkgShopWishlistMailHistoryCollection(): Collection
{
    return $this->PkgShopWishlistMailHistoryCollection;
}

public function addPkgShopWishlistMailHistoryCollection(pkgShopWishlistMailHistory $PkgShopWishlistMailHistory): self
{
    if (!$this->PkgShopWishlistMailHistoryCollection->contains($PkgShopWishlistMailHistory)) {
        $this->PkgShopWishlistMailHistoryCollection->add($PkgShopWishlistMailHistory);
        $PkgShopWishlistMailHistory->setPkgShopWishlist($this);
    }

    return $this;
}

public function removePkgShopWishlistMailHistoryCollection(pkgShopWishlistMailHistory $PkgShopWishlistMailHistory): self
{
    if ($this->PkgShopWishlistMailHistoryCollection->removeElement($PkgShopWishlistMailHistory)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopWishlistMailHistory->getPkgShopWishlist() === $this) {
            $PkgShopWishlistMailHistory->setPkgShopWishlist(null);
        }
    }

    return $this;
}


  
}
