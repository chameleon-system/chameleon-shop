<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\Core\CmsTags;
use ChameleonSystem\DataAccessBundle\Entity\Core\CmsUser;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsDocument;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;
use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopCategory;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopVat;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopArticle
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldCMSUser
        /** @var CmsUser|null - Name */
        private ?CmsUser $cmsUser = null,
        // TCMSFieldVarchar
        /** @var string - Product number */
        private string $articlenumber = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticleImage> - Detailed product pictures */
        private Collection $shopArticleImageCollection = new ArrayCollection(),
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Default preview image of the product */
        private ?CmsMedia $cmsMediaDefaultPreviewImage = null,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticlePreviewImage> - Product preview images */
        private Collection $shopArticlePreviewImageCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticleDocument> - Product documents */
        private Collection $shopArticleDocumentCollection = new ArrayCollection(),
        // TCMSFieldDateTimeNow
        /** @var \DateTime|null - Created on */
        private ?\DateTime $datecreated = new \DateTime(),
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldVarchar
        /** @var string - Subtitle */
        private string $subtitle = '',
        // TCMSFieldVarchar
        /** @var string - USP */
        private string $usp = '',
        // TCMSFieldLookupMultiselectTags
        /** @var Collection<int, CmsTags> - Tag / Catchword */
        private Collection $cmsTagsCollection = new ArrayCollection(),
        // TCMSFieldDecimal
        /** @var string - */
        private string $test = '',
        // TCMSFieldText
        /** @var string - Test */
        private string $testfeld = '',
        // TCMSFieldWYSIWYG
        /** @var string - Short description */
        private string $descriptionShort = '',
        // TCMSFieldWYSIWYG
        /** @var string - Description */
        private string $description = '',
        // TCMSFieldLookup
        /** @var ShopManufacturer|null - Manufacturer / Brand */
        private ?ShopManufacturer $shopManufacturer = null,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticleGroup> - Product groups */
        private Collection $shopArticleGroupCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, ShopArticleType> - Product type */
        private Collection $shopArticleTypeCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopCategory> - Product categories */
        private Collection $shopCategoryCollection = new ArrayCollection(),
        // TCMSFieldExtendedLookup
        /** @var ShopCategory|null - Main category of the product */
        private ?ShopCategory $shopCategory = null,
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, ShopArticleMarker> - Product characteristics */
        private Collection $shopArticleMarkerCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopAttributeValue> - Product attributes */
        private Collection $shopAttributeValueCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, Shop> - Restrict to the following shops */
        private Collection $shopCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticleContributor> - Contributing persons */
        private Collection $shopArticleContributorCollection = new ArrayCollection(),
        // TCMSFieldDownloads
        /** @var Collection<int, CmsDocument> - Download file */
        private Collection $downloadCollection = new ArrayCollection(),
        // TCMSFieldPrice
        /** @var string - Price */
        private string $price = '',
        // TCMSFieldPrice
        /** @var string - Reference price */
        private string $priceReference = '',
        // TCMSFieldLookup
        /** @var ShopVat|null - VAT group */
        private ?ShopVat $shopVat = null,
        // TCMSFieldBoolean
        /** @var bool - Product is free of shipping costs */
        private bool $excludeFromShippingCostCalculation = false,
        // TCMSFieldBoolean
        /** @var bool - Do not allow vouchers */
        private bool $excludeFromVouchers = false,
        // TCMSFieldBoolean
        /** @var bool - Do not allow discounts */
        private bool $excludeFromDiscounts = false,
        // TCMSFieldDecimal
        /** @var string - Weight (grams) */
        private string $sizeWeight = '',
        // TCMSFieldDecimal
        /** @var string - Width (meters) */
        private string $sizeWidth = '',
        // TCMSFieldDecimal
        /** @var string - Height (meters) */
        private string $sizeHeight = '',
        // TCMSFieldDecimal
        /** @var string - Length (meters) */
        private string $sizeLength = '',
        // TCMSFieldDecimal
        /** @var string - Content */
        private string $quantityInUnits = '',
        // TCMSFieldLookup
        /** @var ShopUnitOfMeasurement|null - Measurement unit of content */
        private ?ShopUnitOfMeasurement $shopUnitOfMeasurement = null,
        // TCMSFieldVarchar
        /** @var string - Variant name */
        private string $nameVariantInfo = '',
        // TCMSFieldLookup
        /** @var ShopVariantSet|null - Variant set */
        private ?ShopVariantSet $shopVariantSet = null,
        // TCMSFieldLookupParentID
        /** @var ShopArticle|null - Is a variant of */
        private ?ShopArticle $variantParent = null,
        // TCMSFieldBoolean
        /** @var bool - Is the parent of the variant active? */
        private bool $variantParentIsActive = true,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticle> - Product variants */
        private Collection $shopArticleVariantsCollection = new ArrayCollection(),
        // TCMSFieldShopVariantDetails
        /** @var Collection<int, ShopVariantTypeValue> - Variant values */
        private Collection $shopVariantTypeValueCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var ShopArticleStock|null - Stock */
        private ?ShopArticleStock $shopArticleStockCollection = null,
        // TCMSFieldBoolean
        /** @var bool - Offer preorder at 0 stock */
        private bool $showPreorderOnZeroStock = false,
        // TCMSFieldLookup
        /** @var ShopStockMessage|null - Delivery status */
        private ?ShopStockMessage $shopStockMessage = null,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticle> - Accessories */
        private Collection $shopArticle2Collection = new ArrayCollection(),
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticle> - Similar products */
        private Collection $shopArticleCollection = new ArrayCollection(),
        // TCMSFieldVarchar
        /** @var string - SEO pattern */
        private string $seoPattern = '',
        // TCMSFieldVarchar
        /** @var string - Meta keywords */
        private string $metaKeywords = '',
        // TCMSFieldVarchar
        /** @var string - Meta description */
        private string $metaDescription = '',
        // TCMSFieldNumber
        /** @var int - Quantifier / Product ranking */
        private int $listRank = 0,
        // TCMSFieldBoolean
        /** @var bool - Virtual product */
        private bool $virtualArticle = false,
        // TCMSFieldBoolean
        /** @var bool - Is searchable */
        private bool $isSearchable = true,
        // TCMSFieldBoolean
        /** @var bool - Mark as new */
        private bool $isNew = false,
        // TCMSFieldVarchar
        /** @var string - Number of stars */
        private string $stars = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticleReview> - Customer reviews */
        private Collection $shopArticleReviewCollection = new ArrayCollection(),
        // TCMSFieldBoolean
        /** @var bool - Is a bundle */
        private bool $isBundle = false,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopBundleArticle> - Items belonging to this bundle */
        private Collection $shopBundleArticleCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var ShopArticleStats|null - Statistics */
        private ?ShopArticleStats $shopArticleStatsCollection = null
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

    // TCMSFieldCMSUser
    public function getCmsUser(): ?CmsUser
    {
        return $this->cmsUser;
    }

    public function setCmsUser(?CmsUser $cmsUser): self
    {
        $this->cmsUser = $cmsUser;

        return $this;
    }

    // TCMSFieldVarchar
    public function getArticlenumber(): string
    {
        return $this->articlenumber;
    }

    public function setArticlenumber(string $articlenumber): self
    {
        $this->articlenumber = $articlenumber;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopArticleImage>
     */
    public function getShopArticleImageCollection(): Collection
    {
        return $this->shopArticleImageCollection;
    }

    public function addShopArticleImageCollection(ShopArticleImage $shopArticleImage): self
    {
        if (!$this->shopArticleImageCollection->contains($shopArticleImage)) {
            $this->shopArticleImageCollection->add($shopArticleImage);
            $shopArticleImage->setShopArticle($this);
        }

        return $this;
    }

    public function removeShopArticleImageCollection(ShopArticleImage $shopArticleImage): self
    {
        if ($this->shopArticleImageCollection->removeElement($shopArticleImage)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleImage->getShopArticle() === $this) {
                $shopArticleImage->setShopArticle(null);
            }
        }

        return $this;
    }

    // TCMSFieldExtendedLookupMedia
    public function getCmsMediaDefaultPreviewImage(): ?CmsMedia
    {
        return $this->cmsMediaDefaultPreviewImage;
    }

    public function setCmsMediaDefaultPreviewImage(?CmsMedia $cmsMediaDefaultPreviewImage): self
    {
        $this->cmsMediaDefaultPreviewImage = $cmsMediaDefaultPreviewImage;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopArticlePreviewImage>
     */
    public function getShopArticlePreviewImageCollection(): Collection
    {
        return $this->shopArticlePreviewImageCollection;
    }

    public function addShopArticlePreviewImageCollection(ShopArticlePreviewImage $shopArticlePreviewImage): self
    {
        if (!$this->shopArticlePreviewImageCollection->contains($shopArticlePreviewImage)) {
            $this->shopArticlePreviewImageCollection->add($shopArticlePreviewImage);
            $shopArticlePreviewImage->setShopArticle($this);
        }

        return $this;
    }

    public function removeShopArticlePreviewImageCollection(ShopArticlePreviewImage $shopArticlePreviewImage): self
    {
        if ($this->shopArticlePreviewImageCollection->removeElement($shopArticlePreviewImage)) {
            // set the owning side to null (unless already changed)
            if ($shopArticlePreviewImage->getShopArticle() === $this) {
                $shopArticlePreviewImage->setShopArticle(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopArticleDocument>
     */
    public function getShopArticleDocumentCollection(): Collection
    {
        return $this->shopArticleDocumentCollection;
    }

    public function addShopArticleDocumentCollection(ShopArticleDocument $shopArticleDocument): self
    {
        if (!$this->shopArticleDocumentCollection->contains($shopArticleDocument)) {
            $this->shopArticleDocumentCollection->add($shopArticleDocument);
            $shopArticleDocument->setShopArticle($this);
        }

        return $this;
    }

    public function removeShopArticleDocumentCollection(ShopArticleDocument $shopArticleDocument): self
    {
        if ($this->shopArticleDocumentCollection->removeElement($shopArticleDocument)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleDocument->getShopArticle() === $this) {
                $shopArticleDocument->setShopArticle(null);
            }
        }

        return $this;
    }

    // TCMSFieldDateTimeNow
    public function getDatecreated(): ?\DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?\DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

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

    // TCMSFieldVarchar
    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    // TCMSFieldVarchar
    public function getUsp(): string
    {
        return $this->usp;
    }

    public function setUsp(string $usp): self
    {
        $this->usp = $usp;

        return $this;
    }

    // TCMSFieldLookupMultiselectTags

    /**
     * @return Collection<int, CmsTags>
     */
    public function getCmsTagsCollection(): Collection
    {
        return $this->cmsTagsCollection;
    }

    public function addCmsTagsCollection(CmsTags $cmsTagsMlt): self
    {
        if (!$this->cmsTagsCollection->contains($cmsTagsMlt)) {
            $this->cmsTagsCollection->add($cmsTagsMlt);
            $cmsTagsMlt->set($this);
        }

        return $this;
    }

    public function removeCmsTagsCollection(CmsTags $cmsTagsMlt): self
    {
        if ($this->cmsTagsCollection->removeElement($cmsTagsMlt)) {
            // set the owning side to null (unless already changed)
            if ($cmsTagsMlt->get() === $this) {
                $cmsTagsMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldDecimal
    public function getTest(): string
    {
        return $this->test;
    }

    public function setTest(string $test): self
    {
        $this->test = $test;

        return $this;
    }

    // TCMSFieldText
    public function getTestfeld(): string
    {
        return $this->testfeld;
    }

    public function setTestfeld(string $testfeld): self
    {
        $this->testfeld = $testfeld;

        return $this;
    }

    // TCMSFieldWYSIWYG
    public function getDescriptionShort(): string
    {
        return $this->descriptionShort;
    }

    public function setDescriptionShort(string $descriptionShort): self
    {
        $this->descriptionShort = $descriptionShort;

        return $this;
    }

    // TCMSFieldWYSIWYG
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopManufacturer(): ?ShopManufacturer
    {
        return $this->shopManufacturer;
    }

    public function setShopManufacturer(?ShopManufacturer $shopManufacturer): self
    {
        $this->shopManufacturer = $shopManufacturer;

        return $this;
    }

    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopArticleGroup>
     */
    public function getShopArticleGroupCollection(): Collection
    {
        return $this->shopArticleGroupCollection;
    }

    public function addShopArticleGroupCollection(ShopArticleGroup $shopArticleGroupMlt): self
    {
        if (!$this->shopArticleGroupCollection->contains($shopArticleGroupMlt)) {
            $this->shopArticleGroupCollection->add($shopArticleGroupMlt);
            $shopArticleGroupMlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleGroupCollection(ShopArticleGroup $shopArticleGroupMlt): self
    {
        if ($this->shopArticleGroupCollection->removeElement($shopArticleGroupMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleGroupMlt->get() === $this) {
                $shopArticleGroupMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, ShopArticleType>
     */
    public function getShopArticleTypeCollection(): Collection
    {
        return $this->shopArticleTypeCollection;
    }

    public function addShopArticleTypeCollection(ShopArticleType $shopArticleTypeMlt): self
    {
        if (!$this->shopArticleTypeCollection->contains($shopArticleTypeMlt)) {
            $this->shopArticleTypeCollection->add($shopArticleTypeMlt);
            $shopArticleTypeMlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleTypeCollection(ShopArticleType $shopArticleTypeMlt): self
    {
        if ($this->shopArticleTypeCollection->removeElement($shopArticleTypeMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleTypeMlt->get() === $this) {
                $shopArticleTypeMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopCategory>
     */
    public function getShopCategoryCollection(): Collection
    {
        return $this->shopCategoryCollection;
    }

    public function addShopCategoryCollection(ShopCategory $shopCategoryMlt): self
    {
        if (!$this->shopCategoryCollection->contains($shopCategoryMlt)) {
            $this->shopCategoryCollection->add($shopCategoryMlt);
            $shopCategoryMlt->set($this);
        }

        return $this;
    }

    public function removeShopCategoryCollection(ShopCategory $shopCategoryMlt): self
    {
        if ($this->shopCategoryCollection->removeElement($shopCategoryMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopCategoryMlt->get() === $this) {
                $shopCategoryMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getShopCategory(): ?ShopCategory
    {
        return $this->shopCategory;
    }

    public function setShopCategory(?ShopCategory $shopCategory): self
    {
        $this->shopCategory = $shopCategory;

        return $this;
    }

    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, ShopArticleMarker>
     */
    public function getShopArticleMarkerCollection(): Collection
    {
        return $this->shopArticleMarkerCollection;
    }

    public function addShopArticleMarkerCollection(ShopArticleMarker $shopArticleMarkerMlt): self
    {
        if (!$this->shopArticleMarkerCollection->contains($shopArticleMarkerMlt)) {
            $this->shopArticleMarkerCollection->add($shopArticleMarkerMlt);
            $shopArticleMarkerMlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleMarkerCollection(ShopArticleMarker $shopArticleMarkerMlt): self
    {
        if ($this->shopArticleMarkerCollection->removeElement($shopArticleMarkerMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleMarkerMlt->get() === $this) {
                $shopArticleMarkerMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopAttributeValue>
     */
    public function getShopAttributeValueCollection(): Collection
    {
        return $this->shopAttributeValueCollection;
    }

    public function addShopAttributeValueCollection(ShopAttributeValue $shopAttributeValueMlt): self
    {
        if (!$this->shopAttributeValueCollection->contains($shopAttributeValueMlt)) {
            $this->shopAttributeValueCollection->add($shopAttributeValueMlt);
            $shopAttributeValueMlt->set($this);
        }

        return $this;
    }

    public function removeShopAttributeValueCollection(ShopAttributeValue $shopAttributeValueMlt): self
    {
        if ($this->shopAttributeValueCollection->removeElement($shopAttributeValueMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopAttributeValueMlt->get() === $this) {
                $shopAttributeValueMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, Shop>
     */
    public function getShopCollection(): Collection
    {
        return $this->shopCollection;
    }

    public function addShopCollection(Shop $shopMlt): self
    {
        if (!$this->shopCollection->contains($shopMlt)) {
            $this->shopCollection->add($shopMlt);
            $shopMlt->set($this);
        }

        return $this;
    }

    public function removeShopCollection(Shop $shopMlt): self
    {
        if ($this->shopCollection->removeElement($shopMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopMlt->get() === $this) {
                $shopMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopArticleContributor>
     */
    public function getShopArticleContributorCollection(): Collection
    {
        return $this->shopArticleContributorCollection;
    }

    public function addShopArticleContributorCollection(ShopArticleContributor $shopArticleContributor): self
    {
        if (!$this->shopArticleContributorCollection->contains($shopArticleContributor)) {
            $this->shopArticleContributorCollection->add($shopArticleContributor);
            $shopArticleContributor->setShopArticle($this);
        }

        return $this;
    }

    public function removeShopArticleContributorCollection(ShopArticleContributor $shopArticleContributor): self
    {
        if ($this->shopArticleContributorCollection->removeElement($shopArticleContributor)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleContributor->getShopArticle() === $this) {
                $shopArticleContributor->setShopArticle(null);
            }
        }

        return $this;
    }

    // TCMSFieldDownloads

    /**
     * @return Collection<int, CmsDocument>
     */
    public function getDownloadCollection(): Collection
    {
        return $this->downloadCollection;
    }

    public function addDownloadCollection(CmsDocument $download): self
    {
        if (!$this->downloadCollection->contains($download)) {
            $this->downloadCollection->add($download);
            $download->set($this);
        }

        return $this;
    }

    public function removeDownloadCollection(CmsDocument $download): self
    {
        if ($this->downloadCollection->removeElement($download)) {
            // set the owning side to null (unless already changed)
            if ($download->get() === $this) {
                $download->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldPrice
    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    // TCMSFieldPrice
    public function getPriceReference(): string
    {
        return $this->priceReference;
    }

    public function setPriceReference(string $priceReference): self
    {
        $this->priceReference = $priceReference;

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

    // TCMSFieldBoolean
    public function isExcludeFromShippingCostCalculation(): bool
    {
        return $this->excludeFromShippingCostCalculation;
    }

    public function setExcludeFromShippingCostCalculation(bool $excludeFromShippingCostCalculation): self
    {
        $this->excludeFromShippingCostCalculation = $excludeFromShippingCostCalculation;

        return $this;
    }

    // TCMSFieldBoolean
    public function isExcludeFromVouchers(): bool
    {
        return $this->excludeFromVouchers;
    }

    public function setExcludeFromVouchers(bool $excludeFromVouchers): self
    {
        $this->excludeFromVouchers = $excludeFromVouchers;

        return $this;
    }

    // TCMSFieldBoolean
    public function isExcludeFromDiscounts(): bool
    {
        return $this->excludeFromDiscounts;
    }

    public function setExcludeFromDiscounts(bool $excludeFromDiscounts): self
    {
        $this->excludeFromDiscounts = $excludeFromDiscounts;

        return $this;
    }

    // TCMSFieldDecimal
    public function getSizeWeight(): string
    {
        return $this->sizeWeight;
    }

    public function setSizeWeight(string $sizeWeight): self
    {
        $this->sizeWeight = $sizeWeight;

        return $this;
    }

    // TCMSFieldDecimal
    public function getSizeWidth(): string
    {
        return $this->sizeWidth;
    }

    public function setSizeWidth(string $sizeWidth): self
    {
        $this->sizeWidth = $sizeWidth;

        return $this;
    }

    // TCMSFieldDecimal
    public function getSizeHeight(): string
    {
        return $this->sizeHeight;
    }

    public function setSizeHeight(string $sizeHeight): self
    {
        $this->sizeHeight = $sizeHeight;

        return $this;
    }

    // TCMSFieldDecimal
    public function getSizeLength(): string
    {
        return $this->sizeLength;
    }

    public function setSizeLength(string $sizeLength): self
    {
        $this->sizeLength = $sizeLength;

        return $this;
    }

    // TCMSFieldDecimal
    public function getQuantityInUnits(): string
    {
        return $this->quantityInUnits;
    }

    public function setQuantityInUnits(string $quantityInUnits): self
    {
        $this->quantityInUnits = $quantityInUnits;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopUnitOfMeasurement(): ?ShopUnitOfMeasurement
    {
        return $this->shopUnitOfMeasurement;
    }

    public function setShopUnitOfMeasurement(?ShopUnitOfMeasurement $shopUnitOfMeasurement): self
    {
        $this->shopUnitOfMeasurement = $shopUnitOfMeasurement;

        return $this;
    }

    // TCMSFieldVarchar
    public function getNameVariantInfo(): string
    {
        return $this->nameVariantInfo;
    }

    public function setNameVariantInfo(string $nameVariantInfo): self
    {
        $this->nameVariantInfo = $nameVariantInfo;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopVariantSet(): ?ShopVariantSet
    {
        return $this->shopVariantSet;
    }

    public function setShopVariantSet(?ShopVariantSet $shopVariantSet): self
    {
        $this->shopVariantSet = $shopVariantSet;

        return $this;
    }

    // TCMSFieldLookupParentID

    public function isVariantParentIsActive(): bool
    {
        return $this->variantParentIsActive;
    }

    public function setVariantParentIsActive(bool $variantParentIsActive): self
    {
        $this->variantParentIsActive = $variantParentIsActive;

        return $this;
    }

    // TCMSFieldBoolean

    /**
     * @return Collection<int, ShopArticle>
     */
    public function getShopArticleVariantsCollection(): Collection
    {
        return $this->shopArticleVariantsCollection;
    }

    public function addShopArticleVariantsCollection(ShopArticle $shopArticleVariants): self
    {
        if (!$this->shopArticleVariantsCollection->contains($shopArticleVariants)) {
            $this->shopArticleVariantsCollection->add($shopArticleVariants);
            $shopArticleVariants->setVariantParent($this);
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    public function setVariantParent(?ShopArticle $variantParent): self
    {
        $this->variantParent = $variantParent;

        return $this;
    }

    public function removeShopArticleVariantsCollection(ShopArticle $shopArticleVariants): self
    {
        if ($this->shopArticleVariantsCollection->removeElement($shopArticleVariants)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleVariants->getVariantParent() === $this) {
                $shopArticleVariants->setVariantParent(null);
            }
        }

        return $this;
    }

    public function getVariantParent(): ?ShopArticle
    {
        return $this->variantParent;
    }

    // TCMSFieldShopVariantDetails

    /**
     * @return Collection<int, ShopVariantTypeValue>
     */
    public function getShopVariantTypeValueCollection(): Collection
    {
        return $this->shopVariantTypeValueCollection;
    }

    public function addShopVariantTypeValueCollection(ShopVariantTypeValue $shopVariantTypeValueMlt): self
    {
        if (!$this->shopVariantTypeValueCollection->contains($shopVariantTypeValueMlt)) {
            $this->shopVariantTypeValueCollection->add($shopVariantTypeValueMlt);
            $shopVariantTypeValueMlt->set($this);
        }

        return $this;
    }

    public function removeShopVariantTypeValueCollection(ShopVariantTypeValue $shopVariantTypeValueMlt): self
    {
        if ($this->shopVariantTypeValueCollection->removeElement($shopVariantTypeValueMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopVariantTypeValueMlt->get() === $this) {
                $shopVariantTypeValueMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable
    public function getShopArticleStockCollection(): ?ShopArticleStock
    {
        return $this->shopArticleStockCollection;
    }

    public function setShopArticleStockCollection(?ShopArticleStock $shopArticleStockCollection): self
    {
        $this->shopArticleStockCollection = $shopArticleStockCollection;

        return $this;
    }

    // TCMSFieldBoolean
    public function isShowPreorderOnZeroStock(): bool
    {
        return $this->showPreorderOnZeroStock;
    }

    public function setShowPreorderOnZeroStock(bool $showPreorderOnZeroStock): self
    {
        $this->showPreorderOnZeroStock = $showPreorderOnZeroStock;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopStockMessage(): ?ShopStockMessage
    {
        return $this->shopStockMessage;
    }

    public function setShopStockMessage(?ShopStockMessage $shopStockMessage): self
    {
        $this->shopStockMessage = $shopStockMessage;

        return $this;
    }

    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopArticle>
     */
    public function getShopArticle2Collection(): Collection
    {
        return $this->shopArticle2Collection;
    }

    public function addShopArticle2Collection(ShopArticle $shopArticle2Mlt): self
    {
        if (!$this->shopArticle2Collection->contains($shopArticle2Mlt)) {
            $this->shopArticle2Collection->add($shopArticle2Mlt);
            $shopArticle2Mlt->set($this);
        }

        return $this;
    }

    public function removeShopArticle2Collection(ShopArticle $shopArticle2Mlt): self
    {
        if ($this->shopArticle2Collection->removeElement($shopArticle2Mlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticle2Mlt->get() === $this) {
                $shopArticle2Mlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopArticle>
     */
    public function getShopArticleCollection(): Collection
    {
        return $this->shopArticleCollection;
    }

    public function addShopArticleCollection(ShopArticle $shopArticleMlt): self
    {
        if (!$this->shopArticleCollection->contains($shopArticleMlt)) {
            $this->shopArticleCollection->add($shopArticleMlt);
            $shopArticleMlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleCollection(ShopArticle $shopArticleMlt): self
    {
        if ($this->shopArticleCollection->removeElement($shopArticleMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleMlt->get() === $this) {
                $shopArticleMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldVarchar
    public function getSeoPattern(): string
    {
        return $this->seoPattern;
    }

    public function setSeoPattern(string $seoPattern): self
    {
        $this->seoPattern = $seoPattern;

        return $this;
    }

    // TCMSFieldVarchar
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    // TCMSFieldVarchar
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    // TCMSFieldNumber
    public function getListRank(): int
    {
        return $this->listRank;
    }

    public function setListRank(int $listRank): self
    {
        $this->listRank = $listRank;

        return $this;
    }

    // TCMSFieldBoolean
    public function isVirtualArticle(): bool
    {
        return $this->virtualArticle;
    }

    public function setVirtualArticle(bool $virtualArticle): self
    {
        $this->virtualArticle = $virtualArticle;

        return $this;
    }

    // TCMSFieldBoolean
    public function isIsSearchable(): bool
    {
        return $this->isSearchable;
    }

    public function setIsSearchable(bool $isSearchable): self
    {
        $this->isSearchable = $isSearchable;

        return $this;
    }

    // TCMSFieldBoolean
    public function isIsNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;

        return $this;
    }

    // TCMSFieldVarchar
    public function getStars(): string
    {
        return $this->stars;
    }

    public function setStars(string $stars): self
    {
        $this->stars = $stars;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopArticleReview>
     */
    public function getShopArticleReviewCollection(): Collection
    {
        return $this->shopArticleReviewCollection;
    }

    public function addShopArticleReviewCollection(ShopArticleReview $shopArticleReview): self
    {
        if (!$this->shopArticleReviewCollection->contains($shopArticleReview)) {
            $this->shopArticleReviewCollection->add($shopArticleReview);
            $shopArticleReview->setShopArticle($this);
        }

        return $this;
    }

    public function removeShopArticleReviewCollection(ShopArticleReview $shopArticleReview): self
    {
        if ($this->shopArticleReviewCollection->removeElement($shopArticleReview)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleReview->getShopArticle() === $this) {
                $shopArticleReview->setShopArticle(null);
            }
        }

        return $this;
    }

    // TCMSFieldBoolean
    public function isIsBundle(): bool
    {
        return $this->isBundle;
    }

    public function setIsBundle(bool $isBundle): self
    {
        $this->isBundle = $isBundle;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopBundleArticle>
     */
    public function getShopBundleArticleCollection(): Collection
    {
        return $this->shopBundleArticleCollection;
    }

    public function addShopBundleArticleCollection(ShopBundleArticle $shopBundleArticle): self
    {
        if (!$this->shopBundleArticleCollection->contains($shopBundleArticle)) {
            $this->shopBundleArticleCollection->add($shopBundleArticle);
            $shopBundleArticle->setShopArticle($this);
        }

        return $this;
    }

    public function removeShopBundleArticleCollection(ShopBundleArticle $shopBundleArticle): self
    {
        if ($this->shopBundleArticleCollection->removeElement($shopBundleArticle)) {
            // set the owning side to null (unless already changed)
            if ($shopBundleArticle->getShopArticle() === $this) {
                $shopBundleArticle->setShopArticle(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable
    public function getShopArticleStatsCollection(): ?ShopArticleStats
    {
        return $this->shopArticleStatsCollection;
    }

    public function setShopArticleStatsCollection(?ShopArticleStats $shopArticleStatsCollection): self
    {
        $this->shopArticleStatsCollection = $shopArticleStatsCollection;

        return $this;
    }
}
