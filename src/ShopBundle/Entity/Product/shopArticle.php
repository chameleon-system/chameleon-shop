<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleImage;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticlePreviewImage;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleDocument;
use ChameleonSystem\ShopBundle\Entity\Product\shopManufacturer;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopVat;
use ChameleonSystem\ShopBundle\Entity\Product\shopUnitOfMeasurement;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleStock;
use ChameleonSystem\ShopBundle\Entity\Product\shopStockMessage;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleGroup;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleType;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleMarker;
use ChameleonSystem\ShopBundle\Entity\Product\shopAttributeValue;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleContributor;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleReview;
use ChameleonSystem\ShopBundle\Entity\Product\shopBundleArticle;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsDocument;
use ChameleonSystem\ShopBundle\Entity\Product\shopVariantSet;
use ChameleonSystem\ShopBundle\Entity\Product\shopVariantTypeValue;
use ChameleonSystem\DataAccessBundle\Entity\Core\cmsTags;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleStats;

class shopArticle {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticle> - Accessories  */
private Collection $ShopArticle2Collection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - SEO pattern */
private string $SeoPattern = '', 
    // TCMSFieldVarchar
/** @var string - Product number */
private string $Articlenumber = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticleImage> - Detailed product pictures */
private Collection $ShopArticleImageCollection = new ArrayCollection()
, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Default preview image of the product */
private ?cmsMedia $CmsMediaDefaultPreviewImage = null
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticlePreviewImage> - Product preview images */
private Collection $ShopArticlePreviewImageCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticleDocument> - Product documents */
private Collection $ShopArticleDocumentCollection = new ArrayCollection()
, 
    // TCMSFieldNumber
/** @var int - Quantifier / Product ranking */
private int $ListRank = 0, 
    // TCMSFieldDateTimeNow
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = new \DateTime(), 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldWYSIWYG
/** @var string - Short description */
private string $DescriptionShort = '', 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldLookup
/** @var shopManufacturer|null - Manufacturer / Brand */
private ?shopManufacturer $ShopManufacturer = null
, 
    // TCMSFieldPrice
/** @var float - Price */
private float $Price = 0, 
    // TCMSFieldPrice
/** @var float - Reference price */
private float $PriceReference = 0, 
    // TCMSFieldLookup
/** @var shopVat|null - VAT group */
private ?shopVat $ShopVat = null
, 
    // TCMSFieldDecimal
/** @var float - Weight (grams) */
private float $SizeWeight = 0, 
    // TCMSFieldDecimal
/** @var float - Width (meters) */
private float $SizeWidth = 0, 
    // TCMSFieldDecimal
/** @var float - Height (meters) */
private float $SizeHeight = 0, 
    // TCMSFieldDecimal
/** @var float - Length (meters) */
private float $SizeLength = 0, 
    // TCMSFieldDecimal
/** @var float - Content */
private float $QuantityInUnits = 0, 
    // TCMSFieldLookup
/** @var shopUnitOfMeasurement|null - Measurement unit of content */
private ?shopUnitOfMeasurement $ShopUnitOfMeasurement = null
, 
    // TCMSFieldPropertyTable
/** @var shopArticleStock|null - Stock */
private ?shopArticleStock $ShopArticleStockCollection = null
, 
    // TCMSFieldBoolean
/** @var bool - Offer preorder at 0 stock */
private bool $ShowPreorderOnZeroStock = false, 
    // TCMSFieldLookup
/** @var shopStockMessage|null - Delivery status */
private ?shopStockMessage $ShopStockMessage = null
, 
    // TCMSFieldBoolean
/** @var bool - Virtual product */
private bool $VirtualArticle = false, 
    // TCMSFieldBoolean
/** @var bool - Is searchable */
private bool $IsSearchable = true, 
    // TCMSFieldBoolean
/** @var bool - Product is free of shipping costs */
private bool $ExcludeFromShippingCostCalculation = false, 
    // TCMSFieldBoolean
/** @var bool - Do not allow vouchers */
private bool $ExcludeFromVouchers = false, 
    // TCMSFieldBoolean
/** @var bool - Do not allow discounts */
private bool $ExcludeFromDiscounts = false, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticleGroup> - Product groups */
private Collection $ShopArticleGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, shopArticleType> - Product type */
private Collection $ShopArticleTypeCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopCategory> - Product categories */
private Collection $ShopCategoryCollection = new ArrayCollection()
, 
    // TCMSFieldExtendedLookup
/** @var shopCategory|null - Main category of the product */
private ?shopCategory $ShopCategory = null
, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, shopArticleMarker> - Product characteristics */
private Collection $ShopArticleMarkerCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopAttributeValue> - Product attributes */
private Collection $ShopAttributeValueCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, shop> - Restrict to the following shops */
private Collection $ShopCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticleContributor> - Contributing persons */
private Collection $ShopArticleContributorCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Subtitle */
private string $Subtitle = '', 
    // TCMSFieldBoolean
/** @var bool - Mark as new */
private bool $IsNew = false, 
    // TCMSFieldVarchar
/** @var string - USP */
private string $Usp = '', 
    // TCMSFieldVarchar
/** @var string - Number of stars */
private string $Stars = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticleReview> - Customer reviews */
private Collection $ShopArticleReviewCollection = new ArrayCollection()
, 
    // TCMSFieldBoolean
/** @var bool - Is a bundle */
private bool $IsBundle = false, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopBundleArticle> - Items belonging to this bundle */
private Collection $ShopBundleArticleCollection = new ArrayCollection()
, 
    // TCMSFieldDownloads
/** @var Collection<int, cmsDocument> - Download file */
private Collection $DownCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Variant name */
private string $NameVariantInfo = '', 
    // TCMSFieldLookup
/** @var shopVariantSet|null - Variant set */
private ?shopVariantSet $ShopVariantSet = null
, 
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Is a variant of */
private ?shopArticle $VariantParent = null
, 
    // TCMSFieldBoolean
/** @var bool - Is the parent of the variant active? */
private bool $VariantParentIsActive = true, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticle> - Product variants */
private Collection $ShopArticleVariantsCollection = new ArrayCollection()
, 
    // TCMSFieldShopVariantDetails
/** @var Collection<int, shopVariantTypeValue> - Variant values */
private Collection $ShopVariantTypeValueCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Meta keywords */
private string $MetaKeywords = '', 
    // TCMSFieldVarchar
/** @var string - Meta description */
private string $MetaDescription = '', 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticle> - Similar products */
private Collection $ShopArticleCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselectTags
/** @var Collection<int, cmsTags> - Tag / Catchword */
private Collection $CmsTagsCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var shopArticleStats|null - Statistics */
private ?shopArticleStats $ShopArticleStatsCollection = null
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


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopArticle>
*/
public function getShopArticle2Collection(): Collection
{
    return $this->ShopArticle2Collection;
}

public function addShopArticle2Collection(shopArticle $ShopArticle2Mlt): self
{
    if (!$this->ShopArticle2Collection->contains($ShopArticle2Mlt)) {
        $this->ShopArticle2Collection->add($ShopArticle2Mlt);
        $ShopArticle2Mlt->set($this);
    }

    return $this;
}

public function removeShopArticle2Collection(shopArticle $ShopArticle2Mlt): self
{
    if ($this->ShopArticle2Collection->removeElement($ShopArticle2Mlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticle2Mlt->get() === $this) {
            $ShopArticle2Mlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getseoPattern(): string
{
    return $this->SeoPattern;
}
public function setseoPattern(string $SeoPattern): self
{
    $this->SeoPattern = $SeoPattern;

    return $this;
}


  
    // TCMSFieldVarchar
public function getarticlenumber(): string
{
    return $this->Articlenumber;
}
public function setarticlenumber(string $Articlenumber): self
{
    $this->Articlenumber = $Articlenumber;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticleImage>
*/
public function getShopArticleImageCollection(): Collection
{
    return $this->ShopArticleImageCollection;
}

public function addShopArticleImageCollection(shopArticleImage $ShopArticleImage): self
{
    if (!$this->ShopArticleImageCollection->contains($ShopArticleImage)) {
        $this->ShopArticleImageCollection->add($ShopArticleImage);
        $ShopArticleImage->setShopArticle($this);
    }

    return $this;
}

public function removeShopArticleImageCollection(shopArticleImage $ShopArticleImage): self
{
    if ($this->ShopArticleImageCollection->removeElement($ShopArticleImage)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleImage->getShopArticle() === $this) {
            $ShopArticleImage->setShopArticle(null);
        }
    }

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getCmsMediaDefaultPreviewImage(): ?cmsMedia
{
    return $this->CmsMediaDefaultPreviewImage;
}

public function setCmsMediaDefaultPreviewImage(?cmsMedia $CmsMediaDefaultPreviewImage): self
{
    $this->CmsMediaDefaultPreviewImage = $CmsMediaDefaultPreviewImage;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticlePreviewImage>
*/
public function getShopArticlePreviewImageCollection(): Collection
{
    return $this->ShopArticlePreviewImageCollection;
}

public function addShopArticlePreviewImageCollection(shopArticlePreviewImage $ShopArticlePreviewImage): self
{
    if (!$this->ShopArticlePreviewImageCollection->contains($ShopArticlePreviewImage)) {
        $this->ShopArticlePreviewImageCollection->add($ShopArticlePreviewImage);
        $ShopArticlePreviewImage->setShopArticle($this);
    }

    return $this;
}

public function removeShopArticlePreviewImageCollection(shopArticlePreviewImage $ShopArticlePreviewImage): self
{
    if ($this->ShopArticlePreviewImageCollection->removeElement($ShopArticlePreviewImage)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticlePreviewImage->getShopArticle() === $this) {
            $ShopArticlePreviewImage->setShopArticle(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticleDocument>
*/
public function getShopArticleDocumentCollection(): Collection
{
    return $this->ShopArticleDocumentCollection;
}

public function addShopArticleDocumentCollection(shopArticleDocument $ShopArticleDocument): self
{
    if (!$this->ShopArticleDocumentCollection->contains($ShopArticleDocument)) {
        $this->ShopArticleDocumentCollection->add($ShopArticleDocument);
        $ShopArticleDocument->setShopArticle($this);
    }

    return $this;
}

public function removeShopArticleDocumentCollection(shopArticleDocument $ShopArticleDocument): self
{
    if ($this->ShopArticleDocumentCollection->removeElement($ShopArticleDocument)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleDocument->getShopArticle() === $this) {
            $ShopArticleDocument->setShopArticle(null);
        }
    }

    return $this;
}


  
    // TCMSFieldNumber
public function getlistRank(): int
{
    return $this->ListRank;
}
public function setlistRank(int $ListRank): self
{
    $this->ListRank = $ListRank;

    return $this;
}


  
    // TCMSFieldDateTimeNow
public function getdatecreated(): ?\DateTime
{
    return $this->Datecreated;
}
public function setdatecreated(?\DateTime $Datecreated): self
{
    $this->Datecreated = $Datecreated;

    return $this;
}


  
    // TCMSFieldBoolean
public function isactive(): bool
{
    return $this->Active;
}
public function setactive(bool $Active): self
{
    $this->Active = $Active;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescriptionShort(): string
{
    return $this->DescriptionShort;
}
public function setdescriptionShort(string $DescriptionShort): self
{
    $this->DescriptionShort = $DescriptionShort;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescription(): string
{
    return $this->Description;
}
public function setdescription(string $Description): self
{
    $this->Description = $Description;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopManufacturer(): ?shopManufacturer
{
    return $this->ShopManufacturer;
}

public function setShopManufacturer(?shopManufacturer $ShopManufacturer): self
{
    $this->ShopManufacturer = $ShopManufacturer;

    return $this;
}


  
    // TCMSFieldPrice
public function getprice(): float
{
    return $this->Price;
}
public function setprice(float $Price): self
{
    $this->Price = $Price;

    return $this;
}


  
    // TCMSFieldPrice
public function getpriceReference(): float
{
    return $this->PriceReference;
}
public function setpriceReference(float $PriceReference): self
{
    $this->PriceReference = $PriceReference;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopVat(): ?shopVat
{
    return $this->ShopVat;
}

public function setShopVat(?shopVat $ShopVat): self
{
    $this->ShopVat = $ShopVat;

    return $this;
}


  
    // TCMSFieldDecimal
public function getsizeWeight(): float
{
    return $this->SizeWeight;
}
public function setsizeWeight(float $SizeWeight): self
{
    $this->SizeWeight = $SizeWeight;

    return $this;
}


  
    // TCMSFieldDecimal
public function getsizeWidth(): float
{
    return $this->SizeWidth;
}
public function setsizeWidth(float $SizeWidth): self
{
    $this->SizeWidth = $SizeWidth;

    return $this;
}


  
    // TCMSFieldDecimal
public function getsizeHeight(): float
{
    return $this->SizeHeight;
}
public function setsizeHeight(float $SizeHeight): self
{
    $this->SizeHeight = $SizeHeight;

    return $this;
}


  
    // TCMSFieldDecimal
public function getsizeLength(): float
{
    return $this->SizeLength;
}
public function setsizeLength(float $SizeLength): self
{
    $this->SizeLength = $SizeLength;

    return $this;
}


  
    // TCMSFieldDecimal
public function getquantityInUnits(): float
{
    return $this->QuantityInUnits;
}
public function setquantityInUnits(float $QuantityInUnits): self
{
    $this->QuantityInUnits = $QuantityInUnits;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopUnitOfMeasurement(): ?shopUnitOfMeasurement
{
    return $this->ShopUnitOfMeasurement;
}

public function setShopUnitOfMeasurement(?shopUnitOfMeasurement $ShopUnitOfMeasurement): self
{
    $this->ShopUnitOfMeasurement = $ShopUnitOfMeasurement;

    return $this;
}


  
    // TCMSFieldPropertyTable
public function getShopArticleStockCollection(): ?shopArticleStock
{
    return $this->ShopArticleStockCollection;
}

public function setShopArticleStockCollection(?shopArticleStock $ShopArticleStockCollection): self
{
    $this->ShopArticleStockCollection = $ShopArticleStockCollection;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowPreorderOnZeroStock(): bool
{
    return $this->ShowPreorderOnZeroStock;
}
public function setshowPreorderOnZeroStock(bool $ShowPreorderOnZeroStock): self
{
    $this->ShowPreorderOnZeroStock = $ShowPreorderOnZeroStock;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopStockMessage(): ?shopStockMessage
{
    return $this->ShopStockMessage;
}

public function setShopStockMessage(?shopStockMessage $ShopStockMessage): self
{
    $this->ShopStockMessage = $ShopStockMessage;

    return $this;
}


  
    // TCMSFieldBoolean
public function isvirtualArticle(): bool
{
    return $this->VirtualArticle;
}
public function setvirtualArticle(bool $VirtualArticle): self
{
    $this->VirtualArticle = $VirtualArticle;

    return $this;
}


  
    // TCMSFieldBoolean
public function isisSearchable(): bool
{
    return $this->IsSearchable;
}
public function setisSearchable(bool $IsSearchable): self
{
    $this->IsSearchable = $IsSearchable;

    return $this;
}


  
    // TCMSFieldBoolean
public function isexcludeFromShippingCostCalculation(): bool
{
    return $this->ExcludeFromShippingCostCalculation;
}
public function setexcludeFromShippingCostCalculation(bool $ExcludeFromShippingCostCalculation): self
{
    $this->ExcludeFromShippingCostCalculation = $ExcludeFromShippingCostCalculation;

    return $this;
}


  
    // TCMSFieldBoolean
public function isexcludeFromVouchers(): bool
{
    return $this->ExcludeFromVouchers;
}
public function setexcludeFromVouchers(bool $ExcludeFromVouchers): self
{
    $this->ExcludeFromVouchers = $ExcludeFromVouchers;

    return $this;
}


  
    // TCMSFieldBoolean
public function isexcludeFromDiscounts(): bool
{
    return $this->ExcludeFromDiscounts;
}
public function setexcludeFromDiscounts(bool $ExcludeFromDiscounts): self
{
    $this->ExcludeFromDiscounts = $ExcludeFromDiscounts;

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopArticleGroup>
*/
public function getShopArticleGroupCollection(): Collection
{
    return $this->ShopArticleGroupCollection;
}

public function addShopArticleGroupCollection(shopArticleGroup $ShopArticleGroupMlt): self
{
    if (!$this->ShopArticleGroupCollection->contains($ShopArticleGroupMlt)) {
        $this->ShopArticleGroupCollection->add($ShopArticleGroupMlt);
        $ShopArticleGroupMlt->set($this);
    }

    return $this;
}

public function removeShopArticleGroupCollection(shopArticleGroup $ShopArticleGroupMlt): self
{
    if ($this->ShopArticleGroupCollection->removeElement($ShopArticleGroupMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleGroupMlt->get() === $this) {
            $ShopArticleGroupMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, shopArticleType>
*/
public function getShopArticleTypeCollection(): Collection
{
    return $this->ShopArticleTypeCollection;
}

public function addShopArticleTypeCollection(shopArticleType $ShopArticleTypeMlt): self
{
    if (!$this->ShopArticleTypeCollection->contains($ShopArticleTypeMlt)) {
        $this->ShopArticleTypeCollection->add($ShopArticleTypeMlt);
        $ShopArticleTypeMlt->set($this);
    }

    return $this;
}

public function removeShopArticleTypeCollection(shopArticleType $ShopArticleTypeMlt): self
{
    if ($this->ShopArticleTypeCollection->removeElement($ShopArticleTypeMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleTypeMlt->get() === $this) {
            $ShopArticleTypeMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopCategory>
*/
public function getShopCategoryCollection(): Collection
{
    return $this->ShopCategoryCollection;
}

public function addShopCategoryCollection(shopCategory $ShopCategoryMlt): self
{
    if (!$this->ShopCategoryCollection->contains($ShopCategoryMlt)) {
        $this->ShopCategoryCollection->add($ShopCategoryMlt);
        $ShopCategoryMlt->set($this);
    }

    return $this;
}

public function removeShopCategoryCollection(shopCategory $ShopCategoryMlt): self
{
    if ($this->ShopCategoryCollection->removeElement($ShopCategoryMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopCategoryMlt->get() === $this) {
            $ShopCategoryMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getShopCategory(): ?shopCategory
{
    return $this->ShopCategory;
}

public function setShopCategory(?shopCategory $ShopCategory): self
{
    $this->ShopCategory = $ShopCategory;

    return $this;
}


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, shopArticleMarker>
*/
public function getShopArticleMarkerCollection(): Collection
{
    return $this->ShopArticleMarkerCollection;
}

public function addShopArticleMarkerCollection(shopArticleMarker $ShopArticleMarkerMlt): self
{
    if (!$this->ShopArticleMarkerCollection->contains($ShopArticleMarkerMlt)) {
        $this->ShopArticleMarkerCollection->add($ShopArticleMarkerMlt);
        $ShopArticleMarkerMlt->set($this);
    }

    return $this;
}

public function removeShopArticleMarkerCollection(shopArticleMarker $ShopArticleMarkerMlt): self
{
    if ($this->ShopArticleMarkerCollection->removeElement($ShopArticleMarkerMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleMarkerMlt->get() === $this) {
            $ShopArticleMarkerMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopAttributeValue>
*/
public function getShopAttributeValueCollection(): Collection
{
    return $this->ShopAttributeValueCollection;
}

public function addShopAttributeValueCollection(shopAttributeValue $ShopAttributeValueMlt): self
{
    if (!$this->ShopAttributeValueCollection->contains($ShopAttributeValueMlt)) {
        $this->ShopAttributeValueCollection->add($ShopAttributeValueMlt);
        $ShopAttributeValueMlt->set($this);
    }

    return $this;
}

public function removeShopAttributeValueCollection(shopAttributeValue $ShopAttributeValueMlt): self
{
    if ($this->ShopAttributeValueCollection->removeElement($ShopAttributeValueMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopAttributeValueMlt->get() === $this) {
            $ShopAttributeValueMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, shop>
*/
public function getShopCollection(): Collection
{
    return $this->ShopCollection;
}

public function addShopCollection(shop $ShopMlt): self
{
    if (!$this->ShopCollection->contains($ShopMlt)) {
        $this->ShopCollection->add($ShopMlt);
        $ShopMlt->set($this);
    }

    return $this;
}

public function removeShopCollection(shop $ShopMlt): self
{
    if ($this->ShopCollection->removeElement($ShopMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopMlt->get() === $this) {
            $ShopMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticleContributor>
*/
public function getShopArticleContributorCollection(): Collection
{
    return $this->ShopArticleContributorCollection;
}

public function addShopArticleContributorCollection(shopArticleContributor $ShopArticleContributor): self
{
    if (!$this->ShopArticleContributorCollection->contains($ShopArticleContributor)) {
        $this->ShopArticleContributorCollection->add($ShopArticleContributor);
        $ShopArticleContributor->setShopArticle($this);
    }

    return $this;
}

public function removeShopArticleContributorCollection(shopArticleContributor $ShopArticleContributor): self
{
    if ($this->ShopArticleContributorCollection->removeElement($ShopArticleContributor)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleContributor->getShopArticle() === $this) {
            $ShopArticleContributor->setShopArticle(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getsubtitle(): string
{
    return $this->Subtitle;
}
public function setsubtitle(string $Subtitle): self
{
    $this->Subtitle = $Subtitle;

    return $this;
}


  
    // TCMSFieldBoolean
public function isisNew(): bool
{
    return $this->IsNew;
}
public function setisNew(bool $IsNew): self
{
    $this->IsNew = $IsNew;

    return $this;
}


  
    // TCMSFieldVarchar
public function getusp(): string
{
    return $this->Usp;
}
public function setusp(string $Usp): self
{
    $this->Usp = $Usp;

    return $this;
}


  
    // TCMSFieldVarchar
public function getstars(): string
{
    return $this->Stars;
}
public function setstars(string $Stars): self
{
    $this->Stars = $Stars;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticleReview>
*/
public function getShopArticleReviewCollection(): Collection
{
    return $this->ShopArticleReviewCollection;
}

public function addShopArticleReviewCollection(shopArticleReview $ShopArticleReview): self
{
    if (!$this->ShopArticleReviewCollection->contains($ShopArticleReview)) {
        $this->ShopArticleReviewCollection->add($ShopArticleReview);
        $ShopArticleReview->setShopArticle($this);
    }

    return $this;
}

public function removeShopArticleReviewCollection(shopArticleReview $ShopArticleReview): self
{
    if ($this->ShopArticleReviewCollection->removeElement($ShopArticleReview)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleReview->getShopArticle() === $this) {
            $ShopArticleReview->setShopArticle(null);
        }
    }

    return $this;
}


  
    // TCMSFieldBoolean
public function isisBundle(): bool
{
    return $this->IsBundle;
}
public function setisBundle(bool $IsBundle): self
{
    $this->IsBundle = $IsBundle;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopBundleArticle>
*/
public function getShopBundleArticleCollection(): Collection
{
    return $this->ShopBundleArticleCollection;
}

public function addShopBundleArticleCollection(shopBundleArticle $ShopBundleArticle): self
{
    if (!$this->ShopBundleArticleCollection->contains($ShopBundleArticle)) {
        $this->ShopBundleArticleCollection->add($ShopBundleArticle);
        $ShopBundleArticle->setShopArticle($this);
    }

    return $this;
}

public function removeShopBundleArticleCollection(shopBundleArticle $ShopBundleArticle): self
{
    if ($this->ShopBundleArticleCollection->removeElement($ShopBundleArticle)) {
        // set the owning side to null (unless already changed)
        if ($ShopBundleArticle->getShopArticle() === $this) {
            $ShopBundleArticle->setShopArticle(null);
        }
    }

    return $this;
}


  
    // TCMSFieldDownloads
/**
* @return Collection<int, cmsDocument>
*/
public function getDownCollection(): Collection
{
    return $this->DownCollection;
}

public function addDownCollection(cmsDocument $Download): self
{
    if (!$this->DownCollection->contains($Download)) {
        $this->DownCollection->add($Download);
        $Download->set($this);
    }

    return $this;
}

public function removeDownCollection(cmsDocument $Download): self
{
    if ($this->DownCollection->removeElement($Download)) {
        // set the owning side to null (unless already changed)
        if ($Download->get() === $this) {
            $Download->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getnameVariantInfo(): string
{
    return $this->NameVariantInfo;
}
public function setnameVariantInfo(string $NameVariantInfo): self
{
    $this->NameVariantInfo = $NameVariantInfo;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopVariantSet(): ?shopVariantSet
{
    return $this->ShopVariantSet;
}

public function setShopVariantSet(?shopVariantSet $ShopVariantSet): self
{
    $this->ShopVariantSet = $ShopVariantSet;

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getVariantParent(): ?shopArticle
{
    return $this->VariantParent;
}

public function setVariantParent(?shopArticle $VariantParent): self
{
    $this->VariantParent = $VariantParent;

    return $this;
}


  
    // TCMSFieldBoolean
public function isvariantParentIsActive(): bool
{
    return $this->VariantParentIsActive;
}
public function setvariantParentIsActive(bool $VariantParentIsActive): self
{
    $this->VariantParentIsActive = $VariantParentIsActive;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticle>
*/
public function getShopArticleVariantsCollection(): Collection
{
    return $this->ShopArticleVariantsCollection;
}

public function addShopArticleVariantsCollection(shopArticle $ShopArticleVariants): self
{
    if (!$this->ShopArticleVariantsCollection->contains($ShopArticleVariants)) {
        $this->ShopArticleVariantsCollection->add($ShopArticleVariants);
        $ShopArticleVariants->setVariantParent($this);
    }

    return $this;
}

public function removeShopArticleVariantsCollection(shopArticle $ShopArticleVariants): self
{
    if ($this->ShopArticleVariantsCollection->removeElement($ShopArticleVariants)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleVariants->getVariantParent() === $this) {
            $ShopArticleVariants->setVariantParent(null);
        }
    }

    return $this;
}


  
    // TCMSFieldShopVariantDetails
/**
* @return Collection<int, shopVariantTypeValue>
*/
public function getShopVariantTypeValueCollection(): Collection
{
    return $this->ShopVariantTypeValueCollection;
}

public function addShopVariantTypeValueCollection(shopVariantTypeValue $ShopVariantTypeValueMlt): self
{
    if (!$this->ShopVariantTypeValueCollection->contains($ShopVariantTypeValueMlt)) {
        $this->ShopVariantTypeValueCollection->add($ShopVariantTypeValueMlt);
        $ShopVariantTypeValueMlt->set($this);
    }

    return $this;
}

public function removeShopVariantTypeValueCollection(shopVariantTypeValue $ShopVariantTypeValueMlt): self
{
    if ($this->ShopVariantTypeValueCollection->removeElement($ShopVariantTypeValueMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopVariantTypeValueMlt->get() === $this) {
            $ShopVariantTypeValueMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getmetaKeywords(): string
{
    return $this->MetaKeywords;
}
public function setmetaKeywords(string $MetaKeywords): self
{
    $this->MetaKeywords = $MetaKeywords;

    return $this;
}


  
    // TCMSFieldVarchar
public function getmetaDescription(): string
{
    return $this->MetaDescription;
}
public function setmetaDescription(string $MetaDescription): self
{
    $this->MetaDescription = $MetaDescription;

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopArticle>
*/
public function getShopArticleCollection(): Collection
{
    return $this->ShopArticleCollection;
}

public function addShopArticleCollection(shopArticle $ShopArticleMlt): self
{
    if (!$this->ShopArticleCollection->contains($ShopArticleMlt)) {
        $this->ShopArticleCollection->add($ShopArticleMlt);
        $ShopArticleMlt->set($this);
    }

    return $this;
}

public function removeShopArticleCollection(shopArticle $ShopArticleMlt): self
{
    if ($this->ShopArticleCollection->removeElement($ShopArticleMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleMlt->get() === $this) {
            $ShopArticleMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselectTags
/**
* @return Collection<int, cmsTags>
*/
public function getCmsTagsCollection(): Collection
{
    return $this->CmsTagsCollection;
}

public function addCmsTagsCollection(cmsTags $CmsTagsMlt): self
{
    if (!$this->CmsTagsCollection->contains($CmsTagsMlt)) {
        $this->CmsTagsCollection->add($CmsTagsMlt);
        $CmsTagsMlt->set($this);
    }

    return $this;
}

public function removeCmsTagsCollection(cmsTags $CmsTagsMlt): self
{
    if ($this->CmsTagsCollection->removeElement($CmsTagsMlt)) {
        // set the owning side to null (unless already changed)
        if ($CmsTagsMlt->get() === $this) {
            $CmsTagsMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
public function getShopArticleStatsCollection(): ?shopArticleStats
{
    return $this->ShopArticleStatsCollection;
}

public function setShopArticleStatsCollection(?shopArticleStats $ShopArticleStatsCollection): self
{
    $this->ShopArticleStatsCollection = $ShopArticleStatsCollection;

    return $this;
}


  
}
