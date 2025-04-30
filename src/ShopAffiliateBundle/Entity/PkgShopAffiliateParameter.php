<?php

namespace ChameleonSystem\ShopAffiliateBundle\Entity;

class PkgShopAffiliateParameter
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgShopAffiliate|null - Belongs to affiliate program */
        private ?PkgShopAffiliate $pkgShopAffiliate = null,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldText
        /** @var string - Value */
        private string $value = ''
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
    public function getPkgShopAffiliate(): ?PkgShopAffiliate
    {
        return $this->pkgShopAffiliate;
    }

    public function setPkgShopAffiliate(?PkgShopAffiliate $pkgShopAffiliate): self
    {
        $this->pkgShopAffiliate = $pkgShopAffiliate;

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

    // TCMSFieldText
    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
