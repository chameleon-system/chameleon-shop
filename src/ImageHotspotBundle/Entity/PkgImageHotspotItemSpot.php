<?php

namespace ChameleonSystem\ImageHotspotBundle\Entity;

class PkgImageHotspotItemSpot
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgImageHotspotItem|null - Belongs to hotspot image */
        private ?PkgImageHotspotItem $pkgImageHotspotItem = null,
        // TCMSFieldNumber
        /** @var int - Distance top */
        private int $top = 0,
        // TCMSFieldNumber
        /** @var int - Distance left */
        private int $left = 0,
        // TCMSFieldOption
        /** @var string - Hotspot icon type */
        private string $hotspotType = 'Hotspot-Rechts',
        // TCMSFieldExtendedLookupMultiTable
        /** @var string - Linked CMS object */
        private string $linkedRecord = '',
        // TCMSFieldExtendedLookupMultiTable
        /** @var string - Linked CMS object */
        private string $linkedRecordTableName = '',
        // TCMSFieldURL
        /** @var string - External URL */
        private string $externalUrl = '',
        // TCMSFieldText
        /** @var string - Polygon area */
        private string $polygonArea = '',
        // TCMSFieldBoolean
        /** @var bool - Show product info layover */
        private bool $showSpot = true
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

    // TCMSFieldOption
    public function getHotspotType(): string
    {
        return $this->hotspotType;
    }

    public function setHotspotType(string $hotspotType): self
    {
        $this->hotspotType = $hotspotType;

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
    public function getExternalUrl(): string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(string $externalUrl): self
    {
        $this->externalUrl = $externalUrl;

        return $this;
    }

    // TCMSFieldText
    public function getPolygonArea(): string
    {
        return $this->polygonArea;
    }

    public function setPolygonArea(string $polygonArea): self
    {
        $this->polygonArea = $polygonArea;

        return $this;
    }

    // TCMSFieldBoolean
    public function isShowSpot(): bool
    {
        return $this->showSpot;
    }

    public function setShowSpot(bool $showSpot): self
    {
        $this->showSpot = $showSpot;

        return $this;
    }
}
