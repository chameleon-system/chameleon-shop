<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\ShopBundle\Entity\ShopVoucher\shopVoucherSeries;
use ChameleonSystem\ShopBundle\Entity\ShopVoucher\shopVoucherUse;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopVoucher {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopVoucherSeries|null - Belongs to voucher series */
private ?shopVoucherSeries $ShopVoucherSeries = null
, 
    // TCMSFieldVarchar
/** @var string - Code */
private string $Code = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Used up on */
private ?\DateTime $DateUsedUp = null, 
    // TCMSFieldBoolean
/** @var bool - Is used up */
private bool $IsUsedUp = false, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopVoucherUse> - Voucher usages */
private Collection $ShopVoucherUseCollection = new ArrayCollection()
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
public function getShopVoucherSeries(): ?shopVoucherSeries
{
    return $this->ShopVoucherSeries;
}

public function setShopVoucherSeries(?shopVoucherSeries $ShopVoucherSeries): self
{
    $this->ShopVoucherSeries = $ShopVoucherSeries;

    return $this;
}


  
    // TCMSFieldVarchar
public function getcode(): string
{
    return $this->Code;
}
public function setcode(string $Code): self
{
    $this->Code = $Code;

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


  
    // TCMSFieldDateTime
public function getdateUsedUp(): ?\DateTime
{
    return $this->DateUsedUp;
}
public function setdateUsedUp(?\DateTime $DateUsedUp): self
{
    $this->DateUsedUp = $DateUsedUp;

    return $this;
}


  
    // TCMSFieldBoolean
public function isisUsedUp(): bool
{
    return $this->IsUsedUp;
}
public function setisUsedUp(bool $IsUsedUp): self
{
    $this->IsUsedUp = $IsUsedUp;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopVoucherUse>
*/
public function getShopVoucherUseCollection(): Collection
{
    return $this->ShopVoucherUseCollection;
}

public function addShopVoucherUseCollection(shopVoucherUse $ShopVoucherUse): self
{
    if (!$this->ShopVoucherUseCollection->contains($ShopVoucherUse)) {
        $this->ShopVoucherUseCollection->add($ShopVoucherUse);
        $ShopVoucherUse->setShopVoucher($this);
    }

    return $this;
}

public function removeShopVoucherUseCollection(shopVoucherUse $ShopVoucherUse): self
{
    if ($this->ShopVoucherUseCollection->removeElement($ShopVoucherUse)) {
        // set the owning side to null (unless already changed)
        if ($ShopVoucherUse->getShopVoucher() === $this) {
            $ShopVoucherUse->setShopVoucher(null);
        }
    }

    return $this;
}


  
}
