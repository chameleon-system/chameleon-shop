<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\DataAccessBundle\Entity\Core\CmsLanguage;
use ChameleonSystem\DataAccessBundle\Entity\Core\DataCountry;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetSalutation;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopAffiliateBundle\Entity\PkgShopAffiliate;
use ChameleonSystem\ShopBundle\Entity\Payment\ShopPaymentMethod;
use ChameleonSystem\ShopBundle\Entity\ShopCore\PkgShopCurrency;
use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopShippingGroup;
use ChameleonSystem\ShopBundle\Entity\ShopVoucher\ShopVoucherUse;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\PkgShopPaymentIpnMessage;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\PkgShopPaymentTransaction;
use ChameleonSystem\ShopRatingServiceBundle\Entity\PkgShopRatingService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopOrder
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldBoolean
        /** @var bool - Shop rating email - was processed */
        private bool $pkgShopRatingServiceMailProcessed = false,
        // TCMSFieldLookup
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null
        ,
        // TCMSFieldBoolean
        /** @var bool - User has also subscribed to the newsletter */
        private bool $newsletterSignup = false,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopPaymentIpnMessage> - */
        private Collection $pkgShopPaymentIpnMessageCollection = new ArrayCollection()
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopPaymentTransaction> - Transactions */
        private Collection $pkgShopPaymentTransactionCollection = new ArrayCollection()
        ,
        // TCMSFieldLookup
        /** @var CmsPortal|null - Placed by portal */
        private ?CmsPortal $cmsPortal = null
        ,
        // TCMSFieldNumber
        /** @var int - Order number */
        private int $ordernumber = 0,
        // TCMSFieldBoolean
        /** @var bool - Shop rating email - email was sent */
        private bool $pkgShopRatingServiceMailSent = false,
        // TCMSFieldLookup
        /** @var PkgShopRatingService|null - Used rating service */
        private ?PkgShopRatingService $pkgShopRatingService = null
        ,
        // TCMSFieldVarcharUnique
        /** @var string - Basket ID (unique ID that is already assigned in the order process) */
        private string $orderIdent = '',
        // TCMSFieldDateTime
        /** @var DateTime|null - Created on */
        private ?DateTime $datecreated = null,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderItem> - Items */
        private Collection $shopOrderItemCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupParentID
        /** @var DataExtranetUser|null - Shop customer */
        private ?DataExtranetUser $dataExtranetUser = null
        ,
        // TCMSFieldNumber
        /** @var int - Customer number */
        private int $customerNumber = 0,
        // TCMSFieldEmail
        /** @var string - Customer email */
        private string $userEmail = '',
        // TCMSFieldVarchar
        /** @var string - Company */
        private string $adrBillingCompany = '',
        // TCMSFieldExtendedLookup
        /** @var DataExtranetSalutation|null - Salutation */
        private ?DataExtranetSalutation $adrBillingSalutation = null
        ,
        // TCMSFieldVarchar
        /** @var string - First name */
        private string $adrBillingFirstname = '',
        // TCMSFieldVarchar
        /** @var string - Last name */
        private string $adrBillingLastname = '',
        // TCMSFieldVarchar
        /** @var string - Address appendix */
        private string $adrBillingAdditionalInfo = '',
        // TCMSFieldVarchar
        /** @var string - Street */
        private string $adrBillingStreet = '',
        // TCMSFieldVarchar
        /** @var string - Street number */
        private string $adrBillingStreetnr = '',
        // TCMSFieldVarchar
        /** @var string - City */
        private string $adrBillingCity = '',
        // TCMSFieldVarchar
        /** @var string - Zip code */
        private string $adrBillingPostalcode = '',
        // TCMSFieldExtendedLookup
        /** @var DataCountry|null - Country */
        private ?DataCountry $adrBillingCountry = null
        ,
        // TCMSFieldVarchar
        /** @var string - Telephone */
        private string $adrBillingTelefon = '',
        // TCMSFieldVarchar
        /** @var string - Fax */
        private string $adrBillingFax = '',
        // TCMSFieldExtendedLookup
        /** @var CmsLanguage|null - Language */
        private ?CmsLanguage $cmsLanguage = null
        ,
        // TCMSFieldVarchar
        /** @var string - User IP */
        private string $userIp = '',
        // TCMSFieldBoolean
        /** @var bool - Ship to billing address */
        private bool $adrShippingUseBilling = false,
        // TCMSFieldBoolean
        /** @var bool - Shipping address is a Packstation */
        private bool $adrShippingIsDhlPackstation = false,
        // TCMSFieldVarchar
        /** @var string - Company */
        private string $adrShippingCompany = '',
        // TCMSFieldExtendedLookup
        /** @var DataExtranetSalutation|null - Salutation */
        private ?DataExtranetSalutation $adrShippingSalutation = null
        ,
        // TCMSFieldVarchar
        /** @var string - First name */
        private string $adrShippingFirstname = '',
        // TCMSFieldVarchar
        /** @var string - Last name */
        private string $adrShippingLastname = '',
        // TCMSFieldVarchar
        /** @var string - Address appendix */
        private string $adrShippingAdditionalInfo = '',
        // TCMSFieldVarchar
        /** @var string - Street */
        private string $adrShippingStreet = '',
        // TCMSFieldVarchar
        /** @var string - Street number */
        private string $adrShippingStreetnr = '',
        // TCMSFieldVarchar
        /** @var string - City */
        private string $adrShippingCity = '',
        // TCMSFieldVarchar
        /** @var string - Zip code */
        private string $adrShippingPostalcode = '',
        // TCMSFieldExtendedLookup
        /** @var DataCountry|null - Country */
        private ?DataCountry $adrShippingCountry = null
        ,
        // TCMSFieldVarchar
        /** @var string - Telephone */
        private string $adrShippingTelefon = '',
        // TCMSFieldVarchar
        /** @var string - Fax */
        private string $adrShippingFax = '',
        // TCMSFieldLookup
        /** @var ShopShippingGroup|null - Shipping cost group */
        private ?ShopShippingGroup $shopShippingGroup = null
        ,
        // TCMSFieldVarchar
        /** @var string - Shipping cost group – name */
        private string $shopShippingGroupName = '',
        // TCMSFieldDecimal
        /** @var string - Shipping cost group – costs */
        private string $shopShippingGroupPrice = '',
        // TCMSFieldDecimal
        /** @var string - Shipping cost group – tax rate */
        private string $shopShippingGroupVatPercent = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderShippingGroupParameter> - Shipping cost group – parameter/user data */
        private Collection $shopOrderShippingGroupParameterCollection = new ArrayCollection()
        ,
        // TCMSFieldLookup
        /** @var ShopPaymentMethod|null - Payment method */
        private ?ShopPaymentMethod $shopPaymentMethod = null
        ,
        // TCMSFieldVarchar
        /** @var string - Payment method – name */
        private string $shopPaymentMethodName = '',
        // TCMSFieldDecimal
        /** @var string - Payment method – costs */
        private string $shopPaymentMethodPrice = '',
        // TCMSFieldDecimal
        /** @var string - Payment method – tax rate */
        private string $shopPaymentMethodVatPercent = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderPaymentMethodParameter> - Payment method – parameter/user data */
        private Collection $shopOrderPaymentMethodParameterCollection = new ArrayCollection()
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderVat> - Order VAT (by tax rate) */
        private Collection $shopOrderVatCollection = new ArrayCollection()
        ,
        // TCMSFieldDecimal
        /** @var string - Items value */
        private string $valueArticle = '',
        // TCMSFieldDecimal
        /** @var string - Total value */
        private string $valueTotal = '',
        // TCMSFieldExtendedLookup
        /** @var PkgShopCurrency|null - Currency */
        private ?PkgShopCurrency $pkgShopCurrency = null
        ,
        // TCMSFieldDecimal
        /** @var string - Wrapping costs */
        private string $valueWrapping = '',
        // TCMSFieldDecimal
        /** @var string - Wrapping greeting card costs */
        private string $valueWrappingCard = '',
        // TCMSFieldDecimal
        /** @var string - Total voucher value */
        private string $valueVouchers = '',
        // TCMSFieldDecimal
        /** @var string - Value of the non-sponsered vouchers (discount vouchers) */
        private string $valueVouchersNotSponsored = '',
        // TCMSFieldDecimal
        /** @var string - Total discount value */
        private string $valueDiscounts = '',
        // TCMSFieldDecimal
        /** @var string - Total VAT value */
        private string $valueVatTotal = '',
        // TCMSFieldDecimal
        /** @var string - Total number of items */
        private string $countArticles = '',
        // TCMSFieldNumber
        /** @var int - Number of different items */
        private int $countUniqueArticles = 0,
        // TCMSFieldDecimal
        /** @var string - Total weight (grams) */
        private string $totalweight = '',
        // TCMSFieldDecimal
        /** @var string - Total volume (cubic meters) */
        private string $totalvolume = '',
        // TCMSFieldBoolean
        /** @var bool - Saved order completely */
        private bool $systemOrderSaveCompleted = false,
        // TCMSFieldBoolean
        /** @var bool - Order confirmation sent */
        private bool $systemOrderNotificationSend = false,
        // TCMSFieldBoolean
        /** @var bool - Payment method executed successfully */
        private bool $systemOrderPaymentMethodExecuted = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Payment method executed on */
        private ?DateTime $systemOrderPaymentMethodExecutedDate = null,
        // TCMSFieldBoolean
        /** @var bool - Paid */
        private bool $orderIsPaid = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Marked as paid on */
        private ?DateTime $orderIsPaidDate = null,
        // TCMSFieldBoolean
        /** @var bool - Order was cancelled */
        private bool $canceled = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Date the order was marked as cancelled */
        private ?DateTime $canceledDate = null,
        // TCMSFieldDateTime
        /** @var DateTime|null - Was exported for ERP on */
        private ?DateTime $systemOrderExportedDate = null,
        // TCMSFieldVarchar
        /** @var string - Affiliate code */
        private string $affiliateCode = '',
        // TCMSFieldLookup
        /** @var PkgShopAffiliate|null - Order created via affiliate program */
        private ?PkgShopAffiliate $pkgShopAffiliate = null
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopVoucherUse> - Used vouchers */
        private Collection $shopVoucherUseCollection = new ArrayCollection()
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderDiscount> - Discount */
        private Collection $shopOrderDiscountCollection = new ArrayCollection()
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopOrderStatus> - Order status */
        private Collection $shopOrderStatusCollection = new ArrayCollection()
        ,
        // TCMSFieldText
        /** @var string - Mail object */
        private string $objectMail = '',
        // TCMSFieldDateTime
        /** @var DateTime|null - Rating request sent on */
        private ?DateTime $pkgShopRatingServiceRatingProcessedOn = null,
        // TCMSFieldVarchar
        /** @var string - VAT ID */
        private string $vatId = '',
        // TCMSFieldText
        /** @var string - Internal comment */
        private string $internalComment = '',
        // TCMSFieldDateTime
        /** @var DateTime|null - Date of shipment of all products */
        private ?DateTime $pkgShopRatingServiceOrderCompletelyShipped = null
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

    // TCMSFieldBoolean
    public function isPkgShopRatingServiceMailProcessed(): bool
    {
        return $this->pkgShopRatingServiceMailProcessed;
    }

    public function setPkgShopRatingServiceMailProcessed(bool $pkgShopRatingServiceMailProcessed): self
    {
        $this->pkgShopRatingServiceMailProcessed = $pkgShopRatingServiceMailProcessed;

        return $this;
    }


    // TCMSFieldLookup
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }


    // TCMSFieldBoolean
    public function isNewsletterSignup(): bool
    {
        return $this->newsletterSignup;
    }

    public function setNewsletterSignup(bool $newsletterSignup): self
    {
        $this->newsletterSignup = $newsletterSignup;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopPaymentIpnMessage>
     */
    public function getPkgShopPaymentIpnMessageCollection(): Collection
    {
        return $this->pkgShopPaymentIpnMessageCollection;
    }

    public function addPkgShopPaymentIpnMessageCollection(PkgShopPaymentIpnMessage $pkgShopPaymentIpnMessage): self
    {
        if (!$this->pkgShopPaymentIpnMessageCollection->contains($pkgShopPaymentIpnMessage)) {
            $this->pkgShopPaymentIpnMessageCollection->add($pkgShopPaymentIpnMessage);
            $pkgShopPaymentIpnMessage->setShopOrder($this);
        }

        return $this;
    }

    public function removePkgShopPaymentIpnMessageCollection(PkgShopPaymentIpnMessage $pkgShopPaymentIpnMessage): self
    {
        if ($this->pkgShopPaymentIpnMessageCollection->removeElement($pkgShopPaymentIpnMessage)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopPaymentIpnMessage->getShopOrder() === $this) {
                $pkgShopPaymentIpnMessage->setShopOrder(null);
            }
        }

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopPaymentTransaction>
     */
    public function getPkgShopPaymentTransactionCollection(): Collection
    {
        return $this->pkgShopPaymentTransactionCollection;
    }

    public function addPkgShopPaymentTransactionCollection(PkgShopPaymentTransaction $pkgShopPaymentTransaction): self
    {
        if (!$this->pkgShopPaymentTransactionCollection->contains($pkgShopPaymentTransaction)) {
            $this->pkgShopPaymentTransactionCollection->add($pkgShopPaymentTransaction);
            $pkgShopPaymentTransaction->setShopOrder($this);
        }

        return $this;
    }

    public function removePkgShopPaymentTransactionCollection(PkgShopPaymentTransaction $pkgShopPaymentTransaction
    ): self {
        if ($this->pkgShopPaymentTransactionCollection->removeElement($pkgShopPaymentTransaction)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopPaymentTransaction->getShopOrder() === $this) {
                $pkgShopPaymentTransaction->setShopOrder(null);
            }
        }

        return $this;
    }


    // TCMSFieldLookup
    public function getCmsPortal(): ?CmsPortal
    {
        return $this->cmsPortal;
    }

    public function setCmsPortal(?CmsPortal $cmsPortal): self
    {
        $this->cmsPortal = $cmsPortal;

        return $this;
    }


    // TCMSFieldNumber
    public function getOrdernumber(): int
    {
        return $this->ordernumber;
    }

    public function setOrdernumber(int $ordernumber): self
    {
        $this->ordernumber = $ordernumber;

        return $this;
    }


    // TCMSFieldBoolean
    public function isPkgShopRatingServiceMailSent(): bool
    {
        return $this->pkgShopRatingServiceMailSent;
    }

    public function setPkgShopRatingServiceMailSent(bool $pkgShopRatingServiceMailSent): self
    {
        $this->pkgShopRatingServiceMailSent = $pkgShopRatingServiceMailSent;

        return $this;
    }


    // TCMSFieldLookup
    public function getPkgShopRatingService(): ?PkgShopRatingService
    {
        return $this->pkgShopRatingService;
    }

    public function setPkgShopRatingService(?PkgShopRatingService $pkgShopRatingService): self
    {
        $this->pkgShopRatingService = $pkgShopRatingService;

        return $this;
    }


    // TCMSFieldVarcharUnique
    public function getOrderIdent(): string
    {
        return $this->orderIdent;
    }

    public function setOrderIdent(string $orderIdent): self
    {
        $this->orderIdent = $orderIdent;

        return $this;
    }


    // TCMSFieldDateTime
    public function getDatecreated(): ?DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderItem>
     */
    public function getShopOrderItemCollection(): Collection
    {
        return $this->shopOrderItemCollection;
    }

    public function addShopOrderItemCollection(ShopOrderItem $shopOrderItem): self
    {
        if (!$this->shopOrderItemCollection->contains($shopOrderItem)) {
            $this->shopOrderItemCollection->add($shopOrderItem);
            $shopOrderItem->setShopOrder($this);
        }

        return $this;
    }

    public function removeShopOrderItemCollection(ShopOrderItem $shopOrderItem): self
    {
        if ($this->shopOrderItemCollection->removeElement($shopOrderItem)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderItem->getShopOrder() === $this) {
                $shopOrderItem->setShopOrder(null);
            }
        }

        return $this;
    }


    // TCMSFieldLookupParentID
    public function getDataExtranetUser(): ?DataExtranetUser
    {
        return $this->dataExtranetUser;
    }

    public function setDataExtranetUser(?DataExtranetUser $dataExtranetUser): self
    {
        $this->dataExtranetUser = $dataExtranetUser;

        return $this;
    }


    // TCMSFieldNumber
    public function getCustomerNumber(): int
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(int $customerNumber): self
    {
        $this->customerNumber = $customerNumber;

        return $this;
    }


    // TCMSFieldEmail
    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): self
    {
        $this->userEmail = $userEmail;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingCompany(): string
    {
        return $this->adrBillingCompany;
    }

    public function setAdrBillingCompany(string $adrBillingCompany): self
    {
        $this->adrBillingCompany = $adrBillingCompany;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getAdrBillingSalutation(): ?DataExtranetSalutation
    {
        return $this->adrBillingSalutation;
    }

    public function setAdrBillingSalutation(?DataExtranetSalutation $adrBillingSalutation): self
    {
        $this->adrBillingSalutation = $adrBillingSalutation;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingFirstname(): string
    {
        return $this->adrBillingFirstname;
    }

    public function setAdrBillingFirstname(string $adrBillingFirstname): self
    {
        $this->adrBillingFirstname = $adrBillingFirstname;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingLastname(): string
    {
        return $this->adrBillingLastname;
    }

    public function setAdrBillingLastname(string $adrBillingLastname): self
    {
        $this->adrBillingLastname = $adrBillingLastname;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingAdditionalInfo(): string
    {
        return $this->adrBillingAdditionalInfo;
    }

    public function setAdrBillingAdditionalInfo(string $adrBillingAdditionalInfo): self
    {
        $this->adrBillingAdditionalInfo = $adrBillingAdditionalInfo;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingStreet(): string
    {
        return $this->adrBillingStreet;
    }

    public function setAdrBillingStreet(string $adrBillingStreet): self
    {
        $this->adrBillingStreet = $adrBillingStreet;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingStreetnr(): string
    {
        return $this->adrBillingStreetnr;
    }

    public function setAdrBillingStreetnr(string $adrBillingStreetnr): self
    {
        $this->adrBillingStreetnr = $adrBillingStreetnr;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingCity(): string
    {
        return $this->adrBillingCity;
    }

    public function setAdrBillingCity(string $adrBillingCity): self
    {
        $this->adrBillingCity = $adrBillingCity;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingPostalcode(): string
    {
        return $this->adrBillingPostalcode;
    }

    public function setAdrBillingPostalcode(string $adrBillingPostalcode): self
    {
        $this->adrBillingPostalcode = $adrBillingPostalcode;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getAdrBillingCountry(): ?DataCountry
    {
        return $this->adrBillingCountry;
    }

    public function setAdrBillingCountry(?DataCountry $adrBillingCountry): self
    {
        $this->adrBillingCountry = $adrBillingCountry;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingTelefon(): string
    {
        return $this->adrBillingTelefon;
    }

    public function setAdrBillingTelefon(string $adrBillingTelefon): self
    {
        $this->adrBillingTelefon = $adrBillingTelefon;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrBillingFax(): string
    {
        return $this->adrBillingFax;
    }

    public function setAdrBillingFax(string $adrBillingFax): self
    {
        $this->adrBillingFax = $adrBillingFax;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getCmsLanguage(): ?CmsLanguage
    {
        return $this->cmsLanguage;
    }

    public function setCmsLanguage(?CmsLanguage $cmsLanguage): self
    {
        $this->cmsLanguage = $cmsLanguage;

        return $this;
    }


    // TCMSFieldVarchar
    public function getUserIp(): string
    {
        return $this->userIp;
    }

    public function setUserIp(string $userIp): self
    {
        $this->userIp = $userIp;

        return $this;
    }


    // TCMSFieldBoolean
    public function isAdrShippingUseBilling(): bool
    {
        return $this->adrShippingUseBilling;
    }

    public function setAdrShippingUseBilling(bool $adrShippingUseBilling): self
    {
        $this->adrShippingUseBilling = $adrShippingUseBilling;

        return $this;
    }


    // TCMSFieldBoolean
    public function isAdrShippingIsDhlPackstation(): bool
    {
        return $this->adrShippingIsDhlPackstation;
    }

    public function setAdrShippingIsDhlPackstation(bool $adrShippingIsDhlPackstation): self
    {
        $this->adrShippingIsDhlPackstation = $adrShippingIsDhlPackstation;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingCompany(): string
    {
        return $this->adrShippingCompany;
    }

    public function setAdrShippingCompany(string $adrShippingCompany): self
    {
        $this->adrShippingCompany = $adrShippingCompany;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getAdrShippingSalutation(): ?DataExtranetSalutation
    {
        return $this->adrShippingSalutation;
    }

    public function setAdrShippingSalutation(?DataExtranetSalutation $adrShippingSalutation): self
    {
        $this->adrShippingSalutation = $adrShippingSalutation;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingFirstname(): string
    {
        return $this->adrShippingFirstname;
    }

    public function setAdrShippingFirstname(string $adrShippingFirstname): self
    {
        $this->adrShippingFirstname = $adrShippingFirstname;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingLastname(): string
    {
        return $this->adrShippingLastname;
    }

    public function setAdrShippingLastname(string $adrShippingLastname): self
    {
        $this->adrShippingLastname = $adrShippingLastname;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingAdditionalInfo(): string
    {
        return $this->adrShippingAdditionalInfo;
    }

    public function setAdrShippingAdditionalInfo(string $adrShippingAdditionalInfo): self
    {
        $this->adrShippingAdditionalInfo = $adrShippingAdditionalInfo;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingStreet(): string
    {
        return $this->adrShippingStreet;
    }

    public function setAdrShippingStreet(string $adrShippingStreet): self
    {
        $this->adrShippingStreet = $adrShippingStreet;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingStreetnr(): string
    {
        return $this->adrShippingStreetnr;
    }

    public function setAdrShippingStreetnr(string $adrShippingStreetnr): self
    {
        $this->adrShippingStreetnr = $adrShippingStreetnr;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingCity(): string
    {
        return $this->adrShippingCity;
    }

    public function setAdrShippingCity(string $adrShippingCity): self
    {
        $this->adrShippingCity = $adrShippingCity;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingPostalcode(): string
    {
        return $this->adrShippingPostalcode;
    }

    public function setAdrShippingPostalcode(string $adrShippingPostalcode): self
    {
        $this->adrShippingPostalcode = $adrShippingPostalcode;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getAdrShippingCountry(): ?DataCountry
    {
        return $this->adrShippingCountry;
    }

    public function setAdrShippingCountry(?DataCountry $adrShippingCountry): self
    {
        $this->adrShippingCountry = $adrShippingCountry;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingTelefon(): string
    {
        return $this->adrShippingTelefon;
    }

    public function setAdrShippingTelefon(string $adrShippingTelefon): self
    {
        $this->adrShippingTelefon = $adrShippingTelefon;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAdrShippingFax(): string
    {
        return $this->adrShippingFax;
    }

    public function setAdrShippingFax(string $adrShippingFax): self
    {
        $this->adrShippingFax = $adrShippingFax;

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


    // TCMSFieldVarchar
    public function getShopShippingGroupName(): string
    {
        return $this->shopShippingGroupName;
    }

    public function setShopShippingGroupName(string $shopShippingGroupName): self
    {
        $this->shopShippingGroupName = $shopShippingGroupName;

        return $this;
    }


    // TCMSFieldDecimal
    public function getShopShippingGroupPrice(): string
    {
        return $this->shopShippingGroupPrice;
    }

    public function setShopShippingGroupPrice(string $shopShippingGroupPrice): self
    {
        $this->shopShippingGroupPrice = $shopShippingGroupPrice;

        return $this;
    }


    // TCMSFieldDecimal
    public function getShopShippingGroupVatPercent(): string
    {
        return $this->shopShippingGroupVatPercent;
    }

    public function setShopShippingGroupVatPercent(string $shopShippingGroupVatPercent): self
    {
        $this->shopShippingGroupVatPercent = $shopShippingGroupVatPercent;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderShippingGroupParameter>
     */
    public function getShopOrderShippingGroupParameterCollection(): Collection
    {
        return $this->shopOrderShippingGroupParameterCollection;
    }

    public function addShopOrderShippingGroupParameterCollection(
        ShopOrderShippingGroupParameter $shopOrderShippingGroupParameter
    ): self {
        if (!$this->shopOrderShippingGroupParameterCollection->contains($shopOrderShippingGroupParameter)) {
            $this->shopOrderShippingGroupParameterCollection->add($shopOrderShippingGroupParameter);
            $shopOrderShippingGroupParameter->setShopOrder($this);
        }

        return $this;
    }

    public function removeShopOrderShippingGroupParameterCollection(
        ShopOrderShippingGroupParameter $shopOrderShippingGroupParameter
    ): self {
        if ($this->shopOrderShippingGroupParameterCollection->removeElement($shopOrderShippingGroupParameter)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderShippingGroupParameter->getShopOrder() === $this) {
                $shopOrderShippingGroupParameter->setShopOrder(null);
            }
        }

        return $this;
    }


    // TCMSFieldLookup
    public function getShopPaymentMethod(): ?ShopPaymentMethod
    {
        return $this->shopPaymentMethod;
    }

    public function setShopPaymentMethod(?ShopPaymentMethod $shopPaymentMethod): self
    {
        $this->shopPaymentMethod = $shopPaymentMethod;

        return $this;
    }


    // TCMSFieldVarchar
    public function getShopPaymentMethodName(): string
    {
        return $this->shopPaymentMethodName;
    }

    public function setShopPaymentMethodName(string $shopPaymentMethodName): self
    {
        $this->shopPaymentMethodName = $shopPaymentMethodName;

        return $this;
    }


    // TCMSFieldDecimal
    public function getShopPaymentMethodPrice(): string
    {
        return $this->shopPaymentMethodPrice;
    }

    public function setShopPaymentMethodPrice(string $shopPaymentMethodPrice): self
    {
        $this->shopPaymentMethodPrice = $shopPaymentMethodPrice;

        return $this;
    }


    // TCMSFieldDecimal
    public function getShopPaymentMethodVatPercent(): string
    {
        return $this->shopPaymentMethodVatPercent;
    }

    public function setShopPaymentMethodVatPercent(string $shopPaymentMethodVatPercent): self
    {
        $this->shopPaymentMethodVatPercent = $shopPaymentMethodVatPercent;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderPaymentMethodParameter>
     */
    public function getShopOrderPaymentMethodParameterCollection(): Collection
    {
        return $this->shopOrderPaymentMethodParameterCollection;
    }

    public function addShopOrderPaymentMethodParameterCollection(
        ShopOrderPaymentMethodParameter $shopOrderPaymentMethodParameter
    ): self {
        if (!$this->shopOrderPaymentMethodParameterCollection->contains($shopOrderPaymentMethodParameter)) {
            $this->shopOrderPaymentMethodParameterCollection->add($shopOrderPaymentMethodParameter);
            $shopOrderPaymentMethodParameter->setShopOrder($this);
        }

        return $this;
    }

    public function removeShopOrderPaymentMethodParameterCollection(
        ShopOrderPaymentMethodParameter $shopOrderPaymentMethodParameter
    ): self {
        if ($this->shopOrderPaymentMethodParameterCollection->removeElement($shopOrderPaymentMethodParameter)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderPaymentMethodParameter->getShopOrder() === $this) {
                $shopOrderPaymentMethodParameter->setShopOrder(null);
            }
        }

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderVat>
     */
    public function getShopOrderVatCollection(): Collection
    {
        return $this->shopOrderVatCollection;
    }

    public function addShopOrderVatCollection(ShopOrderVat $shopOrderVat): self
    {
        if (!$this->shopOrderVatCollection->contains($shopOrderVat)) {
            $this->shopOrderVatCollection->add($shopOrderVat);
            $shopOrderVat->setShopOrder($this);
        }

        return $this;
    }

    public function removeShopOrderVatCollection(ShopOrderVat $shopOrderVat): self
    {
        if ($this->shopOrderVatCollection->removeElement($shopOrderVat)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderVat->getShopOrder() === $this) {
                $shopOrderVat->setShopOrder(null);
            }
        }

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueArticle(): string
    {
        return $this->valueArticle;
    }

    public function setValueArticle(string $valueArticle): self
    {
        $this->valueArticle = $valueArticle;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueTotal(): string
    {
        return $this->valueTotal;
    }

    public function setValueTotal(string $valueTotal): self
    {
        $this->valueTotal = $valueTotal;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getPkgShopCurrency(): ?PkgShopCurrency
    {
        return $this->pkgShopCurrency;
    }

    public function setPkgShopCurrency(?PkgShopCurrency $pkgShopCurrency): self
    {
        $this->pkgShopCurrency = $pkgShopCurrency;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueWrapping(): string
    {
        return $this->valueWrapping;
    }

    public function setValueWrapping(string $valueWrapping): self
    {
        $this->valueWrapping = $valueWrapping;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueWrappingCard(): string
    {
        return $this->valueWrappingCard;
    }

    public function setValueWrappingCard(string $valueWrappingCard): self
    {
        $this->valueWrappingCard = $valueWrappingCard;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueVouchers(): string
    {
        return $this->valueVouchers;
    }

    public function setValueVouchers(string $valueVouchers): self
    {
        $this->valueVouchers = $valueVouchers;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueVouchersNotSponsored(): string
    {
        return $this->valueVouchersNotSponsored;
    }

    public function setValueVouchersNotSponsored(string $valueVouchersNotSponsored): self
    {
        $this->valueVouchersNotSponsored = $valueVouchersNotSponsored;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueDiscounts(): string
    {
        return $this->valueDiscounts;
    }

    public function setValueDiscounts(string $valueDiscounts): self
    {
        $this->valueDiscounts = $valueDiscounts;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValueVatTotal(): string
    {
        return $this->valueVatTotal;
    }

    public function setValueVatTotal(string $valueVatTotal): self
    {
        $this->valueVatTotal = $valueVatTotal;

        return $this;
    }


    // TCMSFieldDecimal
    public function getCountArticles(): string
    {
        return $this->countArticles;
    }

    public function setCountArticles(string $countArticles): self
    {
        $this->countArticles = $countArticles;

        return $this;
    }


    // TCMSFieldNumber
    public function getCountUniqueArticles(): int
    {
        return $this->countUniqueArticles;
    }

    public function setCountUniqueArticles(int $countUniqueArticles): self
    {
        $this->countUniqueArticles = $countUniqueArticles;

        return $this;
    }


    // TCMSFieldDecimal
    public function getTotalweight(): string
    {
        return $this->totalweight;
    }

    public function setTotalweight(string $totalweight): self
    {
        $this->totalweight = $totalweight;

        return $this;
    }


    // TCMSFieldDecimal
    public function getTotalvolume(): string
    {
        return $this->totalvolume;
    }

    public function setTotalvolume(string $totalvolume): self
    {
        $this->totalvolume = $totalvolume;

        return $this;
    }


    // TCMSFieldBoolean
    public function isSystemOrderSaveCompleted(): bool
    {
        return $this->systemOrderSaveCompleted;
    }

    public function setSystemOrderSaveCompleted(bool $systemOrderSaveCompleted): self
    {
        $this->systemOrderSaveCompleted = $systemOrderSaveCompleted;

        return $this;
    }


    // TCMSFieldBoolean
    public function isSystemOrderNotificationSend(): bool
    {
        return $this->systemOrderNotificationSend;
    }

    public function setSystemOrderNotificationSend(bool $systemOrderNotificationSend): self
    {
        $this->systemOrderNotificationSend = $systemOrderNotificationSend;

        return $this;
    }


    // TCMSFieldBoolean
    public function isSystemOrderPaymentMethodExecuted(): bool
    {
        return $this->systemOrderPaymentMethodExecuted;
    }

    public function setSystemOrderPaymentMethodExecuted(bool $systemOrderPaymentMethodExecuted): self
    {
        $this->systemOrderPaymentMethodExecuted = $systemOrderPaymentMethodExecuted;

        return $this;
    }


    // TCMSFieldDateTime
    public function getSystemOrderPaymentMethodExecutedDate(): ?DateTime
    {
        return $this->systemOrderPaymentMethodExecutedDate;
    }

    public function setSystemOrderPaymentMethodExecutedDate(?DateTime $systemOrderPaymentMethodExecutedDate): self
    {
        $this->systemOrderPaymentMethodExecutedDate = $systemOrderPaymentMethodExecutedDate;

        return $this;
    }


    // TCMSFieldBoolean
    public function isOrderIsPaid(): bool
    {
        return $this->orderIsPaid;
    }

    public function setOrderIsPaid(bool $orderIsPaid): self
    {
        $this->orderIsPaid = $orderIsPaid;

        return $this;
    }


    // TCMSFieldDateTime
    public function getOrderIsPaidDate(): ?DateTime
    {
        return $this->orderIsPaidDate;
    }

    public function setOrderIsPaidDate(?DateTime $orderIsPaidDate): self
    {
        $this->orderIsPaidDate = $orderIsPaidDate;

        return $this;
    }


    // TCMSFieldBoolean
    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    public function setCanceled(bool $canceled): self
    {
        $this->canceled = $canceled;

        return $this;
    }


    // TCMSFieldDateTime
    public function getCanceledDate(): ?DateTime
    {
        return $this->canceledDate;
    }

    public function setCanceledDate(?DateTime $canceledDate): self
    {
        $this->canceledDate = $canceledDate;

        return $this;
    }


    // TCMSFieldDateTime
    public function getSystemOrderExportedDate(): ?DateTime
    {
        return $this->systemOrderExportedDate;
    }

    public function setSystemOrderExportedDate(?DateTime $systemOrderExportedDate): self
    {
        $this->systemOrderExportedDate = $systemOrderExportedDate;

        return $this;
    }


    // TCMSFieldVarchar
    public function getAffiliateCode(): string
    {
        return $this->affiliateCode;
    }

    public function setAffiliateCode(string $affiliateCode): self
    {
        $this->affiliateCode = $affiliateCode;

        return $this;
    }


    // TCMSFieldLookup
    public function getPkgShopAffiliate(): ?PkgShopAffiliate
    {
        return $this->pkgShopAffiliate;
    }

    public function setPkgShopAffiliate(?PkgShopAffiliate $pkgShopAffiliate): self
    {
        $this->pkgShopAffiliate = $pkgShopAffiliate;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopVoucherUse>
     */
    public function getShopVoucherUseCollection(): Collection
    {
        return $this->shopVoucherUseCollection;
    }

    public function addShopVoucherUseCollection(ShopVoucherUse $shopVoucherUse): self
    {
        if (!$this->shopVoucherUseCollection->contains($shopVoucherUse)) {
            $this->shopVoucherUseCollection->add($shopVoucherUse);
            $shopVoucherUse->setShopOrder($this);
        }

        return $this;
    }

    public function removeShopVoucherUseCollection(ShopVoucherUse $shopVoucherUse): self
    {
        if ($this->shopVoucherUseCollection->removeElement($shopVoucherUse)) {
            // set the owning side to null (unless already changed)
            if ($shopVoucherUse->getShopOrder() === $this) {
                $shopVoucherUse->setShopOrder(null);
            }
        }

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderDiscount>
     */
    public function getShopOrderDiscountCollection(): Collection
    {
        return $this->shopOrderDiscountCollection;
    }

    public function addShopOrderDiscountCollection(ShopOrderDiscount $shopOrderDiscount): self
    {
        if (!$this->shopOrderDiscountCollection->contains($shopOrderDiscount)) {
            $this->shopOrderDiscountCollection->add($shopOrderDiscount);
            $shopOrderDiscount->setShopOrder($this);
        }

        return $this;
    }

    public function removeShopOrderDiscountCollection(ShopOrderDiscount $shopOrderDiscount): self
    {
        if ($this->shopOrderDiscountCollection->removeElement($shopOrderDiscount)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderDiscount->getShopOrder() === $this) {
                $shopOrderDiscount->setShopOrder(null);
            }
        }

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopOrderStatus>
     */
    public function getShopOrderStatusCollection(): Collection
    {
        return $this->shopOrderStatusCollection;
    }

    public function addShopOrderStatusCollection(ShopOrderStatus $shopOrderStatus): self
    {
        if (!$this->shopOrderStatusCollection->contains($shopOrderStatus)) {
            $this->shopOrderStatusCollection->add($shopOrderStatus);
            $shopOrderStatus->setShopOrder($this);
        }

        return $this;
    }

    public function removeShopOrderStatusCollection(ShopOrderStatus $shopOrderStatus): self
    {
        if ($this->shopOrderStatusCollection->removeElement($shopOrderStatus)) {
            // set the owning side to null (unless already changed)
            if ($shopOrderStatus->getShopOrder() === $this) {
                $shopOrderStatus->setShopOrder(null);
            }
        }

        return $this;
    }


    // TCMSFieldText
    public function getObjectMail(): string
    {
        return $this->objectMail;
    }

    public function setObjectMail(string $objectMail): self
    {
        $this->objectMail = $objectMail;

        return $this;
    }


    // TCMSFieldDateTime
    public function getPkgShopRatingServiceRatingProcessedOn(): ?DateTime
    {
        return $this->pkgShopRatingServiceRatingProcessedOn;
    }

    public function setPkgShopRatingServiceRatingProcessedOn(?DateTime $pkgShopRatingServiceRatingProcessedOn): self
    {
        $this->pkgShopRatingServiceRatingProcessedOn = $pkgShopRatingServiceRatingProcessedOn;

        return $this;
    }


    // TCMSFieldVarchar
    public function getVatId(): string
    {
        return $this->vatId;
    }

    public function setVatId(string $vatId): self
    {
        $this->vatId = $vatId;

        return $this;
    }


    // TCMSFieldText
    public function getInternalComment(): string
    {
        return $this->internalComment;
    }

    public function setInternalComment(string $internalComment): self
    {
        $this->internalComment = $internalComment;

        return $this;
    }


    // TCMSFieldDateTime
    public function getPkgShopRatingServiceOrderCompletelyShipped(): ?DateTime
    {
        return $this->pkgShopRatingServiceOrderCompletelyShipped;
    }

    public function setPkgShopRatingServiceOrderCompletelyShipped(?DateTime $pkgShopRatingServiceOrderCompletelyShipped
    ): self {
        $this->pkgShopRatingServiceOrderCompletelyShipped = $pkgShopRatingServiceOrderCompletelyShipped;

        return $this;
    }


}
