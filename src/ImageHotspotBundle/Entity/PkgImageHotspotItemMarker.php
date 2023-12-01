<?php

namespace ChameleonSystem\ImageHotspotBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;

class PkgImageHotspotItemMarker
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgImageHotspotItem|null - Belongs to hotspot image */
        private ?PkgImageHotspotItem $pkgImageHotspotItem = null
        ,
        // TCMSFieldVarchar
        /** @var string - Alt or link text of the image */
        private string $name = '',
        // TCMSFieldNumber
        /** @var int - Position of top border relative to top border of background image */
        private int $top = 0,
        // TCMSFieldNumber
        /** @var int - Position of left border relative to left border of background image */
        private int $left = 0,
        // TCMSFieldExtendedLookupMultiTable
        /** @var string - Link to object */
        private string $linkedRecord = '',
// TCMSFieldExtendedLookupMultiTable
        /** @var string - Link to object */
        private string $linkedRecordTableName = '',
        // TCMSFieldURL
        /** @var string - Alternative link */
        private string $url = '',
        // TCMSFieldBoolean
        /** @var bool - Show object layover */
        private bool $showObjectLayover = false,
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Image */
        private ?CmsMedia $cmsMedia = null
        ,
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Hover image */
        private ?CmsMedia $cmsMediaHover = null
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
    public function getPkgImageHotspotItem(): ?PkgImageHotspotItem
    {
        return $this->pkgImageHotspotItem;
    }

    public function setPkgImageHotspotItem(?PkgImageHotspotItem $pkgImageHotspotItem): self
    {
        $this->pkgImageHotspotItem = $pkgImageHotspotItem;

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


    // TCMSFieldNumber
    public function getTop(): int
    {
        return $this->top;
    }

    public function setTop(int $top): self
    {
        $this->top = $top;

        return $this;
    }


    // TCMSFieldNumber
    public function getLeft(): int
    {
        return $this->left;
    }

    public function setLeft(int $left): self
    {
        $this->left = $left;

        return $this;
    }


    // TCMSFieldExtendedLookupMultiTable
    public function getLinkedRecord(): string
    {
        return $this->linkedRecord;
    }

    public function setLinkedRecord(string $linkedRecord): self
    {
        $this->linkedRecord = $linkedRecord;

        return $this;
    }

// TCMSFieldExtendedLookupMultiTable
    public function getLinkedRecordTableName(): string
    {
        return $this->linkedRecordTableName;
    }

    public function setLinkedRecordTableName(string $linkedRecordTableName): self
    {
        $this->linkedRecordTableName = $linkedRecordTableName;

        return $this;
    }


    // TCMSFieldURL
    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }


    // TCMSFieldBoolean
    public function isShowObjectLayover(): bool
    {
        return $this->showObjectLayover;
    }

    public function setShowObjectLayover(bool $showObjectLayover): self
    {
        $this->showObjectLayover = $showObjectLayover;

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


    // TCMSFieldExtendedLookupMedia
    public function getCmsMediaHover(): ?CmsMedia
    {
        return $this->cmsMediaHover;
    }

    public function setCmsMediaHover(?CmsMedia $cmsMediaHover): self
    {
        $this->cmsMediaHover = $cmsMediaHover;

        return $this;
    }


}
