<?php
namespace ChameleonSystem\ShopPaymentIPNBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerGroup;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnMessageTrigger;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnStatus;

class pkgShopPaymentIpnTrigger {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopPaymentHandlerGroup|null - Belongs to payment provider */
private ?shopPaymentHandlerGroup $ShopPaymentHandlerGroup = null
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentIpnMessageTrigger> -  */
private Collection $PkgShopPaymentIpnMessageTriggerCollection = new ArrayCollection()
, 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldURL
/** @var string - Target URL */
private string $TargetUrl = '', 
    // TCMSFieldNumber
/** @var int - Timeout */
private int $TimeoutSeconds = 30, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, pkgShopPaymentIpnStatus> - Status codes to be forwarded */
private Collection $PkgShopPaymentIpnStatusCollection = new ArrayCollection()
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
public function getShopPaymentHandlerGroup(): ?shopPaymentHandlerGroup
{
    return $this->ShopPaymentHandlerGroup;
}

public function setShopPaymentHandlerGroup(?shopPaymentHandlerGroup $ShopPaymentHandlerGroup): self
{
    $this->ShopPaymentHandlerGroup = $ShopPaymentHandlerGroup;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentIpnMessageTrigger>
*/
public function getPkgShopPaymentIpnMessageTriggerCollection(): Collection
{
    return $this->PkgShopPaymentIpnMessageTriggerCollection;
}

public function addPkgShopPaymentIpnMessageTriggerCollection(pkgShopPaymentIpnMessageTrigger $PkgShopPaymentIpnMessageTrigger): self
{
    if (!$this->PkgShopPaymentIpnMessageTriggerCollection->contains($PkgShopPaymentIpnMessageTrigger)) {
        $this->PkgShopPaymentIpnMessageTriggerCollection->add($PkgShopPaymentIpnMessageTrigger);
        $PkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnTrigger($this);
    }

    return $this;
}

public function removePkgShopPaymentIpnMessageTriggerCollection(pkgShopPaymentIpnMessageTrigger $PkgShopPaymentIpnMessageTrigger): self
{
    if ($this->PkgShopPaymentIpnMessageTriggerCollection->removeElement($PkgShopPaymentIpnMessageTrigger)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentIpnMessageTrigger->getPkgShopPaymentIpnTrigger() === $this) {
            $PkgShopPaymentIpnMessageTrigger->setPkgShopPaymentIpnTrigger(null);
        }
    }

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


  
    // TCMSFieldURL
public function gettargetUrl(): string
{
    return $this->TargetUrl;
}
public function settargetUrl(string $TargetUrl): self
{
    $this->TargetUrl = $TargetUrl;

    return $this;
}


  
    // TCMSFieldNumber
public function gettimeoutSeconds(): int
{
    return $this->TimeoutSeconds;
}
public function settimeoutSeconds(int $TimeoutSeconds): self
{
    $this->TimeoutSeconds = $TimeoutSeconds;

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, pkgShopPaymentIpnStatus>
*/
public function getPkgShopPaymentIpnStatusCollection(): Collection
{
    return $this->PkgShopPaymentIpnStatusCollection;
}

public function addPkgShopPaymentIpnStatusCollection(pkgShopPaymentIpnStatus $PkgShopPaymentIpnStatusMlt): self
{
    if (!$this->PkgShopPaymentIpnStatusCollection->contains($PkgShopPaymentIpnStatusMlt)) {
        $this->PkgShopPaymentIpnStatusCollection->add($PkgShopPaymentIpnStatusMlt);
        $PkgShopPaymentIpnStatusMlt->set($this);
    }

    return $this;
}

public function removePkgShopPaymentIpnStatusCollection(pkgShopPaymentIpnStatus $PkgShopPaymentIpnStatusMlt): self
{
    if ($this->PkgShopPaymentIpnStatusCollection->removeElement($PkgShopPaymentIpnStatusMlt)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentIpnStatusMlt->get() === $this) {
            $PkgShopPaymentIpnStatusMlt->set(null);
        }
    }

    return $this;
}


  
}
