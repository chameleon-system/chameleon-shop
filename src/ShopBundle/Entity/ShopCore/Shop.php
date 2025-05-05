<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\Core\DataCountry;
use ChameleonSystem\DataAccessBundle\Entity\Core\TCountry;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetSalutation;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\SearchBundle\Entity\ShopSearchCache;
use ChameleonSystem\SearchBundle\Entity\ShopSearchFieldWeight;
use ChameleonSystem\SearchBundle\Entity\ShopSearchIgnoreWord;
use ChameleonSystem\SearchBundle\Entity\ShopSearchKeywordArticle;
use ChameleonSystem\SearchBundle\Entity\ShopSearchLog;
use ChameleonSystem\ShopAffiliateBundle\Entity\PkgShopAffiliate;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticleImageSize;
use ChameleonSystem\ShopBundle\Entity\Product\ShopStockMessage;
use ChameleonSystem\ShopBundle\Entity\ProductList\ShopModuleArticlelistOrderby;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\ShopOrderStatusCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Shop
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderStatusCode> - Available shipping status codes */
        private Collection $shopOrderStatusCodeCollection = new ArrayCollection(),
        // TCMSFieldExtendedLookup
        /** @var PkgShopCurrency|null - Default currency */
        private ?PkgShopCurrency $defaultPkgShopCurrency = null,
        // TCMSFieldVarchar
        /** @var string - Shop name */
        private string $name = '',
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, CmsPortal> - Belongs to these portals */
        private Collection $cmsPortalCollection = new ArrayCollection(),
        // TCMSFieldExtendedLookup
        /** @var ShopCategory|null - Shop main category */
        private ?ShopCategory $shopCategory = null,
        // TCMSFieldVarchar
        /** @var string - Company name */
        private string $adrCompany = '',
        // TCMSFieldVarchar
        /** @var string - Company street */
        private string $adrStreet = '',
        // TCMSFieldVarchar
        /** @var string - Company zip code */
        private string $adrZip = '',
        // TCMSFieldVarchar
        /** @var string - Company city */
        private string $adrCity = '',
        // TCMSFieldLookup
        /** @var TCountry|null - Company country */
        private ?TCountry $tCountry = null,
        // TCMSFieldVarchar
        /** @var string - Telephone (customer service) */
        private string $customerServiceTelephone = '',
        // TCMSFieldEmail
        /** @var string - Email (customer service) */
        private string $customerServiceEmail = '',
        // TCMSFieldVarchar
        /** @var string - VAT registration number */
        private string $shopvatnumber = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopBankAccount> - Bank accounts */
        private Collection $shopBankAccountCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, DataExtranetUser> - Customers */
        private Collection $dataExtranetUserCollection = new ArrayCollection(),
        // TCMSFieldNumber
        /** @var int - Length of product history of an user */
        private int $dataExtranetUserShopArticleHistoryMaxArticleCount = 20,
        // TCMSFieldLookup
        /** @var ShopModuleArticlelistOrderby|null - Default sorting of items in the category view */
        private ?ShopModuleArticlelistOrderby $shopModuleArticlelistOrderby = null,
        // TCMSFieldLookup
        /** @var ShopVat|null - Default VAT group */
        private ?ShopVat $shopVat = null,
        // TCMSFieldLookup
        /** @var ShopShippingGroup|null - Default shipping group */
        private ?ShopShippingGroup $shopShippingGroup = null,
        // TCMSFieldBoolean
        /** @var bool - Make VAT of shipping costs dependent on basket contents */
        private bool $shippingVatDependsOnBasketContents = true,
        // TCMSFieldLookup
        /** @var DataExtranetSalutation|null - Default salutation */
        private ?DataExtranetSalutation $dataExtranetSalutation = null,
        // TCMSFieldLookup
        /** @var DataCountry|null - Default country */
        private ?DataCountry $dataCountry = null,
        // TCMSFieldVarchar
        /** @var string - Affiliate URL parameter */
        private string $affiliateParameterName = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopAffiliate> - Affiliate programs */
        private Collection $pkgShopAffiliateCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticleImageSize> - Size of product images */
        private Collection $shopArticleImageSizeCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopSystemInfo> - Shop specific information / text blocks (e.g. Terms and Conditions) */
        private Collection $shopSystemInfoCollection = new ArrayCollection(),
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Replacement image */
        private ?CmsMedia $notFoundImage = null,
        // TCMSFieldDecimal
        /** @var string - Weight bonus for whole words in search */
        private string $shopSearchWordBonus = '',
        // TCMSFieldDecimal
        /** @var string - Weight of search word length */
        private string $shopSearchWordLengthFactor = '0.8',
        // TCMSFieldDecimal
        /** @var string - Deduction for words that only sound similar */
        private string $shopSearchSoundexPenalty = '',
        // TCMSFieldNumber
        /** @var int - Shortest searchable partial word */
        private int $shopSearchMinIndexLength = 3,
        // TCMSFieldNumber
        /** @var int - Longest searchable partial word */
        private int $shopSearchMaxIndexLength = 10,
        // TCMSFieldBoolean
        /** @var bool - Connect search items with AND */
        private bool $shopSearchUseBooleanAnd = false,
        // TCMSFieldNumber
        /** @var int - Maximum age of search cache */
        private int $maxSearchCacheAgeInHours = 0,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopSearchLog> - Search log */
        private Collection $shopSearchLogCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopSearchFieldWeight> - Fields weight */
        private Collection $shopSearchFieldWeightCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopSearchIgnoreWord> - Words to be ignored in searches */
        private Collection $shopSearchIgnoreWordCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopSearchKeywordArticle> - Manually selected search results */
        private Collection $shopSearchKeywordArticleCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopSearchCache> - Search cache */
        private Collection $shopSearchCacheCollection = new ArrayCollection(),
        // TCMSFieldVarchar
        /** @var string - Name of the spot in the layouts containing the basket module */
        private string $basketSpotName = '',
        // TCMSFieldVarchar
        /** @var string - Name of the spot containing the central shop handler */
        private string $shopCentralHandlerSpotName = 'oShopCentralHandler',
        // TCMSFieldBoolean
        /** @var bool - Show empty categories in shop */
        private bool $showEmptyCategories = true,
        // TCMSFieldBoolean
        /** @var bool - Variant parents can be purchased */
        private bool $allowPurchaseOfVariantParents = false,
        // TCMSFieldBoolean
        /** @var bool - Load inactive variants */
        private bool $loadInactiveVariants = false,
        // TCMSFieldBoolean
        /** @var bool - Synchronize profile address with billing address */
        private bool $syncProfileDataWithBillingData = false,
        // TCMSFieldBoolean
        /** @var bool - Is the user allowed to have more than one billing address? */
        private bool $allowMultipleBillingAddresses = true,
        // TCMSFieldBoolean
        /** @var bool - Is the user allowed to have more than one shipping address? */
        private bool $allowMultipleShippingAddresses = true,
        // TCMSFieldBoolean
        /** @var bool - Allow guest orders? */
        private bool $allowGuestPurchase = true,
        // TCMSFieldBoolean
        /** @var bool - Archive customers product recommendations */
        private bool $logArticleSuggestions = true,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopStockMessage> - Stock messages */
        private Collection $shopStockMessageCollection = new ArrayCollection(),
        // TCMSFieldText_ShowExportURL
        /** @var string - Export key */
        private string $exportKey = '',
        // TCMSFieldText
        /** @var string - Basket info text */
        private string $cartInfoText = '',
        // TCMSFieldLookup
        /** @var PkgShopListfilter|null - Results list filter */
        private ?PkgShopListfilter $pkgShopListfilterPostsearch = null,
        // TCMSFieldBoolean
        /** @var bool - If there are no results, refer to page "no results for product search" */
        private bool $redirectToNotFoundPageProductSearchOnNoResults = false,
        // TCMSFieldBoolean
        /** @var bool - Turn on search log */
        private bool $useShopSearchLog = true,
        // TCMSFieldLookup
        /** @var PkgShopListfilter|null - Category list filter for categories without subcategories */
        private ?PkgShopListfilter $pkgShopListfilterCategoryFilter = null,
        // TCMSFieldNumber
        /** @var int - Maximum size of cookie for item history (in KB) */
        private int $dataExtranetUserShopArticleHistoryMaxCookieSize = 0,
        // TCMSFieldOption
        /** @var string - Use SEO-URLs for products */
        private string $productUrlMode = 'V1',
        // TCMSFieldNumber
        /** @var int - Shipping delay (days) */
        private int $shopreviewmailMailDelay = 4,
        // TCMSFieldDecimal
        /** @var string - Recipients (percent) */
        private string $shopreviewmailPercentOfCustomers = '90',
        // TCMSFieldBoolean
        /** @var bool - For each order */
        private bool $shopreviewmailSendForEachOrder = true,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopFooterCategory> - Footer categories */
        private Collection $pkgShopFooterCategoryCollection = new ArrayCollection()
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
    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderStatusCode>
     */
    public function getShopOrderStatusCodeCollection(): Collection
    {
        return $this->shopOrderStatusCodeCollection;
    }

    public function addShopOrderStatusCodeCollection(ShopOrderStatusCode $shopOrderStatusCode): self
    {
        if (!$this->shopOrderStatusCodeCollection->contains($shopOrderStatusCode)) {
            $this->shopOrderStatusCodeCollection->add($shopOrderStatusCode);
            $shopOrderStatusCode->setShop($this);
        }

        return $this;
    }

    public function removeShopOrderStatusCodeCollection(ShopOrderStatusCode $shopOrderStatusCode): self
    {
        if ($this->shopOrderStatusCodeCollection->removeElement($shopOrderStatusCode)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderStatusCode->getShop() === $this) {
                $shopOrderStatusCode->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getDefaultPkgShopCurrency(): ?PkgShopCurrency
    {
        return $this->defaultPkgShopCurrency;
    }

    public function setDefaultPkgShopCurrency(?PkgShopCurrency $defaultPkgShopCurrency): self
    {
        $this->defaultPkgShopCurrency = $defaultPkgShopCurrency;

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

    // TCMSFieldVarchar
    public function getAdrCompany(): string
    {
        return $this->adrCompany;
    }

    public function setAdrCompany(string $adrCompany): self
    {
        $this->adrCompany = $adrCompany;

        return $this;
    }

    // TCMSFieldVarchar
    public function getAdrStreet(): string
    {
        return $this->adrStreet;
    }

    public function setAdrStreet(string $adrStreet): self
    {
        $this->adrStreet = $adrStreet;

        return $this;
    }

    // TCMSFieldVarchar
    public function getAdrZip(): string
    {
        return $this->adrZip;
    }

    public function setAdrZip(string $adrZip): self
    {
        $this->adrZip = $adrZip;

        return $this;
    }

    // TCMSFieldVarchar
    public function getAdrCity(): string
    {
        return $this->adrCity;
    }

    public function setAdrCity(string $adrCity): self
    {
        $this->adrCity = $adrCity;

        return $this;
    }

    // TCMSFieldLookup
    public function getTCountry(): ?TCountry
    {
        return $this->tCountry;
    }

    public function setTCountry(?TCountry $tCountry): self
    {
        $this->tCountry = $tCountry;

        return $this;
    }

    // TCMSFieldVarchar
    public function getCustomerServiceTelephone(): string
    {
        return $this->customerServiceTelephone;
    }

    public function setCustomerServiceTelephone(string $customerServiceTelephone): self
    {
        $this->customerServiceTelephone = $customerServiceTelephone;

        return $this;
    }

    // TCMSFieldEmail
    public function getCustomerServiceEmail(): string
    {
        return $this->customerServiceEmail;
    }

    public function setCustomerServiceEmail(string $customerServiceEmail): self
    {
        $this->customerServiceEmail = $customerServiceEmail;

        return $this;
    }

    // TCMSFieldVarchar
    public function getShopvatnumber(): string
    {
        return $this->shopvatnumber;
    }

    public function setShopvatnumber(string $shopvatnumber): self
    {
        $this->shopvatnumber = $shopvatnumber;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopBankAccount>
     */
    public function getShopBankAccountCollection(): Collection
    {
        return $this->shopBankAccountCollection;
    }

    public function addShopBankAccountCollection(ShopBankAccount $shopBankAccount): self
    {
        if (!$this->shopBankAccountCollection->contains($shopBankAccount)) {
            $this->shopBankAccountCollection->add($shopBankAccount);
            $shopBankAccount->setShop($this);
        }

        return $this;
    }

    public function removeShopBankAccountCollection(ShopBankAccount $shopBankAccount): self
    {
        if ($this->shopBankAccountCollection->removeElement($shopBankAccount)) {
            // set the owning side to null (unless already changed)
            if ($shopBankAccount->getShop() === $this) {
                $shopBankAccount->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, DataExtranetUser>
     */
    public function getDataExtranetUserCollection(): Collection
    {
        return $this->dataExtranetUserCollection;
    }

    public function addDataExtranetUserCollection(DataExtranetUser $dataExtranetUser): self
    {
        if (!$this->dataExtranetUserCollection->contains($dataExtranetUser)) {
            $this->dataExtranetUserCollection->add($dataExtranetUser);
            $dataExtranetUser->setShop($this);
        }

        return $this;
    }

    public function removeDataExtranetUserCollection(DataExtranetUser $dataExtranetUser): self
    {
        if ($this->dataExtranetUserCollection->removeElement($dataExtranetUser)) {
            // set the owning side to null (unless already changed)
            if ($dataExtranetUser->getShop() === $this) {
                $dataExtranetUser->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldNumber
    public function getDataExtranetUserShopArticleHistoryMaxArticleCount(): int
    {
        return $this->dataExtranetUserShopArticleHistoryMaxArticleCount;
    }

    public function setDataExtranetUserShopArticleHistoryMaxArticleCount(
        int $dataExtranetUserShopArticleHistoryMaxArticleCount
    ): self {
        $this->dataExtranetUserShopArticleHistoryMaxArticleCount = $dataExtranetUserShopArticleHistoryMaxArticleCount;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopModuleArticlelistOrderby(): ?ShopModuleArticlelistOrderby
    {
        return $this->shopModuleArticlelistOrderby;
    }

    public function setShopModuleArticlelistOrderby(?ShopModuleArticlelistOrderby $shopModuleArticlelistOrderby): self
    {
        $this->shopModuleArticlelistOrderby = $shopModuleArticlelistOrderby;

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

    // TCMSFieldLookup
    public function getShopShippingGroup(): ?ShopShippingGroup
    {
        return $this->shopShippingGroup;
    }

    public function setShopShippingGroup(?ShopShippingGroup $shopShippingGroup): self
    {
        $this->shopShippingGroup = $shopShippingGroup;

        return $this;
    }

    // TCMSFieldBoolean
    public function isShippingVatDependsOnBasketContents(): bool
    {
        return $this->shippingVatDependsOnBasketContents;
    }

    public function setShippingVatDependsOnBasketContents(bool $shippingVatDependsOnBasketContents): self
    {
        $this->shippingVatDependsOnBasketContents = $shippingVatDependsOnBasketContents;

        return $this;
    }

    // TCMSFieldLookup
    public function getDataExtranetSalutation(): ?DataExtranetSalutation
    {
        return $this->dataExtranetSalutation;
    }

    public function setDataExtranetSalutation(?DataExtranetSalutation $dataExtranetSalutation): self
    {
        $this->dataExtranetSalutation = $dataExtranetSalutation;

        return $this;
    }

    // TCMSFieldLookup
    public function getDataCountry(): ?DataCountry
    {
        return $this->dataCountry;
    }

    public function setDataCountry(?DataCountry $dataCountry): self
    {
        $this->dataCountry = $dataCountry;

        return $this;
    }

    // TCMSFieldVarchar
    public function getAffiliateParameterName(): string
    {
        return $this->affiliateParameterName;
    }

    public function setAffiliateParameterName(string $affiliateParameterName): self
    {
        $this->affiliateParameterName = $affiliateParameterName;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopAffiliate>
     */
    public function getPkgShopAffiliateCollection(): Collection
    {
        return $this->pkgShopAffiliateCollection;
    }

    public function addPkgShopAffiliateCollection(PkgShopAffiliate $pkgShopAffiliate): self
    {
        if (!$this->pkgShopAffiliateCollection->contains($pkgShopAffiliate)) {
            $this->pkgShopAffiliateCollection->add($pkgShopAffiliate);
            $pkgShopAffiliate->setShop($this);
        }

        return $this;
    }

    public function removePkgShopAffiliateCollection(PkgShopAffiliate $pkgShopAffiliate): self
    {
        if ($this->pkgShopAffiliateCollection->removeElement($pkgShopAffiliate)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopAffiliate->getShop() === $this) {
                $pkgShopAffiliate->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopArticleImageSize>
     */
    public function getShopArticleImageSizeCollection(): Collection
    {
        return $this->shopArticleImageSizeCollection;
    }

    public function addShopArticleImageSizeCollection(ShopArticleImageSize $shopArticleImageSize): self
    {
        if (!$this->shopArticleImageSizeCollection->contains($shopArticleImageSize)) {
            $this->shopArticleImageSizeCollection->add($shopArticleImageSize);
            $shopArticleImageSize->setShop($this);
        }

        return $this;
    }

    public function removeShopArticleImageSizeCollection(ShopArticleImageSize $shopArticleImageSize): self
    {
        if ($this->shopArticleImageSizeCollection->removeElement($shopArticleImageSize)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleImageSize->getShop() === $this) {
                $shopArticleImageSize->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopSystemInfo>
     */
    public function getShopSystemInfoCollection(): Collection
    {
        return $this->shopSystemInfoCollection;
    }

    public function addShopSystemInfoCollection(ShopSystemInfo $shopSystemInfo): self
    {
        if (!$this->shopSystemInfoCollection->contains($shopSystemInfo)) {
            $this->shopSystemInfoCollection->add($shopSystemInfo);
            $shopSystemInfo->setShop($this);
        }

        return $this;
    }

    public function removeShopSystemInfoCollection(ShopSystemInfo $shopSystemInfo): self
    {
        if ($this->shopSystemInfoCollection->removeElement($shopSystemInfo)) {
            // set the owning side to null (unless already changed)
            if ($shopSystemInfo->getShop() === $this) {
                $shopSystemInfo->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldExtendedLookupMedia
    public function getNotFoundImage(): ?CmsMedia
    {
        return $this->notFoundImage;
    }

    public function setNotFoundImage(?CmsMedia $notFoundImage): self
    {
        $this->notFoundImage = $notFoundImage;

        return $this;
    }

    // TCMSFieldDecimal
    public function getShopSearchWordBonus(): string
    {
        return $this->shopSearchWordBonus;
    }

    public function setShopSearchWordBonus(string $shopSearchWordBonus): self
    {
        $this->shopSearchWordBonus = $shopSearchWordBonus;

        return $this;
    }

    // TCMSFieldDecimal
    public function getShopSearchWordLengthFactor(): string
    {
        return $this->shopSearchWordLengthFactor;
    }

    public function setShopSearchWordLengthFactor(string $shopSearchWordLengthFactor): self
    {
        $this->shopSearchWordLengthFactor = $shopSearchWordLengthFactor;

        return $this;
    }

    // TCMSFieldDecimal
    public function getShopSearchSoundexPenalty(): string
    {
        return $this->shopSearchSoundexPenalty;
    }

    public function setShopSearchSoundexPenalty(string $shopSearchSoundexPenalty): self
    {
        $this->shopSearchSoundexPenalty = $shopSearchSoundexPenalty;

        return $this;
    }

    // TCMSFieldNumber
    public function getShopSearchMinIndexLength(): int
    {
        return $this->shopSearchMinIndexLength;
    }

    public function setShopSearchMinIndexLength(int $shopSearchMinIndexLength): self
    {
        $this->shopSearchMinIndexLength = $shopSearchMinIndexLength;

        return $this;
    }

    // TCMSFieldNumber
    public function getShopSearchMaxIndexLength(): int
    {
        return $this->shopSearchMaxIndexLength;
    }

    public function setShopSearchMaxIndexLength(int $shopSearchMaxIndexLength): self
    {
        $this->shopSearchMaxIndexLength = $shopSearchMaxIndexLength;

        return $this;
    }

    // TCMSFieldBoolean
    public function isShopSearchUseBooleanAnd(): bool
    {
        return $this->shopSearchUseBooleanAnd;
    }

    public function setShopSearchUseBooleanAnd(bool $shopSearchUseBooleanAnd): self
    {
        $this->shopSearchUseBooleanAnd = $shopSearchUseBooleanAnd;

        return $this;
    }

    // TCMSFieldNumber
    public function getMaxSearchCacheAgeInHours(): int
    {
        return $this->maxSearchCacheAgeInHours;
    }

    public function setMaxSearchCacheAgeInHours(int $maxSearchCacheAgeInHours): self
    {
        $this->maxSearchCacheAgeInHours = $maxSearchCacheAgeInHours;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopSearchLog>
     */
    public function getShopSearchLogCollection(): Collection
    {
        return $this->shopSearchLogCollection;
    }

    public function addShopSearchLogCollection(ShopSearchLog $shopSearchLog): self
    {
        if (!$this->shopSearchLogCollection->contains($shopSearchLog)) {
            $this->shopSearchLogCollection->add($shopSearchLog);
            $shopSearchLog->setShop($this);
        }

        return $this;
    }

    public function removeShopSearchLogCollection(ShopSearchLog $shopSearchLog): self
    {
        if ($this->shopSearchLogCollection->removeElement($shopSearchLog)) {
            // set the owning side to null (unless already changed)
            if ($shopSearchLog->getShop() === $this) {
                $shopSearchLog->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopSearchFieldWeight>
     */
    public function getShopSearchFieldWeightCollection(): Collection
    {
        return $this->shopSearchFieldWeightCollection;
    }

    public function addShopSearchFieldWeightCollection(ShopSearchFieldWeight $shopSearchFieldWeight): self
    {
        if (!$this->shopSearchFieldWeightCollection->contains($shopSearchFieldWeight)) {
            $this->shopSearchFieldWeightCollection->add($shopSearchFieldWeight);
            $shopSearchFieldWeight->setShop($this);
        }

        return $this;
    }

    public function removeShopSearchFieldWeightCollection(ShopSearchFieldWeight $shopSearchFieldWeight): self
    {
        if ($this->shopSearchFieldWeightCollection->removeElement($shopSearchFieldWeight)) {
            // set the owning side to null (unless already changed)
            if ($shopSearchFieldWeight->getShop() === $this) {
                $shopSearchFieldWeight->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopSearchIgnoreWord>
     */
    public function getShopSearchIgnoreWordCollection(): Collection
    {
        return $this->shopSearchIgnoreWordCollection;
    }

    public function addShopSearchIgnoreWordCollection(ShopSearchIgnoreWord $shopSearchIgnoreWord): self
    {
        if (!$this->shopSearchIgnoreWordCollection->contains($shopSearchIgnoreWord)) {
            $this->shopSearchIgnoreWordCollection->add($shopSearchIgnoreWord);
            $shopSearchIgnoreWord->setShop($this);
        }

        return $this;
    }

    public function removeShopSearchIgnoreWordCollection(ShopSearchIgnoreWord $shopSearchIgnoreWord): self
    {
        if ($this->shopSearchIgnoreWordCollection->removeElement($shopSearchIgnoreWord)) {
            // set the owning side to null (unless already changed)
            if ($shopSearchIgnoreWord->getShop() === $this) {
                $shopSearchIgnoreWord->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopSearchKeywordArticle>
     */
    public function getShopSearchKeywordArticleCollection(): Collection
    {
        return $this->shopSearchKeywordArticleCollection;
    }

    public function addShopSearchKeywordArticleCollection(ShopSearchKeywordArticle $shopSearchKeywordArticle): self
    {
        if (!$this->shopSearchKeywordArticleCollection->contains($shopSearchKeywordArticle)) {
            $this->shopSearchKeywordArticleCollection->add($shopSearchKeywordArticle);
            $shopSearchKeywordArticle->setShop($this);
        }

        return $this;
    }

    public function removeShopSearchKeywordArticleCollection(ShopSearchKeywordArticle $shopSearchKeywordArticle): self
    {
        if ($this->shopSearchKeywordArticleCollection->removeElement($shopSearchKeywordArticle)) {
            // set the owning side to null (unless already changed)
            if ($shopSearchKeywordArticle->getShop() === $this) {
                $shopSearchKeywordArticle->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopSearchCache>
     */
    public function getShopSearchCacheCollection(): Collection
    {
        return $this->shopSearchCacheCollection;
    }

    public function addShopSearchCacheCollection(ShopSearchCache $shopSearchCache): self
    {
        if (!$this->shopSearchCacheCollection->contains($shopSearchCache)) {
            $this->shopSearchCacheCollection->add($shopSearchCache);
            $shopSearchCache->setShop($this);
        }

        return $this;
    }

    public function removeShopSearchCacheCollection(ShopSearchCache $shopSearchCache): self
    {
        if ($this->shopSearchCacheCollection->removeElement($shopSearchCache)) {
            // set the owning side to null (unless already changed)
            if ($shopSearchCache->getShop() === $this) {
                $shopSearchCache->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldVarchar
    public function getBasketSpotName(): string
    {
        return $this->basketSpotName;
    }

    public function setBasketSpotName(string $basketSpotName): self
    {
        $this->basketSpotName = $basketSpotName;

        return $this;
    }

    // TCMSFieldVarchar
    public function getShopCentralHandlerSpotName(): string
    {
        return $this->shopCentralHandlerSpotName;
    }

    public function setShopCentralHandlerSpotName(string $shopCentralHandlerSpotName): self
    {
        $this->shopCentralHandlerSpotName = $shopCentralHandlerSpotName;

        return $this;
    }

    // TCMSFieldBoolean
    public function isShowEmptyCategories(): bool
    {
        return $this->showEmptyCategories;
    }

    public function setShowEmptyCategories(bool $showEmptyCategories): self
    {
        $this->showEmptyCategories = $showEmptyCategories;

        return $this;
    }

    // TCMSFieldBoolean
    public function isAllowPurchaseOfVariantParents(): bool
    {
        return $this->allowPurchaseOfVariantParents;
    }

    public function setAllowPurchaseOfVariantParents(bool $allowPurchaseOfVariantParents): self
    {
        $this->allowPurchaseOfVariantParents = $allowPurchaseOfVariantParents;

        return $this;
    }

    // TCMSFieldBoolean
    public function isLoadInactiveVariants(): bool
    {
        return $this->loadInactiveVariants;
    }

    public function setLoadInactiveVariants(bool $loadInactiveVariants): self
    {
        $this->loadInactiveVariants = $loadInactiveVariants;

        return $this;
    }

    // TCMSFieldBoolean
    public function isSyncProfileDataWithBillingData(): bool
    {
        return $this->syncProfileDataWithBillingData;
    }

    public function setSyncProfileDataWithBillingData(bool $syncProfileDataWithBillingData): self
    {
        $this->syncProfileDataWithBillingData = $syncProfileDataWithBillingData;

        return $this;
    }

    // TCMSFieldBoolean
    public function isAllowMultipleBillingAddresses(): bool
    {
        return $this->allowMultipleBillingAddresses;
    }

    public function setAllowMultipleBillingAddresses(bool $allowMultipleBillingAddresses): self
    {
        $this->allowMultipleBillingAddresses = $allowMultipleBillingAddresses;

        return $this;
    }

    // TCMSFieldBoolean
    public function isAllowMultipleShippingAddresses(): bool
    {
        return $this->allowMultipleShippingAddresses;
    }

    public function setAllowMultipleShippingAddresses(bool $allowMultipleShippingAddresses): self
    {
        $this->allowMultipleShippingAddresses = $allowMultipleShippingAddresses;

        return $this;
    }

    // TCMSFieldBoolean
    public function isAllowGuestPurchase(): bool
    {
        return $this->allowGuestPurchase;
    }

    public function setAllowGuestPurchase(bool $allowGuestPurchase): self
    {
        $this->allowGuestPurchase = $allowGuestPurchase;

        return $this;
    }

    // TCMSFieldBoolean
    public function isLogArticleSuggestions(): bool
    {
        return $this->logArticleSuggestions;
    }

    public function setLogArticleSuggestions(bool $logArticleSuggestions): self
    {
        $this->logArticleSuggestions = $logArticleSuggestions;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopStockMessage>
     */
    public function getShopStockMessageCollection(): Collection
    {
        return $this->shopStockMessageCollection;
    }

    public function addShopStockMessageCollection(ShopStockMessage $shopStockMessage): self
    {
        if (!$this->shopStockMessageCollection->contains($shopStockMessage)) {
            $this->shopStockMessageCollection->add($shopStockMessage);
            $shopStockMessage->setShop($this);
        }

        return $this;
    }

    public function removeShopStockMessageCollection(ShopStockMessage $shopStockMessage): self
    {
        if ($this->shopStockMessageCollection->removeElement($shopStockMessage)) {
            // set the owning side to null (unless already changed)
            if ($shopStockMessage->getShop() === $this) {
                $shopStockMessage->setShop(null);
            }
        }

        return $this;
    }

    // TCMSFieldText_ShowExportURL
    public function getExportKey(): string
    {
        return $this->exportKey;
    }

    public function setExportKey(string $exportKey): self
    {
        $this->exportKey = $exportKey;

        return $this;
    }

    // TCMSFieldText
    public function getCartInfoText(): string
    {
        return $this->cartInfoText;
    }

    public function setCartInfoText(string $cartInfoText): self
    {
        $this->cartInfoText = $cartInfoText;

        return $this;
    }

    // TCMSFieldLookup
    public function getPkgShopListfilterPostsearch(): ?PkgShopListfilter
    {
        return $this->pkgShopListfilterPostsearch;
    }

    public function setPkgShopListfilterPostsearch(?PkgShopListfilter $pkgShopListfilterPostsearch): self
    {
        $this->pkgShopListfilterPostsearch = $pkgShopListfilterPostsearch;

        return $this;
    }

    // TCMSFieldBoolean
    public function isRedirectToNotFoundPageProductSearchOnNoResults(): bool
    {
        return $this->redirectToNotFoundPageProductSearchOnNoResults;
    }

    public function setRedirectToNotFoundPageProductSearchOnNoResults(
        bool $redirectToNotFoundPageProductSearchOnNoResults
    ): self {
        $this->redirectToNotFoundPageProductSearchOnNoResults = $redirectToNotFoundPageProductSearchOnNoResults;

        return $this;
    }

    // TCMSFieldBoolean
    public function isUseShopSearchLog(): bool
    {
        return $this->useShopSearchLog;
    }

    public function setUseShopSearchLog(bool $useShopSearchLog): self
    {
        $this->useShopSearchLog = $useShopSearchLog;

        return $this;
    }

    // TCMSFieldLookup
    public function getPkgShopListfilterCategoryFilter(): ?PkgShopListfilter
    {
        return $this->pkgShopListfilterCategoryFilter;
    }

    public function setPkgShopListfilterCategoryFilter(?PkgShopListfilter $pkgShopListfilterCategoryFilter): self
    {
        $this->pkgShopListfilterCategoryFilter = $pkgShopListfilterCategoryFilter;

        return $this;
    }

    // TCMSFieldNumber
    public function getDataExtranetUserShopArticleHistoryMaxCookieSize(): int
    {
        return $this->dataExtranetUserShopArticleHistoryMaxCookieSize;
    }

    public function setDataExtranetUserShopArticleHistoryMaxCookieSize(
        int $dataExtranetUserShopArticleHistoryMaxCookieSize
    ): self {
        $this->dataExtranetUserShopArticleHistoryMaxCookieSize = $dataExtranetUserShopArticleHistoryMaxCookieSize;

        return $this;
    }

    // TCMSFieldOption
    public function getProductUrlMode(): string
    {
        return $this->productUrlMode;
    }

    public function setProductUrlMode(string $productUrlMode): self
    {
        $this->productUrlMode = $productUrlMode;

        return $this;
    }

    // TCMSFieldNumber
    public function getShopreviewmailMailDelay(): int
    {
        return $this->shopreviewmailMailDelay;
    }

    public function setShopreviewmailMailDelay(int $shopreviewmailMailDelay): self
    {
        $this->shopreviewmailMailDelay = $shopreviewmailMailDelay;

        return $this;
    }

    // TCMSFieldDecimal
    public function getShopreviewmailPercentOfCustomers(): string
    {
        return $this->shopreviewmailPercentOfCustomers;
    }

    public function setShopreviewmailPercentOfCustomers(string $shopreviewmailPercentOfCustomers): self
    {
        $this->shopreviewmailPercentOfCustomers = $shopreviewmailPercentOfCustomers;

        return $this;
    }

    // TCMSFieldBoolean
    public function isShopreviewmailSendForEachOrder(): bool
    {
        return $this->shopreviewmailSendForEachOrder;
    }

    public function setShopreviewmailSendForEachOrder(bool $shopreviewmailSendForEachOrder): self
    {
        $this->shopreviewmailSendForEachOrder = $shopreviewmailSendForEachOrder;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopFooterCategory>
     */
    public function getPkgShopFooterCategoryCollection(): Collection
    {
        return $this->pkgShopFooterCategoryCollection;
    }

    public function addPkgShopFooterCategoryCollection(PkgShopFooterCategory $pkgShopFooterCategory): self
    {
        if (!$this->pkgShopFooterCategoryCollection->contains($pkgShopFooterCategory)) {
            $this->pkgShopFooterCategoryCollection->add($pkgShopFooterCategory);
            $pkgShopFooterCategory->setShop($this);
        }

        return $this;
    }

    public function removePkgShopFooterCategoryCollection(PkgShopFooterCategory $pkgShopFooterCategory): self
    {
        if ($this->pkgShopFooterCategoryCollection->removeElement($pkgShopFooterCategory)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopFooterCategory->getShop() === $this) {
                $pkgShopFooterCategory->setShop(null);
            }
        }

        return $this;
    }
}
