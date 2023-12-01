<?php

namespace ChameleonSystem\ShopPaymentTransactionBundle\Entity;

use ChameleonSystem\DataAccessBundle\Entity\Core\CmsUser;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\ShopOrder;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PkgShopPaymentTransaction
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Belongs to order */
        private ?ShopOrder $shopOrder = null
        ,
        // TCMSFieldExtendedLookup
        /** @var DataExtranetUser|null - Executed by user */
        private ?DataExtranetUser $dataExtranetUser = null
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, PkgShopPaymentTransactionPosition> - Positions */
        private Collection $pkgShopPaymentTransactionPositionCollection = new ArrayCollection()
        ,
        // TCMSFieldLookup
        /** @var PkgShopPaymentTransactionType|null - Transaction type */
        private ?PkgShopPaymentTransactionType $pkgShopPaymentTransactionType = null
        ,
        // TCMSFieldExtendedLookup
        /** @var CmsUser|null - Executed by CMS user */
        private ?CmsUser $cmsUser = null
        ,
        // TCMSFieldCreatedTimestamp
        /** @var DateTime|null - Created on */
        private ?DateTime $datecreated = null,
        // TCMSFieldVarchar
        /** @var string - Executed via IP */
        private string $ip = '',
        // TCMSFieldDecimal
        /** @var float - Value */
        private float $amount = 0,
        // TCMSFieldVarchar
        /** @var string - Context */
        private string $context = '',
        // TCMSFieldNumber
        /** @var int - Sequence number */
        private int $sequenceNumber = 0,
        // TCMSFieldBoolean
        /** @var bool - Confirmed */
        private bool $confirmed = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Confirmed on */
        private ?DateTime $confirmedDate = null
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
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getDataExtranetUser(): ?DataExtranetUser
    {
        return $this->dataExtranetUser;
    }

    public function setDataExtranetUser(?DataExtranetUser $dataExtranetUser): self
    {
        $this->dataExtranetUser = $dataExtranetUser;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, PkgShopPaymentTransactionPosition>
     */
    public function getPkgShopPaymentTransactionPositionCollection(): Collection
    {
        return $this->pkgShopPaymentTransactionPositionCollection;
    }

    public function addPkgShopPaymentTransactionPositionCollection(
        PkgShopPaymentTransactionPosition $pkgShopPaymentTransactionPosition
    ): self {
        if (!$this->pkgShopPaymentTransactionPositionCollection->contains($pkgShopPaymentTransactionPosition)) {
            $this->pkgShopPaymentTransactionPositionCollection->add($pkgShopPaymentTransactionPosition);
            $pkgShopPaymentTransactionPosition->setPkgShopPaymentTransaction($this);
        }

        return $this;
    }

    public function removePkgShopPaymentTransactionPositionCollection(
        PkgShopPaymentTransactionPosition $pkgShopPaymentTransactionPosition
    ): self {
        if ($this->pkgShopPaymentTransactionPositionCollection->removeElement($pkgShopPaymentTransactionPosition)) {
            // set the owning side to null (unless already changed)
            if ($pkgShopPaymentTransactionPosition->getPkgShopPaymentTransaction() === $this) {
                $pkgShopPaymentTransactionPosition->setPkgShopPaymentTransaction(null);
            }
        }

        return $this;
    }


    // TCMSFieldLookup
    public function getPkgShopPaymentTransactionType(): ?PkgShopPaymentTransactionType
    {
        return $this->pkgShopPaymentTransactionType;
    }

    public function setPkgShopPaymentTransactionType(?PkgShopPaymentTransactionType $pkgShopPaymentTransactionType
    ): self {
        $this->pkgShopPaymentTransactionType = $pkgShopPaymentTransactionType;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getCmsUser(): ?CmsUser
    {
        return $this->cmsUser;
    }

    public function setCmsUser(?CmsUser $cmsUser): self
    {
        $this->cmsUser = $cmsUser;

        return $this;
    }


    // TCMSFieldCreatedTimestamp
    public function getDatecreated(): ?DateTime
    {
        return $this->datecreated;
    }

    public function setDatecreated(?DateTime $datecreated): self
    {
        $this->datecreated = $datecreated;

        return $this;
    }


    // TCMSFieldVarchar
    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }


    // TCMSFieldDecimal
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }


    // TCMSFieldVarchar
    public function getContext(): string
    {
        return $this->context;
    }

    public function setContext(string $context): self
    {
        $this->context = $context;

        return $this;
    }


    // TCMSFieldNumber
    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function setSequenceNumber(int $sequenceNumber): self
    {
        $this->sequenceNumber = $sequenceNumber;

        return $this;
    }


    // TCMSFieldBoolean
    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }


    // TCMSFieldDateTime
    public function getConfirmedDate(): ?DateTime
    {
        return $this->confirmedDate;
    }

    public function setConfirmedDate(?DateTime $confirmedDate): self
    {
        $this->confirmedDate = $confirmedDate;

        return $this;
    }


}
