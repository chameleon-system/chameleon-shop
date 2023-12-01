<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;

class shopVoucherSeriesSponsor {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Logo */
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
