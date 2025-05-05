<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopListfilter
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - Title to be shown on top of the filter on the website */
        private string $title = '',
        // TCMSFieldWYSIWYG
        /** @var string - Description text shown on top of the filter */
        private string $introtext = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopListfilterItem> - List filter entries */
        private Collection $pkgShopListfilterItemCollection = new ArrayCollection()
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
    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    // TCMSFieldWYSIWYG
    public function getIntrotext(): string
    {
        return $this->introtext;
    }

    public function setIntrotext(string $introtext): self
    {
        $this->introtext = $introtext;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopListfilterItem>
     */
    public function getPkgShopListfilterItemCollection(): Collection
    {
        return $this->pkgShopListfilterItemCollection;
    }

    public function addPkgShopListfilterItemCollection(PkgShopListfilterItem $pkgShopListfilterItem): self
    {
        if (!$this->pkgShopListfilterItemCollection->contains($pkgShopListfilterItem)) {
            $this->pkgShopListfilterItemCollection->add($pkgShopListfilterItem);
            $pkgShopListfilterItem->setPkgShopListfilter($this);
        }

        return $this;
    }

    public function removePkgShopListfilterItemCollection(PkgShopListfilterItem $pkgShopListfilterItem): self
    {
        if ($this->pkgShopListfilterItemCollection->removeElement($pkgShopListfilterItem)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopListfilterItem->getPkgShopListfilter() === $this) {
                $pkgShopListfilterItem->setPkgShopListfilter(null);
            }
        }

        return $this;
    }
}
