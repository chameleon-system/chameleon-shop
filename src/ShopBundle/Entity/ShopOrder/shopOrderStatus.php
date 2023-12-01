<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderStatusCode;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderStatusItem;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopOrderStatus {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Belongs to order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldDateTimeNow
/** @var \DateTime|null - Date */
private ?\DateTime $StatusDate = new \DateTime(), 
    // TCMSFieldLookup
/** @var shopOrderStatusCode|null - Status code */
private ?shopOrderStatusCode $ShopOrderStatusCode = null
, 
    // TCMSFieldBlob
/** @var object|null - Data */
private ?object $Data = null, 
    // TCMSFieldWYSIWYG
/** @var string - Additional info */
private string $Info = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderStatusItem> - Order status items */
private Collection $ShopOrderStatusItemCollection = new ArrayCollection()
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
public function getShopOrder(): ?shopOrder
{
    return $this->ShopOrder;
}

public function setShopOrder(?shopOrder $ShopOrder): self
{
    $this->ShopOrder = $ShopOrder;

    return $this;
}


  
    // TCMSFieldDateTimeNow
public function getstatusDate(): ?\DateTime
{
    return $this->StatusDate;
}
public function setstatusDate(?\DateTime $StatusDate): self
{
    $this->StatusDate = $StatusDate;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopOrderStatusCode(): ?shopOrderStatusCode
{
    return $this->ShopOrderStatusCode;
}

public function setShopOrderStatusCode(?shopOrderStatusCode $ShopOrderStatusCode): self
{
    $this->ShopOrderStatusCode = $ShopOrderStatusCode;

    return $this;
}


  
    // TCMSFieldBlob
public function getdata(): ?object
{
    return $this->Data;
}
public function setdata(?object $Data): self
{
    $this->Data = $Data;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getinfo(): string
{
    return $this->Info;
}
public function setinfo(string $Info): self
{
    $this->Info = $Info;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderStatusItem>
*/
public function getShopOrderStatusItemCollection(): Collection
{
    return $this->ShopOrderStatusItemCollection;
}

public function addShopOrderStatusItemCollection(shopOrderStatusItem $ShopOrderStatusItem): self
{
    if (!$this->ShopOrderStatusItemCollection->contains($ShopOrderStatusItem)) {
        $this->ShopOrderStatusItemCollection->add($ShopOrderStatusItem);
        $ShopOrderStatusItem->setShopOrderStatus($this);
    }

    return $this;
}

public function removeShopOrderStatusItemCollection(shopOrderStatusItem $ShopOrderStatusItem): self
{
    if ($this->ShopOrderStatusItemCollection->removeElement($ShopOrderStatusItem)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderStatusItem->getShopOrderStatus() === $this) {
            $ShopOrderStatusItem->setShopOrderStatus(null);
        }
    }

    return $this;
}


  
}
