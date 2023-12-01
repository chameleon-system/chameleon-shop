<?php
namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\SearchBundle\Entity\shopSearchCacheItem;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopSearchCache {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldVarchar
/** @var string - Search key */
private string $Searchkey = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Last used */
private ?\DateTime $LastUsedDate = null, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopSearchCacheItem> - Results */
private Collection $ShopSearchCacheItemCollection = new ArrayCollection()
, 
    // TCMSFieldText
/** @var string - Category hits */
private string $CategoryHits = '', 
    // TCMSFieldNumber
/** @var int - Number of records found */
private int $NumberOfRecordsFound = -1  ) {}

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
public function getShop(): ?shop
{
    return $this->Shop;
}

public function setShop(?shop $Shop): self
{
    $this->Shop = $Shop;

    return $this;
}


  
    // TCMSFieldVarchar
public function getsearchkey(): string
{
    return $this->Searchkey;
}
public function setsearchkey(string $Searchkey): self
{
    $this->Searchkey = $Searchkey;

    return $this;
}


  
    // TCMSFieldDateTime
public function getlastUsedDate(): ?\DateTime
{
    return $this->LastUsedDate;
}
public function setlastUsedDate(?\DateTime $LastUsedDate): self
{
    $this->LastUsedDate = $LastUsedDate;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopSearchCacheItem>
*/
public function getShopSearchCacheItemCollection(): Collection
{
    return $this->ShopSearchCacheItemCollection;
}

public function addShopSearchCacheItemCollection(shopSearchCacheItem $ShopSearchCacheItem): self
{
    if (!$this->ShopSearchCacheItemCollection->contains($ShopSearchCacheItem)) {
        $this->ShopSearchCacheItemCollection->add($ShopSearchCacheItem);
        $ShopSearchCacheItem->setShopSearchCache($this);
    }

    return $this;
}

public function removeShopSearchCacheItemCollection(shopSearchCacheItem $ShopSearchCacheItem): self
{
    if ($this->ShopSearchCacheItemCollection->removeElement($ShopSearchCacheItem)) {
        // set the owning side to null (unless already changed)
        if ($ShopSearchCacheItem->getShopSearchCache() === $this) {
            $ShopSearchCacheItem->setShopSearchCache(null);
        }
    }

    return $this;
}


  
    // TCMSFieldText
public function getcategoryHits(): string
{
    return $this->CategoryHits;
}
public function setcategoryHits(string $CategoryHits): self
{
    $this->CategoryHits = $CategoryHits;

    return $this;
}


  
    // TCMSFieldNumber
public function getnumberOfRecordsFound(): int
{
    return $this->NumberOfRecordsFound;
}
public function setnumberOfRecordsFound(int $NumberOfRecordsFound): self
{
    $this->NumberOfRecordsFound = $NumberOfRecordsFound;

    return $this;
}


  
}
