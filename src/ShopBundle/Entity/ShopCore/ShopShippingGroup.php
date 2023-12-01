<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetGroup;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Payment\ShopPaymentMethod;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopShippingGroup
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldLookup
        /** @var ShopShippingGroupHandler|null - Shipping group handler */
        private ?ShopShippingGroupHandler $shopShippingGroupHandler = null
        ,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0,
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Active from */
        private ?DateTime $activeFrom = null,
        // TCMSFieldDateTime
        /** @var DateTime|null - Active until */
        private ?DateTime $activeTo = null,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetUser> - Restrict to following customers */
        private Collection $dataExtranetUserCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetGroup> - Restrict to following customer groups */
        private Collection $dataExtranetGroupCollection = new ArrayCollection()
        ,
        // TCMSFieldLookup
        /** @var ShopVat|null - VAT group */
        private ?ShopVat $shopVat = null
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopShippingType> - Shipping types */
        private Collection $shopShippingTypeCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopPaymentMethod> - Payment methods */
        private Collection $shopPaymentMethodCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopShippingGroup> - Is displayed only if the following shipping groups are not available */
        private Collection $shopShippingGroupCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, CmsPortal> - Restrict to the following portals */
        private Collection $cmsPortalCollection = new ArrayCollection()
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


    // TCMSFieldLookup
    public function getShopShippingGroupHandler(): ?ShopShippingGroupHandler
    {
        return $this->shopShippingGroupHandler;
    }

    public function setShopShippingGroupHandler(?ShopShippingGroupHandler $shopShippingGroupHandler): self
    {
        $this->shopShippingGroupHandler = $shopShippingGroupHandler;

        return $this;
    }


    // TCMSFieldPosition
    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }


    // TCMSFieldBoolean
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }


    // TCMSFieldDateTime
    public function getActiveFrom(): ?DateTime
    {
        return $this->activeFrom;
    }

    public function setActiveFrom(?DateTime $activeFrom): self
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }


    // TCMSFieldDateTime
    public function getActiveTo(): ?DateTime
    {
        return $this->activeTo;
    }

    public function setActiveTo(?DateTime $activeTo): self
    {
        $this->activeTo = $activeTo;

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, DataExtranetUser>
     */
    public function getDataExtranetUserCollection(): Collection
    {
        return $this->dataExtranetUserCollection;
    }

    public function addDataExtranetUserCollection(DataExtranetUser $dataExtranetUserMlt): self
    {
        if (!$this->dataExtranetUserCollection->contains($dataExtranetUserMlt)) {
            $this->dataExtranetUserCollection->add($dataExtranetUserMlt);
            $dataExtranetUserMlt->set($this);
        }

        return $this;
    }

    public function removeDataExtranetUserCollection(DataExtranetUser $dataExtranetUserMlt): self
    {
        if ($this->dataExtranetUserCollection->removeElement($dataExtranetUserMlt)) {
            // set the owning side to null (unless already changed)
            if ($dataExtranetUserMlt->get() === $this) {
                $dataExtranetUserMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, DataExtranetGroup>
     */
    public function getDataExtranetGroupCollection(): Collection
    {
        return $this->dataExtranetGroupCollection;
    }

    public function addDataExtranetGroupCollection(DataExtranetGroup $dataExtranetGroupMlt): self
    {
        if (!$this->dataExtranetGroupCollection->contains($dataExtranetGroupMlt)) {
            $this->dataExtranetGroupCollection->add($dataExtranetGroupMlt);
            $dataExtranetGroupMlt->set($this);
        }

        return $this;
    }

    public function removeDataExtranetGroupCollection(DataExtranetGroup $dataExtranetGroupMlt): self
    {
        if ($this->dataExtranetGroupCollection->removeElement($dataExtranetGroupMlt)) {
            // set the owning side to null (unless already changed)
            if ($dataExtranetGroupMlt->get() === $this) {
                $dataExtranetGroupMlt->set(null);
            }
        }

        return $this;
    }


    // TCMSFieldLookup
    public function getShopVat(): ?ShopVat
    {
        return $this->shopVat;
    }

    public function setShopVat(?ShopVat $shopVat): self
    {
        $this->shopVat = $shopVat;

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopShippingType>
     */
    public function getShopShippingTypeCollection(): Collection
    {
        return $this->shopShippingTypeCollection;
    }

    public function addShopShippingTypeCollection(ShopShippingType $shopShippingTypeMlt): self
    {
        if (!$this->shopShippingTypeCollection->contains($shopShippingTypeMlt)) {
            $this->shopShippingTypeCollection->add($shopShippingTypeMlt);
            $shopShippingTypeMlt->set($this);
        }

        return $this;
    }

    public function removeShopShippingTypeCollection(ShopShippingType $shopShippingTypeMlt): self
    {
        if ($this->shopShippingTypeCollection->removeElement($shopShippingTypeMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopShippingTypeMlt->get() === $this) {
                $shopShippingTypeMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopPaymentMethod>
     */
    public function getShopPaymentMethodCollection(): Collection
    {
        return $this->shopPaymentMethodCollection;
    }

    public function addShopPaymentMethodCollection(ShopPaymentMethod $shopPaymentMethodMlt): self
    {
        if (!$this->shopPaymentMethodCollection->contains($shopPaymentMethodMlt)) {
            $this->shopPaymentMethodCollection->add($shopPaymentMethodMlt);
            $shopPaymentMethodMlt->set($this);
        }

        return $this;
    }

    public function removeShopPaymentMethodCollection(ShopPaymentMethod $shopPaymentMethodMlt): self
    {
        if ($this->shopPaymentMethodCollection->removeElement($shopPaymentMethodMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopPaymentMethodMlt->get() === $this) {
                $shopPaymentMethodMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopShippingGroup>
     */
    public function getShopShippingGroupCollection(): Collection
    {
        return $this->shopShippingGroupCollection;
    }

    public function addShopShippingGroupCollection(ShopShippingGroup $shopShippingGroupMlt): self
    {
        if (!$this->shopShippingGroupCollection->contains($shopShippingGroupMlt)) {
            $this->shopShippingGroupCollection->add($shopShippingGroupMlt);
            $shopShippingGroupMlt->set($this);
        }

        return $this;
    }

    public function removeShopShippingGroupCollection(ShopShippingGroup $shopShippingGroupMlt): self
    {
        if ($this->shopShippingGroupCollection->removeElement($shopShippingGroupMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopShippingGroupMlt->get() === $this) {
                $shopShippingGroupMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, CmsPortal>
     */
    public function getCmsPortalCollection(): Collection
    {
        return $this->cmsPortalCollection;
    }

    public function addCmsPortalCollection(CmsPortal $cmsPortalMlt): self
    {
        if (!$this->cmsPortalCollection->contains($cmsPortalMlt)) {
            $this->cmsPortalCollection->add($cmsPortalMlt);
            $cmsPortalMlt->set($this);
        }

        return $this;
    }

    public function removeCmsPortalCollection(CmsPortal $cmsPortalMlt): self
    {
        if ($this->cmsPortalCollection->removeElement($cmsPortalMlt)) {
            // set the owning side to null (unless already changed)
            if ($cmsPortalMlt->get() === $this) {
                $cmsPortalMlt->set(null);
            }
        }

        return $this;
    }


}
