<?php

namespace ChameleonSystem\ShopAffiliateBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopAffiliate
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - URL parameter used to transfer the tracking code */
        private string $urlParameterName = '',
        // TCMSFieldNumber
        /** @var int - Seconds, for which the code is still valid with inactive session */
        private int $numberOfSecondsValid = 0,
        // TCMSFieldVarchar
        /** @var string - Class */
        private string $class = '',
        // TCMSFieldVarchar
        /** @var string - Class subtype (path relative to ./classes) */
        private string $classSubtype = '',
        // TCMSFieldOption
        /** @var string - Class type */
        private string $classType = 'Customer',
        // TCMSFieldText
        /** @var string - Code to be integrated on order success page */
        private string $orderSuccessCode = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopAffiliateParameter> - Parameter */
        private Collection $pkgShopAffiliateParameterCollection = new ArrayCollection()
    ) {
    }

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
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    // TCMSFieldVarchar
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    // TCMSFieldVarchar
    public function getUrlParameterName(): string
    {
        return $this->urlParameterName;
    }

    public function setUrlParameterName(string $urlParameterName): self
    {
        $this->urlParameterName = $urlParameterName;

        return $this;
    }

    // TCMSFieldNumber
    public function getNumberOfSecondsValid(): int
    {
        return $this->numberOfSecondsValid;
    }

    public function setNumberOfSecondsValid(int $numberOfSecondsValid): self
    {
        $this->numberOfSecondsValid = $numberOfSecondsValid;

        return $this;
    }

    // TCMSFieldVarchar
    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    // TCMSFieldVarchar
    public function getClassSubtype(): string
    {
        return $this->classSubtype;
    }

    public function setClassSubtype(string $classSubtype): self
    {
        $this->classSubtype = $classSubtype;

        return $this;
    }

    // TCMSFieldOption
    public function getClassType(): string
    {
        return $this->classType;
    }

    public function setClassType(string $classType): self
    {
        $this->classType = $classType;

        return $this;
    }

    // TCMSFieldText
    public function getOrderSuccessCode(): string
    {
        return $this->orderSuccessCode;
    }

    public function setOrderSuccessCode(string $orderSuccessCode): self
    {
        $this->orderSuccessCode = $orderSuccessCode;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopAffiliateParameter>
     */
    public function getPkgShopAffiliateParameterCollection(): Collection
    {
        return $this->pkgShopAffiliateParameterCollection;
    }

    public function addPkgShopAffiliateParameterCollection(PkgShopAffiliateParameter $pkgShopAffiliateParameter): self
    {
        if (!$this->pkgShopAffiliateParameterCollection->contains($pkgShopAffiliateParameter)) {
            $this->pkgShopAffiliateParameterCollection->add($pkgShopAffiliateParameter);
            $pkgShopAffiliateParameter->setPkgShopAffiliate($this);
        }

        return $this;
    }

    public function removePkgShopAffiliateParameterCollection(PkgShopAffiliateParameter $pkgShopAffiliateParameter
    ): self {
        if ($this->pkgShopAffiliateParameterCollection->removeElement($pkgShopAffiliateParameter)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopAffiliateParameter->getPkgShopAffiliate() === $this) {
                $pkgShopAffiliateParameter->setPkgShopAffiliate(null);
            }
        }

        return $this;
    }
}
