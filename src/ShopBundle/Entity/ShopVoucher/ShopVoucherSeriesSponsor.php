<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;

class ShopVoucherSeriesSponsor
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Logo */
        private ?CmsMedia $cmsMedia = null
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


    // TCMSFieldExtendedLookupMedia
    public function getCmsMedia(): ?CmsMedia
    {
        return $this->cmsMedia;
    }

    public function setCmsMedia(?CmsMedia $cmsMedia): self
    {
        $this->cmsMedia = $cmsMedia;

        return $this;
    }


}
