<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\ShopPaymentIPNBundle\Entity\pkgShopPaymentIpnMessage;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\AmazonPaymentBundle\Entity\amazonPaymentIdMapping;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\pkgShopPaymentTransaction;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;
use ChameleonSystem\ShopRatingServiceBundle\Entity\pkgShopRatingService;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderItem;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetSalutation;
use ChameleonSystem\DataAccessBundle\Entity\Core\dataCountry;
use ChameleonSystem\DataAccessBundle\Entity\Core\cmsLanguage;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopShippingGroup;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderShippingGroupParameter;
use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentMethod;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderPaymentMethodParameter;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderVat;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopCurrency;
use ChameleonSystem\ShopAffiliateBundle\Entity\pkgShopAffiliate;
use ChameleonSystem\ShopBundle\Entity\ShopVoucher\shopVoucherUse;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderDiscount;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderStatus;

class shopOrder {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldBoolean
/** @var bool - Shop rating email - was processed */
private bool $PkgShopRatingServiceMailProcessed = false, 
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldBoolean
/** @var bool - User has also subscribed to the newsletter */
private bool $NewsletterSignup = false, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentIpnMessage> -  */
private Collection $PkgShopPaymentIpnMessageCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, amazonPaymentIdMapping> - Amazon Pay */
private Collection $AmazonPaymentIdMappingCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, pkgShopPaymentTransaction> - Transactions */
private Collection $PkgShopPaymentTransactionCollection = new ArrayCollection()
, 
    // TCMSFieldLookupParentID
/** @var cmsPortal|null - Placed by portal */
private ?cmsPortal $CmsPortal = null
, 
    // TCMSFieldNumber
/** @var int - Order number */
private int $Ordernumber = 0, 
    // TCMSFieldBoolean
/** @var bool - Shop rating email - email was sent */
private bool $PkgShopRatingServiceMailSent = false, 
    // TCMSFieldLookup
/** @var pkgShopRatingService|null - Used rating service */
private ?pkgShopRatingService $PkgShopRatingService = null
, 
    // TCMSFieldVarcharUnique
/** @var string - Basket ID (unique ID that is already assigned in the order process) */
private string $OrderIdent = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = null, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderItem> - Items */
private Collection $ShopOrderItemCollection = new ArrayCollection()
, 
    // TCMSFieldLookupParentID
/** @var dataExtranetUser|null - Shop customer */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldNumber
/** @var int - Customer number */
private int $CustomerNumber = 0, 
    // TCMSFieldEmail
/** @var string - Customer email */
private string $UserEmail = '', 
    // TCMSFieldVarchar
/** @var string - Company */
private string $AdrBillingCompany = '', 
    // TCMSFieldExtendedLookup
/** @var dataExtranetSalutation|null - Salutation */
private ?dataExtranetSalutation $AdrBillingSalutation = null
, 
    // TCMSFieldVarchar
/** @var string - First name */
private string $AdrBillingFirstname = '', 
    // TCMSFieldVarchar
/** @var string - Last name */
private string $AdrBillingLastname = '', 
    // TCMSFieldVarchar
/** @var string - Address appendix */
private string $AdrBillingAdditionalInfo = '', 
    // TCMSFieldVarchar
/** @var string - Street */
private string $AdrBillingStreet = '', 
    // TCMSFieldVarchar
/** @var string - Street number */
private string $AdrBillingStreetnr = '', 
    // TCMSFieldVarchar
/** @var string - City */
private string $AdrBillingCity = '', 
    // TCMSFieldVarchar
/** @var string - Zip code */
private string $AdrBillingPostalcode = '', 
    // TCMSFieldExtendedLookup
/** @var dataCountry|null - Country */
private ?dataCountry $AdrBillingCountry = null
, 
    // TCMSFieldVarchar
/** @var string - Telephone */
private string $AdrBillingTelefon = '', 
    // TCMSFieldVarchar
/** @var string - Fax */
private string $AdrBillingFax = '', 
    // TCMSFieldExtendedLookup
/** @var cmsLanguage|null - Language */
private ?cmsLanguage $CmsLanguage = null
, 
    // TCMSFieldVarchar
/** @var string - User IP */
private string $UserIp = '', 
    // TCMSFieldBoolean
/** @var bool - Ship to billing address */
private bool $AdrShippingUseBilling = false, 
    // TCMSFieldBoolean
/** @var bool - Shipping address is a Packstation */
private bool $AdrShippingIsDhlPackstation = false, 
    // TCMSFieldVarchar
/** @var string - Company */
private string $AdrShippingCompany = '', 
    // TCMSFieldExtendedLookup
/** @var dataExtranetSalutation|null - Salutation */
private ?dataExtranetSalutation $AdrShippingSalutation = null
, 
    // TCMSFieldVarchar
/** @var string - First name */
private string $AdrShippingFirstname = '', 
    // TCMSFieldVarchar
/** @var string - Last name */
private string $AdrShippingLastname = '', 
    // TCMSFieldVarchar
/** @var string - Address appendix */
private string $AdrShippingAdditionalInfo = '', 
    // TCMSFieldVarchar
/** @var string - Street */
private string $AdrShippingStreet = '', 
    // TCMSFieldVarchar
/** @var string - Street number */
private string $AdrShippingStreetnr = '', 
    // TCMSFieldVarchar
/** @var string - City */
private string $AdrShippingCity = '', 
    // TCMSFieldVarchar
/** @var string - Zip code */
private string $AdrShippingPostalcode = '', 
    // TCMSFieldExtendedLookup
/** @var dataCountry|null - Country */
private ?dataCountry $AdrShippingCountry = null
, 
    // TCMSFieldVarchar
/** @var string - Telephone */
private string $AdrShippingTelefon = '', 
    // TCMSFieldVarchar
/** @var string - Fax */
private string $AdrShippingFax = '', 
    // TCMSFieldLookup
/** @var shopShippingGroup|null - Shipping cost group */
private ?shopShippingGroup $ShopShippingGroup = null
, 
    // TCMSFieldVarchar
/** @var string - Shipping cost group – name */
private string $ShopShippingGroupName = '', 
    // TCMSFieldDecimal
/** @var float - Shipping cost group – costs */
private float $ShopShippingGroupPrice = 0, 
    // TCMSFieldDecimal
/** @var float - Shipping cost group – tax rate */
private float $ShopShippingGroupVatPercent = 0, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderShippingGroupParameter> - Shipping cost group – parameter/user data */
private Collection $ShopOrderShippingGroupParameterCollection = new ArrayCollection()
, 
    // TCMSFieldLookup
/** @var shopPaymentMethod|null - Payment method */
private ?shopPaymentMethod $ShopPaymentMethod = null
, 
    // TCMSFieldVarchar
/** @var string - Payment method – name */
private string $ShopPaymentMethodName = '', 
    // TCMSFieldDecimal
/** @var float - Payment method – costs */
private float $ShopPaymentMethodPrice = 0, 
    // TCMSFieldDecimal
/** @var float - Payment method – tax rate */
private float $ShopPaymentMethodVatPercent = 0, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderPaymentMethodParameter> - Payment method – parameter/user data */
private Collection $ShopOrderPaymentMethodParameterCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderVat> - Order VAT (by tax rate) */
private Collection $ShopOrderVatCollection = new ArrayCollection()
, 
    // TCMSFieldDecimal
/** @var float - Items value */
private float $ValueArticle = 0, 
    // TCMSFieldDecimal
/** @var float - Total value */
private float $ValueTotal = 0, 
    // TCMSFieldExtendedLookup
/** @var pkgShopCurrency|null - Currency */
private ?pkgShopCurrency $PkgShopCurrency = null
, 
    // TCMSFieldDecimal
/** @var float - Wrapping costs */
private float $ValueWrapping = 0, 
    // TCMSFieldDecimal
/** @var float - Wrapping greeting card costs */
private float $ValueWrappingCard = 0, 
    // TCMSFieldDecimal
/** @var float - Total voucher value */
private float $ValueVouchers = 0, 
    // TCMSFieldDecimal
/** @var float - Value of the non-sponsered vouchers (discount vouchers) */
private float $ValueVouchersNotSponsored = 0, 
    // TCMSFieldDecimal
/** @var float - Total discount value */
private float $ValueDiscounts = 0, 
    // TCMSFieldDecimal
/** @var float - Total VAT value */
private float $ValueVatTotal = 0, 
    // TCMSFieldDecimal
/** @var float - Total number of items */
private float $CountArticles = 0, 
    // TCMSFieldNumber
/** @var int - Number of different items */
private int $CountUniqueArticles = 0, 
    // TCMSFieldDecimal
/** @var float - Total weight (grams) */
private float $Totalweight = 0, 
    // TCMSFieldDecimal
/** @var float - Total volume (cubic meters) */
private float $Totalvolume = 0, 
    // TCMSFieldBoolean
/** @var bool - Saved order completely */
private bool $SystemOrderSaveCompleted = false, 
    // TCMSFieldBoolean
/** @var bool - Order confirmation sent */
private bool $SystemOrderNotificationSend = false, 
    // TCMSFieldBoolean
/** @var bool - Payment method executed successfully */
private bool $SystemOrderPaymentMethodExecuted = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Payment method executed on */
private ?\DateTime $SystemOrderPaymentMethodExecutedDate = null, 
    // TCMSFieldBoolean
/** @var bool - Paid */
private bool $OrderIsPaid = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Marked as paid on */
private ?\DateTime $OrderIsPaidDate = null, 
    // TCMSFieldBoolean
/** @var bool - Order was cancelled */
private bool $Canceled = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Date the order was marked as cancelled */
private ?\DateTime $CanceledDate = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Was exported for ERP on */
private ?\DateTime $SystemOrderExportedDate = null, 
    // TCMSFieldVarchar
/** @var string - Affiliate code */
private string $AffiliateCode = '', 
    // TCMSFieldLookup
/** @var pkgShopAffiliate|null - Order created via affiliate program */
private ?pkgShopAffiliate $PkgShopAffiliate = null
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopVoucherUse> - Used vouchers */
private Collection $ShopVoucherUseCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderDiscount> - Discount */
private Collection $ShopOrderDiscountCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopOrderStatus> - Order status */
private Collection $ShopOrderStatusCollection = new ArrayCollection()
, 
    // TCMSFieldText
/** @var string - Mail object */
private string $ObjectMail = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Rating request sent on */
private ?\DateTime $PkgShopRatingServiceRatingProcessedOn = null, 
    // TCMSFieldVarchar
/** @var string - VAT ID */
private string $VatId = '', 
    // TCMSFieldText
/** @var string - Internal comment */
private string $InternalComment = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - Date of shipment of all products */
private ?\DateTime $PkgShopRatingServiceOrderCompletelyShipped = null  ) {}

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
public function ispkgShopRatingServiceMailProcessed(): bool
{
    return $this->PkgShopRatingServiceMailProcessed;
}
public function setpkgShopRatingServiceMailProcessed(bool $PkgShopRatingServiceMailProcessed): self
{
    $this->PkgShopRatingServiceMailProcessed = $PkgShopRatingServiceMailProcessed;

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getShop(): ?shop
{
    return $this->Shop;
}

public function setShop(?shop $Shop): self
{
    $this->Shop = $Shop;

    return $this;
}


  
    // TCMSFieldBoolean
public function isnewsletterSignup(): bool
{
    return $this->NewsletterSignup;
}
public function setnewsletterSignup(bool $NewsletterSignup): self
{
    $this->NewsletterSignup = $NewsletterSignup;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentIpnMessage>
*/
public function getPkgShopPaymentIpnMessageCollection(): Collection
{
    return $this->PkgShopPaymentIpnMessageCollection;
}

public function addPkgShopPaymentIpnMessageCollection(pkgShopPaymentIpnMessage $PkgShopPaymentIpnMessage): self
{
    if (!$this->PkgShopPaymentIpnMessageCollection->contains($PkgShopPaymentIpnMessage)) {
        $this->PkgShopPaymentIpnMessageCollection->add($PkgShopPaymentIpnMessage);
        $PkgShopPaymentIpnMessage->setShopOrder($this);
    }

    return $this;
}

public function removePkgShopPaymentIpnMessageCollection(pkgShopPaymentIpnMessage $PkgShopPaymentIpnMessage): self
{
    if ($this->PkgShopPaymentIpnMessageCollection->removeElement($PkgShopPaymentIpnMessage)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentIpnMessage->getShopOrder() === $this) {
            $PkgShopPaymentIpnMessage->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, amazonPaymentIdMapping>
*/
public function getAmazonPaymentIdMappingCollection(): Collection
{
    return $this->AmazonPaymentIdMappingCollection;
}

public function addAmazonPaymentIdMappingCollection(amazonPaymentIdMapping $AmazonPaymentIdMapping): self
{
    if (!$this->AmazonPaymentIdMappingCollection->contains($AmazonPaymentIdMapping)) {
        $this->AmazonPaymentIdMappingCollection->add($AmazonPaymentIdMapping);
        $AmazonPaymentIdMapping->setShopOrder($this);
    }

    return $this;
}

public function removeAmazonPaymentIdMappingCollection(amazonPaymentIdMapping $AmazonPaymentIdMapping): self
{
    if ($this->AmazonPaymentIdMappingCollection->removeElement($AmazonPaymentIdMapping)) {
        // set the owning side to null (unless already changed)
        if ($AmazonPaymentIdMapping->getShopOrder() === $this) {
            $AmazonPaymentIdMapping->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, pkgShopPaymentTransaction>
*/
public function getPkgShopPaymentTransactionCollection(): Collection
{
    return $this->PkgShopPaymentTransactionCollection;
}

public function addPkgShopPaymentTransactionCollection(pkgShopPaymentTransaction $PkgShopPaymentTransaction): self
{
    if (!$this->PkgShopPaymentTransactionCollection->contains($PkgShopPaymentTransaction)) {
        $this->PkgShopPaymentTransactionCollection->add($PkgShopPaymentTransaction);
        $PkgShopPaymentTransaction->setShopOrder($this);
    }

    return $this;
}

public function removePkgShopPaymentTransactionCollection(pkgShopPaymentTransaction $PkgShopPaymentTransaction): self
{
    if ($this->PkgShopPaymentTransactionCollection->removeElement($PkgShopPaymentTransaction)) {
        // set the owning side to null (unless already changed)
        if ($PkgShopPaymentTransaction->getShopOrder() === $this) {
            $PkgShopPaymentTransaction->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getCmsPortal(): ?cmsPortal
{
    return $this->CmsPortal;
}

public function setCmsPortal(?cmsPortal $CmsPortal): self
{
    $this->CmsPortal = $CmsPortal;

    return $this;
}


  
    // TCMSFieldNumber
public function getordernumber(): int
{
    return $this->Ordernumber;
}
public function setordernumber(int $Ordernumber): self
{
    $this->Ordernumber = $Ordernumber;

    return $this;
}


  
    // TCMSFieldBoolean
public function ispkgShopRatingServiceMailSent(): bool
{
    return $this->PkgShopRatingServiceMailSent;
}
public function setpkgShopRatingServiceMailSent(bool $PkgShopRatingServiceMailSent): self
{
    $this->PkgShopRatingServiceMailSent = $PkgShopRatingServiceMailSent;

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopRatingService(): ?pkgShopRatingService
{
    return $this->PkgShopRatingService;
}

public function setPkgShopRatingService(?pkgShopRatingService $PkgShopRatingService): self
{
    $this->PkgShopRatingService = $PkgShopRatingService;

    return $this;
}


  
    // TCMSFieldVarcharUnique
public function getorderIdent(): string
{
    return $this->OrderIdent;
}
public function setorderIdent(string $OrderIdent): self
{
    $this->OrderIdent = $OrderIdent;

    return $this;
}


  
    // TCMSFieldDateTime
public function getdatecreated(): ?\DateTime
{
    return $this->Datecreated;
}
public function setdatecreated(?\DateTime $Datecreated): self
{
    $this->Datecreated = $Datecreated;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderItem>
*/
public function getShopOrderItemCollection(): Collection
{
    return $this->ShopOrderItemCollection;
}

public function addShopOrderItemCollection(shopOrderItem $ShopOrderItem): self
{
    if (!$this->ShopOrderItemCollection->contains($ShopOrderItem)) {
        $this->ShopOrderItemCollection->add($ShopOrderItem);
        $ShopOrderItem->setShopOrder($this);
    }

    return $this;
}

public function removeShopOrderItemCollection(shopOrderItem $ShopOrderItem): self
{
    if ($this->ShopOrderItemCollection->removeElement($ShopOrderItem)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderItem->getShopOrder() === $this) {
            $ShopOrderItem->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupParentID
public function getDataExtranetUser(): ?dataExtranetUser
{
    return $this->DataExtranetUser;
}

public function setDataExtranetUser(?dataExtranetUser $DataExtranetUser): self
{
    $this->DataExtranetUser = $DataExtranetUser;

    return $this;
}


  
    // TCMSFieldNumber
public function getcustomerNumber(): int
{
    return $this->CustomerNumber;
}
public function setcustomerNumber(int $CustomerNumber): self
{
    $this->CustomerNumber = $CustomerNumber;

    return $this;
}


  
    // TCMSFieldEmail
public function getuserEmail(): string
{
    return $this->UserEmail;
}
public function setuserEmail(string $UserEmail): self
{
    $this->UserEmail = $UserEmail;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingCompany(): string
{
    return $this->AdrBillingCompany;
}
public function setadrBillingCompany(string $AdrBillingCompany): self
{
    $this->AdrBillingCompany = $AdrBillingCompany;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getAdrBillingSalutation(): ?dataExtranetSalutation
{
    return $this->AdrBillingSalutation;
}

public function setAdrBillingSalutation(?dataExtranetSalutation $AdrBillingSalutation): self
{
    $this->AdrBillingSalutation = $AdrBillingSalutation;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingFirstname(): string
{
    return $this->AdrBillingFirstname;
}
public function setadrBillingFirstname(string $AdrBillingFirstname): self
{
    $this->AdrBillingFirstname = $AdrBillingFirstname;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingLastname(): string
{
    return $this->AdrBillingLastname;
}
public function setadrBillingLastname(string $AdrBillingLastname): self
{
    $this->AdrBillingLastname = $AdrBillingLastname;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingAdditionalInfo(): string
{
    return $this->AdrBillingAdditionalInfo;
}
public function setadrBillingAdditionalInfo(string $AdrBillingAdditionalInfo): self
{
    $this->AdrBillingAdditionalInfo = $AdrBillingAdditionalInfo;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingStreet(): string
{
    return $this->AdrBillingStreet;
}
public function setadrBillingStreet(string $AdrBillingStreet): self
{
    $this->AdrBillingStreet = $AdrBillingStreet;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingStreetnr(): string
{
    return $this->AdrBillingStreetnr;
}
public function setadrBillingStreetnr(string $AdrBillingStreetnr): self
{
    $this->AdrBillingStreetnr = $AdrBillingStreetnr;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingCity(): string
{
    return $this->AdrBillingCity;
}
public function setadrBillingCity(string $AdrBillingCity): self
{
    $this->AdrBillingCity = $AdrBillingCity;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingPostalcode(): string
{
    return $this->AdrBillingPostalcode;
}
public function setadrBillingPostalcode(string $AdrBillingPostalcode): self
{
    $this->AdrBillingPostalcode = $AdrBillingPostalcode;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getAdrBillingCountry(): ?dataCountry
{
    return $this->AdrBillingCountry;
}

public function setAdrBillingCountry(?dataCountry $AdrBillingCountry): self
{
    $this->AdrBillingCountry = $AdrBillingCountry;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingTelefon(): string
{
    return $this->AdrBillingTelefon;
}
public function setadrBillingTelefon(string $AdrBillingTelefon): self
{
    $this->AdrBillingTelefon = $AdrBillingTelefon;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrBillingFax(): string
{
    return $this->AdrBillingFax;
}
public function setadrBillingFax(string $AdrBillingFax): self
{
    $this->AdrBillingFax = $AdrBillingFax;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getCmsLanguage(): ?cmsLanguage
{
    return $this->CmsLanguage;
}

public function setCmsLanguage(?cmsLanguage $CmsLanguage): self
{
    $this->CmsLanguage = $CmsLanguage;

    return $this;
}


  
    // TCMSFieldVarchar
public function getuserIp(): string
{
    return $this->UserIp;
}
public function setuserIp(string $UserIp): self
{
    $this->UserIp = $UserIp;

    return $this;
}


  
    // TCMSFieldBoolean
public function isadrShippingUseBilling(): bool
{
    return $this->AdrShippingUseBilling;
}
public function setadrShippingUseBilling(bool $AdrShippingUseBilling): self
{
    $this->AdrShippingUseBilling = $AdrShippingUseBilling;

    return $this;
}


  
    // TCMSFieldBoolean
public function isadrShippingIsDhlPackstation(): bool
{
    return $this->AdrShippingIsDhlPackstation;
}
public function setadrShippingIsDhlPackstation(bool $AdrShippingIsDhlPackstation): self
{
    $this->AdrShippingIsDhlPackstation = $AdrShippingIsDhlPackstation;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingCompany(): string
{
    return $this->AdrShippingCompany;
}
public function setadrShippingCompany(string $AdrShippingCompany): self
{
    $this->AdrShippingCompany = $AdrShippingCompany;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getAdrShippingSalutation(): ?dataExtranetSalutation
{
    return $this->AdrShippingSalutation;
}

public function setAdrShippingSalutation(?dataExtranetSalutation $AdrShippingSalutation): self
{
    $this->AdrShippingSalutation = $AdrShippingSalutation;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingFirstname(): string
{
    return $this->AdrShippingFirstname;
}
public function setadrShippingFirstname(string $AdrShippingFirstname): self
{
    $this->AdrShippingFirstname = $AdrShippingFirstname;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingLastname(): string
{
    return $this->AdrShippingLastname;
}
public function setadrShippingLastname(string $AdrShippingLastname): self
{
    $this->AdrShippingLastname = $AdrShippingLastname;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingAdditionalInfo(): string
{
    return $this->AdrShippingAdditionalInfo;
}
public function setadrShippingAdditionalInfo(string $AdrShippingAdditionalInfo): self
{
    $this->AdrShippingAdditionalInfo = $AdrShippingAdditionalInfo;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingStreet(): string
{
    return $this->AdrShippingStreet;
}
public function setadrShippingStreet(string $AdrShippingStreet): self
{
    $this->AdrShippingStreet = $AdrShippingStreet;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingStreetnr(): string
{
    return $this->AdrShippingStreetnr;
}
public function setadrShippingStreetnr(string $AdrShippingStreetnr): self
{
    $this->AdrShippingStreetnr = $AdrShippingStreetnr;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingCity(): string
{
    return $this->AdrShippingCity;
}
public function setadrShippingCity(string $AdrShippingCity): self
{
    $this->AdrShippingCity = $AdrShippingCity;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingPostalcode(): string
{
    return $this->AdrShippingPostalcode;
}
public function setadrShippingPostalcode(string $AdrShippingPostalcode): self
{
    $this->AdrShippingPostalcode = $AdrShippingPostalcode;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getAdrShippingCountry(): ?dataCountry
{
    return $this->AdrShippingCountry;
}

public function setAdrShippingCountry(?dataCountry $AdrShippingCountry): self
{
    $this->AdrShippingCountry = $AdrShippingCountry;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingTelefon(): string
{
    return $this->AdrShippingTelefon;
}
public function setadrShippingTelefon(string $AdrShippingTelefon): self
{
    $this->AdrShippingTelefon = $AdrShippingTelefon;

    return $this;
}


  
    // TCMSFieldVarchar
public function getadrShippingFax(): string
{
    return $this->AdrShippingFax;
}
public function setadrShippingFax(string $AdrShippingFax): self
{
    $this->AdrShippingFax = $AdrShippingFax;

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


  
    // TCMSFieldVarchar
public function getshopShippingGroupName(): string
{
    return $this->ShopShippingGroupName;
}
public function setshopShippingGroupName(string $ShopShippingGroupName): self
{
    $this->ShopShippingGroupName = $ShopShippingGroupName;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopShippingGroupPrice(): float
{
    return $this->ShopShippingGroupPrice;
}
public function setshopShippingGroupPrice(float $ShopShippingGroupPrice): self
{
    $this->ShopShippingGroupPrice = $ShopShippingGroupPrice;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopShippingGroupVatPercent(): float
{
    return $this->ShopShippingGroupVatPercent;
}
public function setshopShippingGroupVatPercent(float $ShopShippingGroupVatPercent): self
{
    $this->ShopShippingGroupVatPercent = $ShopShippingGroupVatPercent;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderShippingGroupParameter>
*/
public function getShopOrderShippingGroupParameterCollection(): Collection
{
    return $this->ShopOrderShippingGroupParameterCollection;
}

public function addShopOrderShippingGroupParameterCollection(shopOrderShippingGroupParameter $ShopOrderShippingGroupParameter): self
{
    if (!$this->ShopOrderShippingGroupParameterCollection->contains($ShopOrderShippingGroupParameter)) {
        $this->ShopOrderShippingGroupParameterCollection->add($ShopOrderShippingGroupParameter);
        $ShopOrderShippingGroupParameter->setShopOrder($this);
    }

    return $this;
}

public function removeShopOrderShippingGroupParameterCollection(shopOrderShippingGroupParameter $ShopOrderShippingGroupParameter): self
{
    if ($this->ShopOrderShippingGroupParameterCollection->removeElement($ShopOrderShippingGroupParameter)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderShippingGroupParameter->getShopOrder() === $this) {
            $ShopOrderShippingGroupParameter->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookup
public function getShopPaymentMethod(): ?shopPaymentMethod
{
    return $this->ShopPaymentMethod;
}

public function setShopPaymentMethod(?shopPaymentMethod $ShopPaymentMethod): self
{
    $this->ShopPaymentMethod = $ShopPaymentMethod;

    return $this;
}


  
    // TCMSFieldVarchar
public function getshopPaymentMethodName(): string
{
    return $this->ShopPaymentMethodName;
}
public function setshopPaymentMethodName(string $ShopPaymentMethodName): self
{
    $this->ShopPaymentMethodName = $ShopPaymentMethodName;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopPaymentMethodPrice(): float
{
    return $this->ShopPaymentMethodPrice;
}
public function setshopPaymentMethodPrice(float $ShopPaymentMethodPrice): self
{
    $this->ShopPaymentMethodPrice = $ShopPaymentMethodPrice;

    return $this;
}


  
    // TCMSFieldDecimal
public function getshopPaymentMethodVatPercent(): float
{
    return $this->ShopPaymentMethodVatPercent;
}
public function setshopPaymentMethodVatPercent(float $ShopPaymentMethodVatPercent): self
{
    $this->ShopPaymentMethodVatPercent = $ShopPaymentMethodVatPercent;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderPaymentMethodParameter>
*/
public function getShopOrderPaymentMethodParameterCollection(): Collection
{
    return $this->ShopOrderPaymentMethodParameterCollection;
}

public function addShopOrderPaymentMethodParameterCollection(shopOrderPaymentMethodParameter $ShopOrderPaymentMethodParameter): self
{
    if (!$this->ShopOrderPaymentMethodParameterCollection->contains($ShopOrderPaymentMethodParameter)) {
        $this->ShopOrderPaymentMethodParameterCollection->add($ShopOrderPaymentMethodParameter);
        $ShopOrderPaymentMethodParameter->setShopOrder($this);
    }

    return $this;
}

public function removeShopOrderPaymentMethodParameterCollection(shopOrderPaymentMethodParameter $ShopOrderPaymentMethodParameter): self
{
    if ($this->ShopOrderPaymentMethodParameterCollection->removeElement($ShopOrderPaymentMethodParameter)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderPaymentMethodParameter->getShopOrder() === $this) {
            $ShopOrderPaymentMethodParameter->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderVat>
*/
public function getShopOrderVatCollection(): Collection
{
    return $this->ShopOrderVatCollection;
}

public function addShopOrderVatCollection(shopOrderVat $ShopOrderVat): self
{
    if (!$this->ShopOrderVatCollection->contains($ShopOrderVat)) {
        $this->ShopOrderVatCollection->add($ShopOrderVat);
        $ShopOrderVat->setShopOrder($this);
    }

    return $this;
}

public function removeShopOrderVatCollection(shopOrderVat $ShopOrderVat): self
{
    if ($this->ShopOrderVatCollection->removeElement($ShopOrderVat)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderVat->getShopOrder() === $this) {
            $ShopOrderVat->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueArticle(): float
{
    return $this->ValueArticle;
}
public function setvalueArticle(float $ValueArticle): self
{
    $this->ValueArticle = $ValueArticle;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueTotal(): float
{
    return $this->ValueTotal;
}
public function setvalueTotal(float $ValueTotal): self
{
    $this->ValueTotal = $ValueTotal;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getPkgShopCurrency(): ?pkgShopCurrency
{
    return $this->PkgShopCurrency;
}

public function setPkgShopCurrency(?pkgShopCurrency $PkgShopCurrency): self
{
    $this->PkgShopCurrency = $PkgShopCurrency;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueWrapping(): float
{
    return $this->ValueWrapping;
}
public function setvalueWrapping(float $ValueWrapping): self
{
    $this->ValueWrapping = $ValueWrapping;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueWrappingCard(): float
{
    return $this->ValueWrappingCard;
}
public function setvalueWrappingCard(float $ValueWrappingCard): self
{
    $this->ValueWrappingCard = $ValueWrappingCard;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueVouchers(): float
{
    return $this->ValueVouchers;
}
public function setvalueVouchers(float $ValueVouchers): self
{
    $this->ValueVouchers = $ValueVouchers;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueVouchersNotSponsored(): float
{
    return $this->ValueVouchersNotSponsored;
}
public function setvalueVouchersNotSponsored(float $ValueVouchersNotSponsored): self
{
    $this->ValueVouchersNotSponsored = $ValueVouchersNotSponsored;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueDiscounts(): float
{
    return $this->ValueDiscounts;
}
public function setvalueDiscounts(float $ValueDiscounts): self
{
    $this->ValueDiscounts = $ValueDiscounts;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueVatTotal(): float
{
    return $this->ValueVatTotal;
}
public function setvalueVatTotal(float $ValueVatTotal): self
{
    $this->ValueVatTotal = $ValueVatTotal;

    return $this;
}


  
    // TCMSFieldDecimal
public function getcountArticles(): float
{
    return $this->CountArticles;
}
public function setcountArticles(float $CountArticles): self
{
    $this->CountArticles = $CountArticles;

    return $this;
}


  
    // TCMSFieldNumber
public function getcountUniqueArticles(): int
{
    return $this->CountUniqueArticles;
}
public function setcountUniqueArticles(int $CountUniqueArticles): self
{
    $this->CountUniqueArticles = $CountUniqueArticles;

    return $this;
}


  
    // TCMSFieldDecimal
public function gettotalweight(): float
{
    return $this->Totalweight;
}
public function settotalweight(float $Totalweight): self
{
    $this->Totalweight = $Totalweight;

    return $this;
}


  
    // TCMSFieldDecimal
public function gettotalvolume(): float
{
    return $this->Totalvolume;
}
public function settotalvolume(float $Totalvolume): self
{
    $this->Totalvolume = $Totalvolume;

    return $this;
}


  
    // TCMSFieldBoolean
public function issystemOrderSaveCompleted(): bool
{
    return $this->SystemOrderSaveCompleted;
}
public function setsystemOrderSaveCompleted(bool $SystemOrderSaveCompleted): self
{
    $this->SystemOrderSaveCompleted = $SystemOrderSaveCompleted;

    return $this;
}


  
    // TCMSFieldBoolean
public function issystemOrderNotificationSend(): bool
{
    return $this->SystemOrderNotificationSend;
}
public function setsystemOrderNotificationSend(bool $SystemOrderNotificationSend): self
{
    $this->SystemOrderNotificationSend = $SystemOrderNotificationSend;

    return $this;
}


  
    // TCMSFieldBoolean
public function issystemOrderPaymentMethodExecuted(): bool
{
    return $this->SystemOrderPaymentMethodExecuted;
}
public function setsystemOrderPaymentMethodExecuted(bool $SystemOrderPaymentMethodExecuted): self
{
    $this->SystemOrderPaymentMethodExecuted = $SystemOrderPaymentMethodExecuted;

    return $this;
}


  
    // TCMSFieldDateTime
public function getsystemOrderPaymentMethodExecutedDate(): ?\DateTime
{
    return $this->SystemOrderPaymentMethodExecutedDate;
}
public function setsystemOrderPaymentMethodExecutedDate(?\DateTime $SystemOrderPaymentMethodExecutedDate): self
{
    $this->SystemOrderPaymentMethodExecutedDate = $SystemOrderPaymentMethodExecutedDate;

    return $this;
}


  
    // TCMSFieldBoolean
public function isorderIsPaid(): bool
{
    return $this->OrderIsPaid;
}
public function setorderIsPaid(bool $OrderIsPaid): self
{
    $this->OrderIsPaid = $OrderIsPaid;

    return $this;
}


  
    // TCMSFieldDateTime
public function getorderIsPaidDate(): ?\DateTime
{
    return $this->OrderIsPaidDate;
}
public function setorderIsPaidDate(?\DateTime $OrderIsPaidDate): self
{
    $this->OrderIsPaidDate = $OrderIsPaidDate;

    return $this;
}


  
    // TCMSFieldBoolean
public function iscanceled(): bool
{
    return $this->Canceled;
}
public function setcanceled(bool $Canceled): self
{
    $this->Canceled = $Canceled;

    return $this;
}


  
    // TCMSFieldDateTime
public function getcanceledDate(): ?\DateTime
{
    return $this->CanceledDate;
}
public function setcanceledDate(?\DateTime $CanceledDate): self
{
    $this->CanceledDate = $CanceledDate;

    return $this;
}


  
    // TCMSFieldDateTime
public function getsystemOrderExportedDate(): ?\DateTime
{
    return $this->SystemOrderExportedDate;
}
public function setsystemOrderExportedDate(?\DateTime $SystemOrderExportedDate): self
{
    $this->SystemOrderExportedDate = $SystemOrderExportedDate;

    return $this;
}


  
    // TCMSFieldVarchar
public function getaffiliateCode(): string
{
    return $this->AffiliateCode;
}
public function setaffiliateCode(string $AffiliateCode): self
{
    $this->AffiliateCode = $AffiliateCode;

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopAffiliate(): ?pkgShopAffiliate
{
    return $this->PkgShopAffiliate;
}

public function setPkgShopAffiliate(?pkgShopAffiliate $PkgShopAffiliate): self
{
    $this->PkgShopAffiliate = $PkgShopAffiliate;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopVoucherUse>
*/
public function getShopVoucherUseCollection(): Collection
{
    return $this->ShopVoucherUseCollection;
}

public function addShopVoucherUseCollection(shopVoucherUse $ShopVoucherUse): self
{
    if (!$this->ShopVoucherUseCollection->contains($ShopVoucherUse)) {
        $this->ShopVoucherUseCollection->add($ShopVoucherUse);
        $ShopVoucherUse->setShopOrder($this);
    }

    return $this;
}

public function removeShopVoucherUseCollection(shopVoucherUse $ShopVoucherUse): self
{
    if ($this->ShopVoucherUseCollection->removeElement($ShopVoucherUse)) {
        // set the owning side to null (unless already changed)
        if ($ShopVoucherUse->getShopOrder() === $this) {
            $ShopVoucherUse->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderDiscount>
*/
public function getShopOrderDiscountCollection(): Collection
{
    return $this->ShopOrderDiscountCollection;
}

public function addShopOrderDiscountCollection(shopOrderDiscount $ShopOrderDiscount): self
{
    if (!$this->ShopOrderDiscountCollection->contains($ShopOrderDiscount)) {
        $this->ShopOrderDiscountCollection->add($ShopOrderDiscount);
        $ShopOrderDiscount->setShopOrder($this);
    }

    return $this;
}

public function removeShopOrderDiscountCollection(shopOrderDiscount $ShopOrderDiscount): self
{
    if ($this->ShopOrderDiscountCollection->removeElement($ShopOrderDiscount)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderDiscount->getShopOrder() === $this) {
            $ShopOrderDiscount->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopOrderStatus>
*/
public function getShopOrderStatusCollection(): Collection
{
    return $this->ShopOrderStatusCollection;
}

public function addShopOrderStatusCollection(shopOrderStatus $ShopOrderStatus): self
{
    if (!$this->ShopOrderStatusCollection->contains($ShopOrderStatus)) {
        $this->ShopOrderStatusCollection->add($ShopOrderStatus);
        $ShopOrderStatus->setShopOrder($this);
    }

    return $this;
}

public function removeShopOrderStatusCollection(shopOrderStatus $ShopOrderStatus): self
{
    if ($this->ShopOrderStatusCollection->removeElement($ShopOrderStatus)) {
        // set the owning side to null (unless already changed)
        if ($ShopOrderStatus->getShopOrder() === $this) {
            $ShopOrderStatus->setShopOrder(null);
        }
    }

    return $this;
}


  
    // TCMSFieldText
public function getobjectMail(): string
{
    return $this->ObjectMail;
}
public function setobjectMail(string $ObjectMail): self
{
    $this->ObjectMail = $ObjectMail;

    return $this;
}


  
    // TCMSFieldDateTime
public function getpkgShopRatingServiceRatingProcessedOn(): ?\DateTime
{
    return $this->PkgShopRatingServiceRatingProcessedOn;
}
public function setpkgShopRatingServiceRatingProcessedOn(?\DateTime $PkgShopRatingServiceRatingProcessedOn): self
{
    $this->PkgShopRatingServiceRatingProcessedOn = $PkgShopRatingServiceRatingProcessedOn;

    return $this;
}


  
    // TCMSFieldVarchar
public function getvatId(): string
{
    return $this->VatId;
}
public function setvatId(string $VatId): self
{
    $this->VatId = $VatId;

    return $this;
}


  
    // TCMSFieldText
public function getinternalComment(): string
{
    return $this->InternalComment;
}
public function setinternalComment(string $InternalComment): self
{
    $this->InternalComment = $InternalComment;

    return $this;
}


  
    // TCMSFieldDateTime
public function getpkgShopRatingServiceOrderCompletelyShipped(): ?\DateTime
{
    return $this->PkgShopRatingServiceOrderCompletelyShipped;
}
public function setpkgShopRatingServiceOrderCompletelyShipped(?\DateTime $PkgShopRatingServiceOrderCompletelyShipped): self
{
    $this->PkgShopRatingServiceOrderCompletelyShipped = $PkgShopRatingServiceOrderCompletelyShipped;

    return $this;
}


  
}
