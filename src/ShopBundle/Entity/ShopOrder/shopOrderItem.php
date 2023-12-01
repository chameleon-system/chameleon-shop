<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\shopManufacturer;
use ChameleonSystem\ShopBundle\Entity\Product\shopUnitOfMeasurement;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderBundleArticle;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsDocument;

class shopOrderItem {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Variant */
private string $NameVariantInfo = '', 
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Belongs to order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldVarchar
/** @var string - sBasketItemKey is the key for the position in the consumer basket */
private string $BasketItemKey = '', 
    // TCMSFieldExtendedLookup
/** @var shopArticle|null - Original article from shop */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Article number */
private string $Articlenumber = '', 
    // TCMSFieldWYSIWYG
/** @var string - Short description */
private string $DescriptionShort = '', 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldExtendedLookup
/** @var shopManufacturer|null - Manufacturer/ brand */
private ?shopManufacturer $ShopManufacturer = null
, 
    // TCMSFieldVarchar
/** @var string - Manufacturer / brand name */
private string $ShopManufacturerName = '', 
    // TCMSFieldDecimal
/** @var float - Price */
private float $Price = 0, 
    // TCMSFieldDecimal
/** @var float - Reference price */
private float $PriceReference = 0, 
    // TCMSFieldDecimal
/** @var float - Discounted price */
private float $PriceDiscounted = 0, 
    // TCMSFieldDecimal
/** @var float - VAT percentage */
private float $VatPercent = 0, 
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
    // TCMSFieldNumber
/** @var int - Stock at time of order */
private int $Stock = 0, 
    // TCMSFieldDecimal
/** @var float - Units per packing */
private float $QuantityInUnits = 0, 
    // TCMSFieldExtendedLookup
/** @var shopUnitOfMeasurement|null - Unit of measurement of content */
private ?shopUnitOfMeasurement $ShopUnitOfMeasurement = null
, 
    // TCMSFieldBoolean
/** @var bool - Virtual article */
private bool $VirtualArticle = false, 
    // TCMSFieldBoolean
/** @var bool - Do not allow vouchers */
private bool $ExcludeFromVouchers = false, 
    // TCMSFieldBoolean
/** @var bool - Do not allow discounts for this article */
private bool $ExcludeFromDiscounts = false, 
    // TCMSFieldVarchar
/** @var string - Subtitle */
private string $Subtitle = '', 
    // TCMSFieldBoolean
/** @var bool - Mark as new */
private bool $IsNew = false, 
    // TCMSFieldNumber
/** @var int - Amount of pages */
private int $Pages = 0, 
    // TCMSFieldVarchar
/** @var string - USP */
private string $Usp = '', 
    // TCMSFieldBlob
/** @var object|null - Custom data */
private ?object $CustomData = null, 
    // TCMSFieldDecimal
/** @var float - Amount */
private float $OrderAmount = 0, 
    // TCMSFieldDecimal
/** @var float - Total price */
private float $OrderPriceTotal = 0, 
    // TCMSFieldDecimal
/** @var float - Order price after calculation of discounts */
private float $OrderPriceAfterDiscounts = 0, 
    // TCMSFieldDecimal
/** @var float - Total weight (grams) */
private float $OrderTotalWeight = 0, 
    // TCMSFieldDecimal
/** @var float - Total volume (cubic meters) */
private float $OrderTotalVolume = 0, 
    // TCMSFieldDecimal
/** @var float - Unit price at time of order */
private float $OrderPrice = 0, 
    // TCMSFieldBoolean
/** @var bool - Is a bundle */
private bool $IsBundle = false, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderBundleArticle> - Articles in order that belong to this bundle */
private Collection $ShopOrderBundleArticleCollection = new ArrayCollection()
, 
    // TCMSFieldDownloads
/** @var Collection<int, cmsDocument> - Download file */
private Collection $DownCollection = new ArrayCollection()
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
public function getnameVariantInfo(): string
{
    return $this->NameVariantInfo;
}
public function setnameVariantInfo(string $NameVariantInfo): self
{
    $this->NameVariantInfo = $NameVariantInfo;

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getShopOrder(): ?shopOrder
{
    return $this->ShopOrder;
}

public function setShopOrder(?shopOrder $ShopOrder): self
{
    $this->ShopOrder = $ShopOrder;

    return $this;
}


  
    // TCMSFieldVarchar
public function getbasketItemKey(): string
{
    return $this->BasketItemKey;
}
public function setbasketItemKey(string $BasketItemKey): self
{
    $this->BasketItemKey = $BasketItemKey;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getShopArticle(): ?shopArticle
{
    return $this->ShopArticle;
}

public function setShopArticle(?shopArticle $ShopArticle): self
{
    $this->ShopArticle = $ShopArticle;

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


  
    // TCMSFieldExtendedLookup
public function getShopManufacturer(): ?shopManufacturer
{
    return $this->ShopManufacturer;
}

public function setShopManufacturer(?shopManufacturer $ShopManufacturer): self
{
    $this->ShopManufacturer = $ShopManufacturer;

    return $this;
}


  
    // TCMSFieldVarchar
public function getshopManufacturerName(): string
{
    return $this->ShopManufacturerName;
}
public function setshopManufacturerName(string $ShopManufacturerName): self
{
    $this->ShopManufacturerName = $ShopManufacturerName;

    return $this;
}


  
    // TCMSFieldDecimal
public function getprice(): float
{
    return $this->Price;
}
public function setprice(float $Price): self
{
    $this->Price = $Price;

    return $this;
}


  
    // TCMSFieldDecimal
public function getpriceReference(): float
{
    return $this->PriceReference;
}
public function setpriceReference(float $PriceReference): self
{
    $this->PriceReference = $PriceReference;

    return $this;
}


  
    // TCMSFieldDecimal
public function getpriceDiscounted(): float
{
    return $this->PriceDiscounted;
}
public function setpriceDiscounted(float $PriceDiscounted): self
{
    $this->PriceDiscounted = $PriceDiscounted;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvatPercent(): float
{
    return $this->VatPercent;
}
public function setvatPercent(float $VatPercent): self
{
    $this->VatPercent = $VatPercent;

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


  
    // TCMSFieldNumber
public function getstock(): int
{
    return $this->Stock;
}
public function setstock(int $Stock): self
{
    $this->Stock = $Stock;

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


  
    // TCMSFieldExtendedLookup
public function getShopUnitOfMeasurement(): ?shopUnitOfMeasurement
{
    return $this->ShopUnitOfMeasurement;
}

public function setShopUnitOfMeasurement(?shopUnitOfMeasurement $ShopUnitOfMeasurement): self
{
    $this->ShopUnitOfMeasurement = $ShopUnitOfMeasurement;

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


  
    // TCMSFieldNumber
public function getpages(): int
{
    return $this->Pages;
}
public function setpages(int $Pages): self
{
    $this->Pages = $Pages;

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


  
    // TCMSFieldBlob
public function getcustomData(): ?object
{
    return $this->CustomData;
}
public function setcustomData(?object $CustomData): self
{
    $this->CustomData = $CustomData;

    return $this;
}


  
    // TCMSFieldDecimal
public function getorderAmount(): float
{
    return $this->OrderAmount;
}
public function setorderAmount(float $OrderAmount): self
{
    $this->OrderAmount = $OrderAmount;

    return $this;
}


  
    // TCMSFieldDecimal
public function getorderPriceTotal(): float
{
    return $this->OrderPriceTotal;
}
public function setorderPriceTotal(float $OrderPriceTotal): self
{
    $this->OrderPriceTotal = $OrderPriceTotal;

    return $this;
}


  
    // TCMSFieldDecimal
public function getorderPriceAfterDiscounts(): float
{
    return $this->OrderPriceAfterDiscounts;
}
public function setorderPriceAfterDiscounts(float $OrderPriceAfterDiscounts): self
{
    $this->OrderPriceAfterDiscounts = $OrderPriceAfterDiscounts;

    return $this;
}


  
    // TCMSFieldDecimal
public function getorderTotalWeight(): float
{
    return $this->OrderTotalWeight;
}
public function setorderTotalWeight(float $OrderTotalWeight): self
{
    $this->OrderTotalWeight = $OrderTotalWeight;

    return $this;
}


  
    // TCMSFieldDecimal
public function getorderTotalVolume(): float
{
    return $this->OrderTotalVolume;
}
public function setorderTotalVolume(float $OrderTotalVolume): self
{
    $this->OrderTotalVolume = $OrderTotalVolume;

    return $this;
}


  
    // TCMSFieldDecimal
public function getorderPrice(): float
{
    return $this->OrderPrice;
}
public function setorderPrice(float $OrderPrice): self
{
    $this->OrderPrice = $OrderPrice;

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
* @return Collection<int, shopOrderBundleArticle>
*/
public function getShopOrderBundleArticleCollection(): Collection
{
    return $this->ShopOrderBundleArticleCollection;
}

public function addShopOrderBundleArticleCollection(shopOrderBundleArticle $ShopOrderBundleArticle): self
{
    if (!$this->ShopOrderBundleArticleCollection->contains($ShopOrderBundleArticle)) {
        $this->ShopOrderBundleArticleCollection->add($ShopOrderBundleArticle);
        $ShopOrderBundleArticle->setShopOrderItem($this);
    }

    return $this;
}

public function removeShopOrderBundleArticleCollection(shopOrderBundleArticle $ShopOrderBundleArticle): self
{
    if ($this->ShopOrderBundleArticleCollection->removeElement($ShopOrderBundleArticle)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderBundleArticle->getShopOrderItem() === $this) {
            $ShopOrderBundleArticle->setShopOrderItem(null);
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


  
}
