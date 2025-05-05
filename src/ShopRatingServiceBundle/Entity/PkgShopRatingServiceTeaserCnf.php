<?php

namespace ChameleonSystem\ShopRatingServiceBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTplModuleInstance;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopRatingServiceTeaserCnf
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsTplModuleInstance|null - Module instance */
        private ?CmsTplModuleInstance $cmsTplModuleInstance = null,
        // TCMSFieldNumber
        /** @var int - Number of ratings to be selected */
        private int $numberOfRatingsToSelectFrom = 0,
        // TCMSFieldVarchar
        /** @var string - Headline */
        private string $headline = '',
        // TCMSFieldVarchar
        /** @var string - Link name for "show all" */
        private string $showAllLinkName = '',
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, PkgShopRatingService> - Rating service */
        private Collection $pkgShopRatingServiceCollection = new ArrayCollection()
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
    public function getCmsTplModuleInstance(): ?CmsTplModuleInstance
    {
        return $this->cmsTplModuleInstance;
    }

    public function setCmsTplModuleInstance(?CmsTplModuleInstance $cmsTplModuleInstance): self
    {
        $this->cmsTplModuleInstance = $cmsTplModuleInstance;

        return $this;
    }

    // TCMSFieldNumber
    public function getNumberOfRatingsToSelectFrom(): int
    {
        return $this->numberOfRatingsToSelectFrom;
    }

    public function setNumberOfRatingsToSelectFrom(int $numberOfRatingsToSelectFrom): self
    {
        $this->numberOfRatingsToSelectFrom = $numberOfRatingsToSelectFrom;

        return $this;
    }

    // TCMSFieldVarchar
    public function getHeadline(): string
    {
        return $this->headline;
    }

    public function setHeadline(string $headline): self
    {
        $this->headline = $headline;

        return $this;
    }

    // TCMSFieldVarchar
    public function getShowAllLinkName(): string
    {
        return $this->showAllLinkName;
    }

    public function setShowAllLinkName(string $showAllLinkName): self
    {
        $this->showAllLinkName = $showAllLinkName;

        return $this;
    }

    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, PkgShopRatingService>
     */
    public function getPkgShopRatingServiceCollection(): Collection
    {
        return $this->pkgShopRatingServiceCollection;
    }

    public function addPkgShopRatingServiceCollection(PkgShopRatingService $pkgShopRatingServiceMlt): self
    {
        if (!$this->pkgShopRatingServiceCollection->contains($pkgShopRatingServiceMlt)) {
            $this->pkgShopRatingServiceCollection->add($pkgShopRatingServiceMlt);
            $pkgShopRatingServiceMlt->set($this);
        }

        return $this;
    }

    public function removePkgShopRatingServiceCollection(PkgShopRatingService $pkgShopRatingServiceMlt): self
    {
        if ($this->pkgShopRatingServiceCollection->removeElement($pkgShopRatingServiceMlt)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopRatingServiceMlt->get() === $this) {
                $pkgShopRatingServiceMlt->set(null);
            }
        }

        return $this;
    }
}
