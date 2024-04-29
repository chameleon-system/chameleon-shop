<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsDocument;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\ShopManufacturer;
use ChameleonSystem\ShopBundle\Entity\Product\ShopUnitOfMeasurement;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopOrderItem
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Variant */
        private string $nameVariantInfo = '',
        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Belongs to order */
        private ?ShopOrder $shopOrder = null
        ,
        // TCMSFieldVarchar
        /** @var string - sBasketItemKey is the key for the position in the consumer basket */
        private string $basketItemKey = '',
        // TCMSFieldExtendedLookup
        /** @var ShopArticle|null - Original article from shop */
        private ?ShopArticle $shopArticle = null
        ,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - Article number */
        private string $articlenumber = '',
        // TCMSFieldWYSIWYG
        /** @var string - Short description */
        private string $descriptionShort = '',
        // TCMSFieldWYSIWYG
        /** @var string - Description */
        private string $description = '',
        // TCMSFieldExtendedLookup
        /** @var ShopManufacturer|null - Manufacturer/ brand */
        private ?ShopManufacturer $shopManufacturer = null
        ,
        // TCMSFieldVarchar
        /** @var string - Manufacturer / brand name */
        private string $shopManufacturerName = '',
        // TCMSFieldDecimal
        /** @var string - Price */
        private string $price = '',
        // TCMSFieldDecimal
        /** @var string - Reference price */
        private string $priceReference = '',
        // TCMSFieldDecimal
        /** @var string - Discounted price */
        private string $priceDiscounted = '0',
        // TCMSFieldDecimal
        /** @var string - VAT percentage */
        private string $vatPercent = '',
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
        // TCMSFieldNumber
        /** @var int - Stock at time of order */
        private int $stock = 0,
        // TCMSFieldDecimal
        /** @var string - Units per packing */
        private string $quantityInUnits = '',
        // TCMSFieldExtendedLookup
        /** @var ShopUnitOfMeasurement|null - Unit of measurement of content */
        private ?ShopUnitOfMeasurement $shopUnitOfMeasurement = null
        ,
        // TCMSFieldBoolean
        /** @var bool - Virtual article */
        private bool $virtualArticle = false,
        // TCMSFieldBoolean
        /** @var bool - Do not allow vouchers */
        private bool $excludeFromVouchers = false,
        // TCMSFieldBoolean
        /** @var bool - Do not allow discounts for this article */
        private bool $excludeFromDiscounts = false,
        // TCMSFieldVarchar
        /** @var string - Subtitle */
        private string $subtitle = '',
        // TCMSFieldBoolean
        /** @var bool - Mark as new */
        private bool $isNew = false,
        // TCMSFieldNumber
        /** @var int - Amount of pages */
        private int $pages = 0,
        // TCMSFieldVarchar
        /** @var string - USP */
        private string $usp = '',
        // TCMSFieldBlob
        /** @var object|null - Custom data */
        private ?object $customData = null,
        // TCMSFieldDecimal
        /** @var string - Amount */
        private string $orderAmount = '',
        // TCMSFieldDecimal
        /** @var string - Total price */
        private string $orderPriceTotal = '',
        // TCMSFieldDecimal
        /** @var string - Order price after calculation of discounts */
        private string $orderPriceAfterDiscounts = '',
        // TCMSFieldDecimal
        /** @var string - Total weight (grams) */
        private string $orderTotalWeight = '',
        // TCMSFieldDecimal
        /** @var string - Total volume (cubic meters) */
        private string $orderTotalVolume = '',
        // TCMSFieldDecimal
        /** @var string - Unit price at time of order */
        private string $orderPrice = '',
        // TCMSFieldBoolean
        /** @var bool - Is a bundle */
        private bool $isBundle = false,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderBundleArticle> - Articles in order that belong to this bundle */
        private Collection $shopOrderBundleArticleCollection = new ArrayCollection()
        ,
        // TCMSFieldDownloads
        /** @var Collection<int, CmsDocument> - Download file */
        private Collection $downloadCollection = new ArrayCollection()
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
    public function getNameVariantInfo(): string
    {
        return $this->nameVariantInfo;
    }

    public function setNameVariantInfo(string $nameVariantInfo): self
    {
        $this->nameVariantInfo = $nameVariantInfo;

        return $this;
    }


    // TCMSFieldLookupParentID
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }


    // TCMSFieldVarchar
    public function getBasketItemKey(): string
    {
        return $this->basketItemKey;
    }

    public function setBasketItemKey(string $basketItemKey): self
    {
        $this->basketItemKey = $basketItemKey;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getShopArticle(): ?ShopArticle
    {
        return $this->shopArticle;
    }

    public function setShopArticle(?ShopArticle $shopArticle): self
    {
        $this->shopArticle = $shopArticle;

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
    public function getArticlenumber(): string
    {
        return $this->articlenumber;
    }

    public function setArticlenumber(string $articlenumber): self
    {
        $this->articlenumber = $articlenumber;

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


    // TCMSFieldExtendedLookup
    public function getShopManufacturer(): ?ShopManufacturer
    {
        return $this->shopManufacturer;
    }

    public function setShopManufacturer(?ShopManufacturer $shopManufacturer): self
    {
        $this->shopManufacturer = $shopManufacturer;

        return $this;
    }


    // TCMSFieldVarchar
    public function getShopManufacturerName(): string
    {
        return $this->shopManufacturerName;
    }

    public function setShopManufacturerName(string $shopManufacturerName): self
    {
        $this->shopManufacturerName = $shopManufacturerName;

        return $this;
    }


    // TCMSFieldDecimal
    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }


    // TCMSFieldDecimal
    public function getPriceReference(): string
    {
        return $this->priceReference;
    }

    public function setPriceReference(string $priceReference): self
    {
        $this->priceReference = $priceReference;

        return $this;
    }


    // TCMSFieldDecimal
    public function getPriceDiscounted(): string
    {
        return $this->priceDiscounted;
    }

    public function setPriceDiscounted(string $priceDiscounted): self
    {
        $this->priceDiscounted = $priceDiscounted;

        return $this;
    }


    // TCMSFieldDecimal
    public function getVatPercent(): string
    {
        return $this->vatPercent;
    }

    public function setVatPercent(string $vatPercent): self
    {
        $this->vatPercent = $vatPercent;

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


    // TCMSFieldNumber
    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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


    // TCMSFieldExtendedLookup
    public function getShopUnitOfMeasurement(): ?ShopUnitOfMeasurement
    {
        return $this->shopUnitOfMeasurement;
    }

    public function setShopUnitOfMeasurement(?ShopUnitOfMeasurement $shopUnitOfMeasurement): self
    {
        $this->shopUnitOfMeasurement = $shopUnitOfMeasurement;

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


    // TCMSFieldNumber
    public function getPages(): int
    {
        return $this->pages;
    }

    public function setPages(int $pages): self
    {
        $this->pages = $pages;

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


    // TCMSFieldBlob
    public function getCustomData(): ?object
    {
        return $this->customData;
    }

    public function setCustomData(?object $customData): self
    {
        $this->customData = $customData;

        return $this;
    }


    // TCMSFieldDecimal
    public function getOrderAmount(): string
    {
        return $this->orderAmount;
    }

    public function setOrderAmount(string $orderAmount): self
    {
        $this->orderAmount = $orderAmount;

        return $this;
    }


    // TCMSFieldDecimal
    public function getOrderPriceTotal(): string
    {
        return $this->orderPriceTotal;
    }

    public function setOrderPriceTotal(string $orderPriceTotal): self
    {
        $this->orderPriceTotal = $orderPriceTotal;

        return $this;
    }


    // TCMSFieldDecimal
    public function getOrderPriceAfterDiscounts(): string
    {
        return $this->orderPriceAfterDiscounts;
    }

    public function setOrderPriceAfterDiscounts(string $orderPriceAfterDiscounts): self
    {
        $this->orderPriceAfterDiscounts = $orderPriceAfterDiscounts;

        return $this;
    }


    // TCMSFieldDecimal
    public function getOrderTotalWeight(): string
    {
        return $this->orderTotalWeight;
    }

    public function setOrderTotalWeight(string $orderTotalWeight): self
    {
        $this->orderTotalWeight = $orderTotalWeight;

        return $this;
    }


    // TCMSFieldDecimal
    public function getOrderTotalVolume(): string
    {
        return $this->orderTotalVolume;
    }

    public function setOrderTotalVolume(string $orderTotalVolume): self
    {
        $this->orderTotalVolume = $orderTotalVolume;

        return $this;
    }


    // TCMSFieldDecimal
    public function getOrderPrice(): string
    {
        return $this->orderPrice;
    }

    public function setOrderPrice(string $orderPrice): self
    {
        $this->orderPrice = $orderPrice;

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
     * @return Collection<int, ShopOrderBundleArticle>
     */
    public function getShopOrderBundleArticleCollection(): Collection
    {
        return $this->shopOrderBundleArticleCollection;
    }

    public function addShopOrderBundleArticleCollection(ShopOrderBundleArticle $shopOrderBundleArticle): self
    {
        if (!$this->shopOrderBundleArticleCollection->contains($shopOrderBundleArticle)) {
            $this->shopOrderBundleArticleCollection->add($shopOrderBundleArticle);
            $shopOrderBundleArticle->setShopOrderItem($this);
        }

        return $this;
    }

    public function removeShopOrderBundleArticleCollection(ShopOrderBundleArticle $shopOrderBundleArticle): self
    {
        if ($this->shopOrderBundleArticleCollection->removeElement($shopOrderBundleArticle)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderBundleArticle->getShopOrderItem() === $this) {
                $shopOrderBundleArticle->setShopOrderItem(null);
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


}
