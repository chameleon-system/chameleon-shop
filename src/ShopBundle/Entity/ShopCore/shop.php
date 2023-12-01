<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderStatusCode;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopCurrency;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use ChameleonSystem\DataAccessBundle\Entity\Core\tCountry;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopBankAccount;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\ProductList\shopModuleArticlelistOrderby;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopVat;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopShippingGroup;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetSalutation;
use ChameleonSystem\DataAccessBundle\Entity\Core\dataCountry;
use ChameleonSystem\ShopAffiliateBundle\Entity\pkgShopAffiliate;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleImageSize;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopSystemInfo;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;
use ChameleonSystem\SearchBundle\Entity\shopSearchLog;
use ChameleonSystem\SearchBundle\Entity\shopSearchFieldWeight;
use ChameleonSystem\SearchBundle\Entity\shopSearchIgnoreWord;
use ChameleonSystem\SearchBundle\Entity\shopSearchKeywordArticle;
use ChameleonSystem\SearchBundle\Entity\shopSearchCache;
use ChameleonSystem\ShopBundle\Entity\Product\shopStockMessage;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopListfilter;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopFooterCategory;

class shop {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderStatusCode> - Available shipping status codes */
private Collection $ShopOrderStatusCodeCollection = new ArrayCollection()
, 
    // TCMSFieldExtendedLookup
/** @var pkgShopCurrency|null - Default currency */
private ?pkgShopCurrency $DefaultPkgShopCurrency = null
, 
    // TCMSFieldVarchar
/** @var string - Shop name */
private string $Name = '', 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, cmsPortal> - Belongs to these portals */
private Collection $CmsPortalCollection = new ArrayCollection()
, 
    // TCMSFieldExtendedLookup
/** @var shopCategory|null - Shop main category */
private ?shopCategory $ShopCategory = null
, 
    // TCMSFieldVarchar
/** @var string - Company name */
private string $AdrCompany = '', 
    // TCMSFieldVarchar
/** @var string - Company street */
private string $AdrStreet = '', 
    // TCMSFieldVarchar
/** @var string - Company zip code */
private string $AdrZip = '', 
    // TCMSFieldVarchar
/** @var string - Company city */
private string $AdrCity = '', 
    // TCMSFieldLookup
/** @var tCountry|null - Company country */
private ?tCountry $TCountry = null
, 
    // TCMSFieldVarchar
/** @var string - Telephone (customer service) */
private string $CustomerServiceTelephone = '', 
    // TCMSFieldEmail
/** @var string - Email (customer service) */
private string $CustomerServiceEmail = '', 
    // TCMSFieldVarchar
/** @var string - VAT registration number */
private string $Shopvatnumber = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopBankAccount> - Bank accounts */
private Collection $ShopBankAccountCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, dataExtranetUser> - Customers */
private Collection $DataExtranetUserCollection = new ArrayCollection()
, 
    // TCMSFieldNumber
/** @var int - Length of product history of an user */
private int $DataExtranetUserShopArticleHistoryMaxArticleCount = 20, 
    // TCMSFieldLookup
/** @var shopModuleArticlelistOrderby|null - Default sorting of items in the category view */
private ?shopModuleArticlelistOrderby $ShopModuleArticlelistOrderby = null
, 
    // TCMSFieldLookup
/** @var shopVat|null - Default VAT group */
private ?shopVat $ShopVat = null
, 
    // TCMSFieldLookup
/** @var shopShippingGroup|null - Default shipping group */
private ?shopShippingGroup $ShopShippingGroup = null
, 
    // TCMSFieldBoolean
/** @var bool - Make VAT of shipping costs dependent on basket contents */
private bool $ShippingVatDependsOnBasketContents = true, 
    // TCMSFieldLookup
/** @var dataExtranetSalutation|null - Default salutation */
private ?dataExtranetSalutation $DataExtranetSalutation = null
, 
    // TCMSFieldLookup
/** @var dataCountry|null - Default country */
private ?dataCountry $DataCountry = null
, 
    // TCMSFieldVarchar
/** @var string - Affiliate URL parameter */
private string $AffiliateParameterName = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopAffiliate> - Affiliate programs */
private Collection $PkgShopAffiliateCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticleImageSize> - Size of product images */
private Collection $ShopArticleImageSizeCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopSystemInfo> - Shop specific information / text blocks (e.g. Terms and Conditions) */
private Collection $ShopSystemInfoCollection = new ArrayCollection()
, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Replacement image */
private ?cmsMedia $NotFoundIm = null
, 
    // TCMSFieldDecimal
/** @var float - Weight bonus for whole words in search */
private float $ShopSearchWordBonus = 0, 
    // TCMSFieldDecimal
/** @var float - Weight of search word length */
private float $ShopSearchWordLengthFactor = 0.8, 
    // TCMSFieldDecimal
/** @var float - Deduction for words that only sound similar */
private float $ShopSearchSoundexPenalty = 0, 
    // TCMSFieldNumber
/** @var int - Shortest searchable partial word */
private int $ShopSearchMinIndexLength = 3, 
    // TCMSFieldNumber
/** @var int - Longest searchable partial word */
private int $ShopSearchMaxIndexLength = 10, 
    // TCMSFieldBoolean
/** @var bool - Connect search items with AND */
private bool $ShopSearchUseBooleanAnd = false, 
    // TCMSFieldNumber
/** @var int - Maximum age of search cache */
private int $MaxSearchCacheAgeInHours = 0, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopSearchLog> - Search log */
private Collection $ShopSearchLogCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopSearchFieldWeight> - Fields weight */
private Collection $ShopSearchFieldWeightCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopSearchIgnoreWord> - Words to be ignored in searches */
private Collection $ShopSearchIgnoreWordCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopSearchKeywordArticle> - Manually selected search results */
private Collection $ShopSearchKeywordArticleCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopSearchCache> - Search cache */
private Collection $ShopSearchCacheCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Name of the spot in the layouts containing the basket module */
private string $BasketSpotName = '', 
    // TCMSFieldVarchar
/** @var string - Name of the spot containing the central shop handler */
private string $ShopCentralHandlerSpotName = 'oShopCentralHandler', 
    // TCMSFieldBoolean
/** @var bool - Show empty categories in shop */
private bool $ShowEmptyCategories = true, 
    // TCMSFieldBoolean
/** @var bool - Variant parents can be purchased */
private bool $AllowPurchaseOfVariantParents = false, 
    // TCMSFieldBoolean
/** @var bool - Load inactive variants */
private bool $LoadInactiveVariants = false, 
    // TCMSFieldBoolean
/** @var bool - Synchronize profile address with billing address */
private bool $SyncProfileDataWithBillingData = false, 
    // TCMSFieldBoolean
/** @var bool - Is the user allowed to have more than one billing address? */
private bool $AllowMultipleBillingAddresses = true, 
    // TCMSFieldBoolean
/** @var bool - Is the user allowed to have more than one shipping address? */
private bool $AllowMultipleShippingAddresses = true, 
    // TCMSFieldBoolean
/** @var bool - Allow guest orders? */
private bool $AllowGuestPurchase = true, 
    // TCMSFieldBoolean
/** @var bool - Archive customers product recommendations */
private bool $LogArticleSuggestions = true, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopStockMessage> - Stock messages */
private Collection $ShopStockMessageCollection = new ArrayCollection()
, 
    // TCMSFieldText_ShowExportURL
/** @var string - Export key */
private string $ExportKey = '', 
    // TCMSFieldText
/** @var string - Basket info text */
private string $CartInfoText = '', 
    // TCMSFieldLookup
/** @var pkgShopListfilter|null - Results list filter */
private ?pkgShopListfilter $PkgShopListfilterPostsearch = null
, 
    // TCMSFieldBoolean
/** @var bool - If there are no results, refer to page "no results for product search" */
private bool $RedirectToNotFoundPageProductSearchOnNoResults = false, 
    // TCMSFieldBoolean
/** @var bool - Turn on search log */
private bool $UseShopSearchLog = true, 
    // TCMSFieldLookup
/** @var pkgShopListfilter|null - Category list filter for categories without subcategories */
private ?pkgShopListfilter $PkgShopListfilterCategoryFilter = null
, 
    // TCMSFieldNumber
/** @var int - Maximum size of cookie for item history (in KB) */
private int $DataExtranetUserShopArticleHistoryMaxCookieSize = 0, 
    // TCMSFieldOption
/** @var string - Use SEO-URLs for products */
private string $ProductUrlMode = 'V1', 
    // TCMSFieldNumber
/** @var int - Shipping delay (days) */
private int $ShopreviewmailMailDelay = 4, 
    // TCMSFieldDecimal
/** @var float - Recipients (percent) */
private float $ShopreviewmailPercentOfCustomers = 90, 
    // TCMSFieldBoolean
/** @var bool - For each order */
private bool $ShopreviewmailSendForEachOrder = true, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopFooterCategory> - Footer categories */
private Collection $PkgShopFooterCategoryCollection = new ArrayCollection()
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
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderStatusCode>
*/
public function getShopOrderStatusCodeCollection(): Collection
{
    return $this->ShopOrderStatusCodeCollection;
}

public function addShopOrderStatusCodeCollection(shopOrderStatusCode $ShopOrderStatusCode): self
{
    if (!$this->ShopOrderStatusCodeCollection->contains($ShopOrderStatusCode)) {
        $this->ShopOrderStatusCodeCollection->add($ShopOrderStatusCode);
        $ShopOrderStatusCode->setShop($this);
    }

    return $this;
}

public function removeShopOrderStatusCodeCollection(shopOrderStatusCode $ShopOrderStatusCode): self
{
    if ($this->ShopOrderStatusCodeCollection->removeElement($ShopOrderStatusCode)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderStatusCode->getShop() === $this) {
            $ShopOrderStatusCode->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getDefaultPkgShopCurrency(): ?pkgShopCurrency
{
    return $this->DefaultPkgShopCurrency;
}

public function setDefaultPkgShopCurrency(?pkgShopCurrency $DefaultPkgShopCurrency): self
{
    $this->DefaultPkgShopCurrency = $DefaultPkgShopCurrency;

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


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, cmsPortal>
*/
public function getCmsPortalCollection(): Collection
{
    return $this->CmsPortalCollection;
}

public function addCmsPortalCollection(cmsPortal $CmsPortalMlt): self
{
    if (!$this->CmsPortalCollection->contains($CmsPortalMlt)) {
        $this->CmsPortalCollection->add($CmsPortalMlt);
        $CmsPortalMlt->set($this);
    }

    return $this;
}

public function removeCmsPortalCollection(cmsPortal $CmsPortalMlt): self
{
    if ($this->CmsPortalCollection->removeElement($CmsPortalMlt)) {
        // set the owning side to null (unless already changed)
        if ($CmsPortalMlt->get() === $this) {
            $CmsPortalMlt->set(null);
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


  
    // TCMSFieldVarchar
public function getadrCompany(): string
{
    return $this->AdrCompany;
}
public function setadrCompany(string $AdrCompany): self
{
    $this->AdrCompany = $AdrCompany;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrStreet(): string
{
    return $this->AdrStreet;
}
public function setadrStreet(string $AdrStreet): self
{
    $this->AdrStreet = $AdrStreet;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrZip(): string
{
    return $this->AdrZip;
}
public function setadrZip(string $AdrZip): self
{
    $this->AdrZip = $AdrZip;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrCity(): string
{
    return $this->AdrCity;
}
public function setadrCity(string $AdrCity): self
{
    $this->AdrCity = $AdrCity;

    return $this;
}


  
    // TCMSFieldLookup
public function getTCountry(): ?tCountry
{
    return $this->TCountry;
}

public function setTCountry(?tCountry $TCountry): self
{
    $this->TCountry = $TCountry;

    return $this;
}


  
    // TCMSFieldVarchar
public function getcustomerServiceTelephone(): string
{
    return $this->CustomerServiceTelephone;
}
public function setcustomerServiceTelephone(string $CustomerServiceTelephone): self
{
    $this->CustomerServiceTelephone = $CustomerServiceTelephone;

    return $this;
}


  
    // TCMSFieldEmail
public function getcustomerServiceEmail(): string
{
    return $this->CustomerServiceEmail;
}
public function setcustomerServiceEmail(string $CustomerServiceEmail): self
{
    $this->CustomerServiceEmail = $CustomerServiceEmail;

    return $this;
}


  
    // TCMSFieldVarchar
public function getshopvatnumber(): string
{
    return $this->Shopvatnumber;
}
public function setshopvatnumber(string $Shopvatnumber): self
{
    $this->Shopvatnumber = $Shopvatnumber;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopBankAccount>
*/
public function getShopBankAccountCollection(): Collection
{
    return $this->ShopBankAccountCollection;
}

public function addShopBankAccountCollection(shopBankAccount $ShopBankAccount): self
{
    if (!$this->ShopBankAccountCollection->contains($ShopBankAccount)) {
        $this->ShopBankAccountCollection->add($ShopBankAccount);
        $ShopBankAccount->setShop($this);
    }

    return $this;
}

public function removeShopBankAccountCollection(shopBankAccount $ShopBankAccount): self
{
    if ($this->ShopBankAccountCollection->removeElement($ShopBankAccount)) {
        // set the owning side to null (unless already changed)
        if ($ShopBankAccount->getShop() === $this) {
            $ShopBankAccount->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, dataExtranetUser>
*/
public function getDataExtranetUserCollection(): Collection
{
    return $this->DataExtranetUserCollection;
}

public function addDataExtranetUserCollection(dataExtranetUser $DataExtranetUser): self
{
    if (!$this->DataExtranetUserCollection->contains($DataExtranetUser)) {
        $this->DataExtranetUserCollection->add($DataExtranetUser);
        $DataExtranetUser->setShop($this);
    }

    return $this;
}

public function removeDataExtranetUserCollection(dataExtranetUser $DataExtranetUser): self
{
    if ($this->DataExtranetUserCollection->removeElement($DataExtranetUser)) {
        // set the owning side to null (unless already changed)
        if ($DataExtranetUser->getShop() === $this) {
            $DataExtranetUser->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldNumber
public function getdataExtranetUserShopArticleHistoryMaxArticleCount(): int
{
    return $this->DataExtranetUserShopArticleHistoryMaxArticleCount;
}
public function setdataExtranetUserShopArticleHistoryMaxArticleCount(int $DataExtranetUserShopArticleHistoryMaxArticleCount): self
{
    $this->DataExtranetUserShopArticleHistoryMaxArticleCount = $DataExtranetUserShopArticleHistoryMaxArticleCount;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopModuleArticlelistOrderby(): ?shopModuleArticlelistOrderby
{
    return $this->ShopModuleArticlelistOrderby;
}

public function setShopModuleArticlelistOrderby(?shopModuleArticlelistOrderby $ShopModuleArticlelistOrderby): self
{
    $this->ShopModuleArticlelistOrderby = $ShopModuleArticlelistOrderby;

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


  
    // TCMSFieldLookup
public function getShopShippingGroup(): ?shopShippingGroup
{
    return $this->ShopShippingGroup;
}

public function setShopShippingGroup(?shopShippingGroup $ShopShippingGroup): self
{
    $this->ShopShippingGroup = $ShopShippingGroup;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshippingVatDependsOnBasketContents(): bool
{
    return $this->ShippingVatDependsOnBasketContents;
}
public function setshippingVatDependsOnBasketContents(bool $ShippingVatDependsOnBasketContents): self
{
    $this->ShippingVatDependsOnBasketContents = $ShippingVatDependsOnBasketContents;

    return $this;
}


  
    // TCMSFieldLookup
public function getDataExtranetSalutation(): ?dataExtranetSalutation
{
    return $this->DataExtranetSalutation;
}

public function setDataExtranetSalutation(?dataExtranetSalutation $DataExtranetSalutation): self
{
    $this->DataExtranetSalutation = $DataExtranetSalutation;

    return $this;
}


  
    // TCMSFieldLookup
public function getDataCountry(): ?dataCountry
{
    return $this->DataCountry;
}

public function setDataCountry(?dataCountry $DataCountry): self
{
    $this->DataCountry = $DataCountry;

    return $this;
}


  
    // TCMSFieldVarchar
public function getaffiliateParameterName(): string
{
    return $this->AffiliateParameterName;
}
public function setaffiliateParameterName(string $AffiliateParameterName): self
{
    $this->AffiliateParameterName = $AffiliateParameterName;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopAffiliate>
*/
public function getPkgShopAffiliateCollection(): Collection
{
    return $this->PkgShopAffiliateCollection;
}

public function addPkgShopAffiliateCollection(pkgShopAffiliate $PkgShopAffiliate): self
{
    if (!$this->PkgShopAffiliateCollection->contains($PkgShopAffiliate)) {
        $this->PkgShopAffiliateCollection->add($PkgShopAffiliate);
        $PkgShopAffiliate->setShop($this);
    }

    return $this;
}

public function removePkgShopAffiliateCollection(pkgShopAffiliate $PkgShopAffiliate): self
{
    if ($this->PkgShopAffiliateCollection->removeElement($PkgShopAffiliate)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopAffiliate->getShop() === $this) {
            $PkgShopAffiliate->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticleImageSize>
*/
public function getShopArticleImageSizeCollection(): Collection
{
    return $this->ShopArticleImageSizeCollection;
}

public function addShopArticleImageSizeCollection(shopArticleImageSize $ShopArticleImageSize): self
{
    if (!$this->ShopArticleImageSizeCollection->contains($ShopArticleImageSize)) {
        $this->ShopArticleImageSizeCollection->add($ShopArticleImageSize);
        $ShopArticleImageSize->setShop($this);
    }

    return $this;
}

public function removeShopArticleImageSizeCollection(shopArticleImageSize $ShopArticleImageSize): self
{
    if ($this->ShopArticleImageSizeCollection->removeElement($ShopArticleImageSize)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleImageSize->getShop() === $this) {
            $ShopArticleImageSize->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopSystemInfo>
*/
public function getShopSystemInfoCollection(): Collection
{
    return $this->ShopSystemInfoCollection;
}

public function addShopSystemInfoCollection(shopSystemInfo $ShopSystemInfo): self
{
    if (!$this->ShopSystemInfoCollection->contains($ShopSystemInfo)) {
        $this->ShopSystemInfoCollection->add($ShopSystemInfo);
        $ShopSystemInfo->setShop($this);
    }

    return $this;
}

public function removeShopSystemInfoCollection(shopSystemInfo $ShopSystemInfo): self
{
    if ($this->ShopSystemInfoCollection->removeElement($ShopSystemInfo)) {
        // set the owning side to null (unless already changed)
        if ($ShopSystemInfo->getShop() === $this) {
            $ShopSystemInfo->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getNotFoundIm(): ?cmsMedia
{
    return $this->NotFoundIm;
}

public function setNotFoundIm(?cmsMedia $NotFoundIm): self
{
    $this->NotFoundIm = $NotFoundIm;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopSearchWordBonus(): float
{
    return $this->ShopSearchWordBonus;
}
public function setshopSearchWordBonus(float $ShopSearchWordBonus): self
{
    $this->ShopSearchWordBonus = $ShopSearchWordBonus;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopSearchWordLengthFactor(): float
{
    return $this->ShopSearchWordLengthFactor;
}
public function setshopSearchWordLengthFactor(float $ShopSearchWordLengthFactor): self
{
    $this->ShopSearchWordLengthFactor = $ShopSearchWordLengthFactor;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopSearchSoundexPenalty(): float
{
    return $this->ShopSearchSoundexPenalty;
}
public function setshopSearchSoundexPenalty(float $ShopSearchSoundexPenalty): self
{
    $this->ShopSearchSoundexPenalty = $ShopSearchSoundexPenalty;

    return $this;
}


  
    // TCMSFieldNumber
public function getshopSearchMinIndexLength(): int
{
    return $this->ShopSearchMinIndexLength;
}
public function setshopSearchMinIndexLength(int $ShopSearchMinIndexLength): self
{
    $this->ShopSearchMinIndexLength = $ShopSearchMinIndexLength;

    return $this;
}


  
    // TCMSFieldNumber
public function getshopSearchMaxIndexLength(): int
{
    return $this->ShopSearchMaxIndexLength;
}
public function setshopSearchMaxIndexLength(int $ShopSearchMaxIndexLength): self
{
    $this->ShopSearchMaxIndexLength = $ShopSearchMaxIndexLength;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshopSearchUseBooleanAnd(): bool
{
    return $this->ShopSearchUseBooleanAnd;
}
public function setshopSearchUseBooleanAnd(bool $ShopSearchUseBooleanAnd): self
{
    $this->ShopSearchUseBooleanAnd = $ShopSearchUseBooleanAnd;

    return $this;
}


  
    // TCMSFieldNumber
public function getmaxSearchCacheAgeInHours(): int
{
    return $this->MaxSearchCacheAgeInHours;
}
public function setmaxSearchCacheAgeInHours(int $MaxSearchCacheAgeInHours): self
{
    $this->MaxSearchCacheAgeInHours = $MaxSearchCacheAgeInHours;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopSearchLog>
*/
public function getShopSearchLogCollection(): Collection
{
    return $this->ShopSearchLogCollection;
}

public function addShopSearchLogCollection(shopSearchLog $ShopSearchLog): self
{
    if (!$this->ShopSearchLogCollection->contains($ShopSearchLog)) {
        $this->ShopSearchLogCollection->add($ShopSearchLog);
        $ShopSearchLog->setShop($this);
    }

    return $this;
}

public function removeShopSearchLogCollection(shopSearchLog $ShopSearchLog): self
{
    if ($this->ShopSearchLogCollection->removeElement($ShopSearchLog)) {
        // set the owning side to null (unless already changed)
        if ($ShopSearchLog->getShop() === $this) {
            $ShopSearchLog->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopSearchFieldWeight>
*/
public function getShopSearchFieldWeightCollection(): Collection
{
    return $this->ShopSearchFieldWeightCollection;
}

public function addShopSearchFieldWeightCollection(shopSearchFieldWeight $ShopSearchFieldWeight): self
{
    if (!$this->ShopSearchFieldWeightCollection->contains($ShopSearchFieldWeight)) {
        $this->ShopSearchFieldWeightCollection->add($ShopSearchFieldWeight);
        $ShopSearchFieldWeight->setShop($this);
    }

    return $this;
}

public function removeShopSearchFieldWeightCollection(shopSearchFieldWeight $ShopSearchFieldWeight): self
{
    if ($this->ShopSearchFieldWeightCollection->removeElement($ShopSearchFieldWeight)) {
        // set the owning side to null (unless already changed)
        if ($ShopSearchFieldWeight->getShop() === $this) {
            $ShopSearchFieldWeight->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopSearchIgnoreWord>
*/
public function getShopSearchIgnoreWordCollection(): Collection
{
    return $this->ShopSearchIgnoreWordCollection;
}

public function addShopSearchIgnoreWordCollection(shopSearchIgnoreWord $ShopSearchIgnoreWord): self
{
    if (!$this->ShopSearchIgnoreWordCollection->contains($ShopSearchIgnoreWord)) {
        $this->ShopSearchIgnoreWordCollection->add($ShopSearchIgnoreWord);
        $ShopSearchIgnoreWord->setShop($this);
    }

    return $this;
}

public function removeShopSearchIgnoreWordCollection(shopSearchIgnoreWord $ShopSearchIgnoreWord): self
{
    if ($this->ShopSearchIgnoreWordCollection->removeElement($ShopSearchIgnoreWord)) {
        // set the owning side to null (unless already changed)
        if ($ShopSearchIgnoreWord->getShop() === $this) {
            $ShopSearchIgnoreWord->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopSearchKeywordArticle>
*/
public function getShopSearchKeywordArticleCollection(): Collection
{
    return $this->ShopSearchKeywordArticleCollection;
}

public function addShopSearchKeywordArticleCollection(shopSearchKeywordArticle $ShopSearchKeywordArticle): self
{
    if (!$this->ShopSearchKeywordArticleCollection->contains($ShopSearchKeywordArticle)) {
        $this->ShopSearchKeywordArticleCollection->add($ShopSearchKeywordArticle);
        $ShopSearchKeywordArticle->setShop($this);
    }

    return $this;
}

public function removeShopSearchKeywordArticleCollection(shopSearchKeywordArticle $ShopSearchKeywordArticle): self
{
    if ($this->ShopSearchKeywordArticleCollection->removeElement($ShopSearchKeywordArticle)) {
        // set the owning side to null (unless already changed)
        if ($ShopSearchKeywordArticle->getShop() === $this) {
            $ShopSearchKeywordArticle->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopSearchCache>
*/
public function getShopSearchCacheCollection(): Collection
{
    return $this->ShopSearchCacheCollection;
}

public function addShopSearchCacheCollection(shopSearchCache $ShopSearchCache): self
{
    if (!$this->ShopSearchCacheCollection->contains($ShopSearchCache)) {
        $this->ShopSearchCacheCollection->add($ShopSearchCache);
        $ShopSearchCache->setShop($this);
    }

    return $this;
}

public function removeShopSearchCacheCollection(shopSearchCache $ShopSearchCache): self
{
    if ($this->ShopSearchCacheCollection->removeElement($ShopSearchCache)) {
        // set the owning side to null (unless already changed)
        if ($ShopSearchCache->getShop() === $this) {
            $ShopSearchCache->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getbasketSpotName(): string
{
    return $this->BasketSpotName;
}
public function setbasketSpotName(string $BasketSpotName): self
{
    $this->BasketSpotName = $BasketSpotName;

    return $this;
}


  
    // TCMSFieldVarchar
public function getshopCentralHandlerSpotName(): string
{
    return $this->ShopCentralHandlerSpotName;
}
public function setshopCentralHandlerSpotName(string $ShopCentralHandlerSpotName): self
{
    $this->ShopCentralHandlerSpotName = $ShopCentralHandlerSpotName;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowEmptyCategories(): bool
{
    return $this->ShowEmptyCategories;
}
public function setshowEmptyCategories(bool $ShowEmptyCategories): self
{
    $this->ShowEmptyCategories = $ShowEmptyCategories;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowPurchaseOfVariantParents(): bool
{
    return $this->AllowPurchaseOfVariantParents;
}
public function setallowPurchaseOfVariantParents(bool $AllowPurchaseOfVariantParents): self
{
    $this->AllowPurchaseOfVariantParents = $AllowPurchaseOfVariantParents;

    return $this;
}


  
    // TCMSFieldBoolean
public function isloadInactiveVariants(): bool
{
    return $this->LoadInactiveVariants;
}
public function setloadInactiveVariants(bool $LoadInactiveVariants): self
{
    $this->LoadInactiveVariants = $LoadInactiveVariants;

    return $this;
}


  
    // TCMSFieldBoolean
public function issyncProfileDataWithBillingData(): bool
{
    return $this->SyncProfileDataWithBillingData;
}
public function setsyncProfileDataWithBillingData(bool $SyncProfileDataWithBillingData): self
{
    $this->SyncProfileDataWithBillingData = $SyncProfileDataWithBillingData;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowMultipleBillingAddresses(): bool
{
    return $this->AllowMultipleBillingAddresses;
}
public function setallowMultipleBillingAddresses(bool $AllowMultipleBillingAddresses): self
{
    $this->AllowMultipleBillingAddresses = $AllowMultipleBillingAddresses;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowMultipleShippingAddresses(): bool
{
    return $this->AllowMultipleShippingAddresses;
}
public function setallowMultipleShippingAddresses(bool $AllowMultipleShippingAddresses): self
{
    $this->AllowMultipleShippingAddresses = $AllowMultipleShippingAddresses;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowGuestPurchase(): bool
{
    return $this->AllowGuestPurchase;
}
public function setallowGuestPurchase(bool $AllowGuestPurchase): self
{
    $this->AllowGuestPurchase = $AllowGuestPurchase;

    return $this;
}


  
    // TCMSFieldBoolean
public function islogArticleSuggestions(): bool
{
    return $this->LogArticleSuggestions;
}
public function setlogArticleSuggestions(bool $LogArticleSuggestions): self
{
    $this->LogArticleSuggestions = $LogArticleSuggestions;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopStockMessage>
*/
public function getShopStockMessageCollection(): Collection
{
    return $this->ShopStockMessageCollection;
}

public function addShopStockMessageCollection(shopStockMessage $ShopStockMessage): self
{
    if (!$this->ShopStockMessageCollection->contains($ShopStockMessage)) {
        $this->ShopStockMessageCollection->add($ShopStockMessage);
        $ShopStockMessage->setShop($this);
    }

    return $this;
}

public function removeShopStockMessageCollection(shopStockMessage $ShopStockMessage): self
{
    if ($this->ShopStockMessageCollection->removeElement($ShopStockMessage)) {
        // set the owning side to null (unless already changed)
        if ($ShopStockMessage->getShop() === $this) {
            $ShopStockMessage->setShop(null);
        }
    }

    return $this;
}


  
    // TCMSFieldText_ShowExportURL
public function getexportKey(): string
{
    return $this->ExportKey;
}
public function setexportKey(string $ExportKey): self
{
    $this->ExportKey = $ExportKey;

    return $this;
}


  
    // TCMSFieldText
public function getcartInfoText(): string
{
    return $this->CartInfoText;
}
public function setcartInfoText(string $CartInfoText): self
{
    $this->CartInfoText = $CartInfoText;

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopListfilterPostsearch(): ?pkgShopListfilter
{
    return $this->PkgShopListfilterPostsearch;
}

public function setPkgShopListfilterPostsearch(?pkgShopListfilter $PkgShopListfilterPostsearch): self
{
    $this->PkgShopListfilterPostsearch = $PkgShopListfilterPostsearch;

    return $this;
}


  
    // TCMSFieldBoolean
public function isredirectToNotFoundPageProductSearchOnNoResults(): bool
{
    return $this->RedirectToNotFoundPageProductSearchOnNoResults;
}
public function setredirectToNotFoundPageProductSearchOnNoResults(bool $RedirectToNotFoundPageProductSearchOnNoResults): self
{
    $this->RedirectToNotFoundPageProductSearchOnNoResults = $RedirectToNotFoundPageProductSearchOnNoResults;

    return $this;
}


  
    // TCMSFieldBoolean
public function isuseShopSearchLog(): bool
{
    return $this->UseShopSearchLog;
}
public function setuseShopSearchLog(bool $UseShopSearchLog): self
{
    $this->UseShopSearchLog = $UseShopSearchLog;

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopListfilterCategoryFilter(): ?pkgShopListfilter
{
    return $this->PkgShopListfilterCategoryFilter;
}

public function setPkgShopListfilterCategoryFilter(?pkgShopListfilter $PkgShopListfilterCategoryFilter): self
{
    $this->PkgShopListfilterCategoryFilter = $PkgShopListfilterCategoryFilter;

    return $this;
}


  
    // TCMSFieldNumber
public function getdataExtranetUserShopArticleHistoryMaxCookieSize(): int
{
    return $this->DataExtranetUserShopArticleHistoryMaxCookieSize;
}
public function setdataExtranetUserShopArticleHistoryMaxCookieSize(int $DataExtranetUserShopArticleHistoryMaxCookieSize): self
{
    $this->DataExtranetUserShopArticleHistoryMaxCookieSize = $DataExtranetUserShopArticleHistoryMaxCookieSize;

    return $this;
}


  
    // TCMSFieldOption
public function getproductUrlMode(): string
{
    return $this->ProductUrlMode;
}
public function setproductUrlMode(string $ProductUrlMode): self
{
    $this->ProductUrlMode = $ProductUrlMode;

    return $this;
}


  
    // TCMSFieldNumber
public function getshopreviewmailMailDelay(): int
{
    return $this->ShopreviewmailMailDelay;
}
public function setshopreviewmailMailDelay(int $ShopreviewmailMailDelay): self
{
    $this->ShopreviewmailMailDelay = $ShopreviewmailMailDelay;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopreviewmailPercentOfCustomers(): float
{
    return $this->ShopreviewmailPercentOfCustomers;
}
public function setshopreviewmailPercentOfCustomers(float $ShopreviewmailPercentOfCustomers): self
{
    $this->ShopreviewmailPercentOfCustomers = $ShopreviewmailPercentOfCustomers;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshopreviewmailSendForEachOrder(): bool
{
    return $this->ShopreviewmailSendForEachOrder;
}
public function setshopreviewmailSendForEachOrder(bool $ShopreviewmailSendForEachOrder): self
{
    $this->ShopreviewmailSendForEachOrder = $ShopreviewmailSendForEachOrder;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopFooterCategory>
*/
public function getPkgShopFooterCategoryCollection(): Collection
{
    return $this->PkgShopFooterCategoryCollection;
}

public function addPkgShopFooterCategoryCollection(pkgShopFooterCategory $PkgShopFooterCategory): self
{
    if (!$this->PkgShopFooterCategoryCollection->contains($PkgShopFooterCategory)) {
        $this->PkgShopFooterCategoryCollection->add($PkgShopFooterCategory);
        $PkgShopFooterCategory->setShop($this);
    }

    return $this;
}

public function removePkgShopFooterCategoryCollection(pkgShopFooterCategory $PkgShopFooterCategory): self
{
    if ($this->PkgShopFooterCategoryCollection->removeElement($PkgShopFooterCategory)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopFooterCategory->getShop() === $this) {
            $PkgShopFooterCategory->setShop(null);
        }
    }

    return $this;
}


  
}
