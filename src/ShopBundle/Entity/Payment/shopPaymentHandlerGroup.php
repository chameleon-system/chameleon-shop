<?php
namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerGroupConfig;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnStatus;
use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandler;
use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentMethod;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnTrigger;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnMessage;

class shopPaymentHandlerGroup {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Overwrite Tdb with this class */
private string $Classname = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopPaymentHandlerGroupConfig> - Configuration */
private Collection $ShopPaymentHandlerGroupConfigCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - IPN Identifier */
private string $IpnGroupIdentifier = '', 
    // TCMSFieldVarchar
/** @var string - Character encoding of data transmitted by the provider */
private string $IpnPayloadCharacterCharset = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentIpnStatus> - IPN status codes */
private Collection $PkgShopPaymentIpnStatusCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - System name */
private string $SystemName = '', 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopPaymentHandler> - Payment handler */
private Collection $ShopPaymentHandlerCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopPaymentMethod> - Payment methods */
private Collection $ShopPaymentMethodCollection = new ArrayCollection()
, 
    // TCMSFieldText
/** @var string - IPN may come from the following IP */
private string $IpnAllowedIps = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentIpnTrigger> - Redirections */
private Collection $PkgShopPaymentIpnTriggerCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentIpnMessage> - IPN messages */
private Collection $PkgShopPaymentIpnMessageCollection = new ArrayCollection()
, 
    // TCMSFieldOption
/** @var string - Environment */
private string $Environment = 'default'  ) {}

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
    // TCMSFieldVarchar
public function getclassname(): string
{
    return $this->Classname;
}
public function setclassname(string $Classname): self
{
    $this->Classname = $Classname;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopPaymentHandlerGroupConfig>
*/
public function getShopPaymentHandlerGroupConfigCollection(): Collection
{
    return $this->ShopPaymentHandlerGroupConfigCollection;
}

public function addShopPaymentHandlerGroupConfigCollection(shopPaymentHandlerGroupConfig $ShopPaymentHandlerGroupConfig): self
{
    if (!$this->ShopPaymentHandlerGroupConfigCollection->contains($ShopPaymentHandlerGroupConfig)) {
        $this->ShopPaymentHandlerGroupConfigCollection->add($ShopPaymentHandlerGroupConfig);
        $ShopPaymentHandlerGroupConfig->setShopPaymentHandlerGroup($this);
    }

    return $this;
}

public function removeShopPaymentHandlerGroupConfigCollection(shopPaymentHandlerGroupConfig $ShopPaymentHandlerGroupConfig): self
{
    if ($this->ShopPaymentHandlerGroupConfigCollection->removeElement($ShopPaymentHandlerGroupConfig)) {
        // set the owning side to null (unless already changed)
        if ($ShopPaymentHandlerGroupConfig->getShopPaymentHandlerGroup() === $this) {
            $ShopPaymentHandlerGroupConfig->setShopPaymentHandlerGroup(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getipnGroupIdentifier(): string
{
    return $this->IpnGroupIdentifier;
}
public function setipnGroupIdentifier(string $IpnGroupIdentifier): self
{
    $this->IpnGroupIdentifier = $IpnGroupIdentifier;

    return $this;
}


  
    // TCMSFieldVarchar
public function getipnPayloadCharacterCharset(): string
{
    return $this->IpnPayloadCharacterCharset;
}
public function setipnPayloadCharacterCharset(string $IpnPayloadCharacterCharset): self
{
    $this->IpnPayloadCharacterCharset = $IpnPayloadCharacterCharset;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentIpnStatus>
*/
public function getPkgShopPaymentIpnStatusCollection(): Collection
{
    return $this->PkgShopPaymentIpnStatusCollection;
}

public function addPkgShopPaymentIpnStatusCollection(pkgShopPaymentIpnStatus $PkgShopPaymentIpnStatus): self
{
    if (!$this->PkgShopPaymentIpnStatusCollection->contains($PkgShopPaymentIpnStatus)) {
        $this->PkgShopPaymentIpnStatusCollection->add($PkgShopPaymentIpnStatus);
        $PkgShopPaymentIpnStatus->setShopPaymentHandlerGroup($this);
    }

    return $this;
}

public function removePkgShopPaymentIpnStatusCollection(pkgShopPaymentIpnStatus $PkgShopPaymentIpnStatus): self
{
    if ($this->PkgShopPaymentIpnStatusCollection->removeElement($PkgShopPaymentIpnStatus)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentIpnStatus->getShopPaymentHandlerGroup() === $this) {
            $PkgShopPaymentIpnStatus->setShopPaymentHandlerGroup(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getname(): string
{
    return $this->Name;
}
public function setname(string $Name): self
{
    $this->Name = $Name;

    return $this;
}


  
    // TCMSFieldVarchar
public function getsystemName(): string
{
    return $this->SystemName;
}
public function setsystemName(string $SystemName): self
{
    $this->SystemName = $SystemName;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescription(): string
{
    return $this->Description;
}
public function setdescription(string $Description): self
{
    $this->Description = $Description;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopPaymentHandler>
*/
public function getShopPaymentHandlerCollection(): Collection
{
    return $this->ShopPaymentHandlerCollection;
}

public function addShopPaymentHandlerCollection(shopPaymentHandler $ShopPaymentHandler): self
{
    if (!$this->ShopPaymentHandlerCollection->contains($ShopPaymentHandler)) {
        $this->ShopPaymentHandlerCollection->add($ShopPaymentHandler);
        $ShopPaymentHandler->setShopPaymentHandlerGroup($this);
    }

    return $this;
}

public function removeShopPaymentHandlerCollection(shopPaymentHandler $ShopPaymentHandler): self
{
    if ($this->ShopPaymentHandlerCollection->removeElement($ShopPaymentHandler)) {
        // set the owning side to null (unless already changed)
        if ($ShopPaymentHandler->getShopPaymentHandlerGroup() === $this) {
            $ShopPaymentHandler->setShopPaymentHandlerGroup(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopPaymentMethod>
*/
public function getShopPaymentMethodCollection(): Collection
{
    return $this->ShopPaymentMethodCollection;
}

public function addShopPaymentMethodCollection(shopPaymentMethod $ShopPaymentMethod): self
{
    if (!$this->ShopPaymentMethodCollection->contains($ShopPaymentMethod)) {
        $this->ShopPaymentMethodCollection->add($ShopPaymentMethod);
        $ShopPaymentMethod->setShopPaymentHandlerGroup($this);
    }

    return $this;
}

public function removeShopPaymentMethodCollection(shopPaymentMethod $ShopPaymentMethod): self
{
    if ($this->ShopPaymentMethodCollection->removeElement($ShopPaymentMethod)) {
        // set the owning side to null (unless already changed)
        if ($ShopPaymentMethod->getShopPaymentHandlerGroup() === $this) {
            $ShopPaymentMethod->setShopPaymentHandlerGroup(null);
        }
    }

    return $this;
}


  
    // TCMSFieldText
public function getipnAllowedIps(): string
{
    return $this->IpnAllowedIps;
}
public function setipnAllowedIps(string $IpnAllowedIps): self
{
    $this->IpnAllowedIps = $IpnAllowedIps;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentIpnTrigger>
*/
public function getPkgShopPaymentIpnTriggerCollection(): Collection
{
    return $this->PkgShopPaymentIpnTriggerCollection;
}

public function addPkgShopPaymentIpnTriggerCollection(pkgShopPaymentIpnTrigger $PkgShopPaymentIpnTrigger): self
{
    if (!$this->PkgShopPaymentIpnTriggerCollection->contains($PkgShopPaymentIpnTrigger)) {
        $this->PkgShopPaymentIpnTriggerCollection->add($PkgShopPaymentIpnTrigger);
        $PkgShopPaymentIpnTrigger->setShopPaymentHandlerGroup($this);
    }

    return $this;
}

public function removePkgShopPaymentIpnTriggerCollection(pkgShopPaymentIpnTrigger $PkgShopPaymentIpnTrigger): self
{
    if ($this->PkgShopPaymentIpnTriggerCollection->removeElement($PkgShopPaymentIpnTrigger)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentIpnTrigger->getShopPaymentHandlerGroup() === $this) {
            $PkgShopPaymentIpnTrigger->setShopPaymentHandlerGroup(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentIpnMessage>
*/
public function getPkgShopPaymentIpnMessageCollection(): Collection
{
    return $this->PkgShopPaymentIpnMessageCollection;
}

public function addPkgShopPaymentIpnMessageCollection(pkgShopPaymentIpnMessage $PkgShopPaymentIpnMessage): self
{
    if (!$this->PkgShopPaymentIpnMessageCollection->contains($PkgShopPaymentIpnMessage)) {
        $this->PkgShopPaymentIpnMessageCollection->add($PkgShopPaymentIpnMessage);
        $PkgShopPaymentIpnMessage->setShopPaymentHandlerGroup($this);
    }

    return $this;
}

public function removePkgShopPaymentIpnMessageCollection(pkgShopPaymentIpnMessage $PkgShopPaymentIpnMessage): self
{
    if ($this->PkgShopPaymentIpnMessageCollection->removeElement($PkgShopPaymentIpnMessage)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentIpnMessage->getShopPaymentHandlerGroup() === $this) {
            $PkgShopPaymentIpnMessage->setShopPaymentHandlerGroup(null);
        }
    }

    return $this;
}


  
    // TCMSFieldOption
public function getenvironment(): string
{
    return $this->Environment;
}
public function setenvironment(string $Environment): self
{
    $this->Environment = $Environment;

    return $this;
}


  
}
