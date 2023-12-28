<?php

namespace ChameleonSystem\ShopPaymentIPNBundle\Entity;

use DateTime;

class PkgShopPaymentIpnMessageTrigger
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var PkgShopPaymentIpnTrigger|null - Trigger */
        private ?PkgShopPaymentIpnTrigger $pkgShopPaymentIpnTrigger = null
        ,
        // TCMSFieldLookupParentID
        /** @var PkgShopPaymentIpnMessage|null - IPN Message */
        private ?PkgShopPaymentIpnMessage $pkgShopPaymentIpnMessage = null
        ,
        // TCMSFieldCreatedTimestamp
        /** @var DateTime|null - Created on */
        private ?DateTime $datecreated = null,
        // TCMSFieldBoolean
        /** @var bool - Processed */
        private bool $done = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Processed on */
        private ?DateTime $doneDate = null,
        // TCMSFieldBoolean
        /** @var bool - Successful */
        private bool $success = false,
        // TCMSFieldNumber
        /** @var int - Number of attempts */
        private int $attemptCount = 0,
        // TCMSFieldDateTime
        /** @var DateTime|null - Next attempt on */
        private ?DateTime $nextAttempt = null,
        // TCMSFieldText
        /** @var string - Log */
        private string $log = ''
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
    public function getPkgShopPaymentIpnTrigger(): ?PkgShopPaymentIpnTrigger
    {
        return $this->pkgShopPaymentIpnTrigger;
    }

    public function setPkgShopPaymentIpnTrigger(?PkgShopPaymentIpnTrigger $pkgShopPaymentIpnTrigger): self
    {
        $this->pkgShopPaymentIpnTrigger = $pkgShopPaymentIpnTrigger;

        return $this;
    }


    // TCMSFieldLookupParentID
    public function getPkgShopPaymentIpnMessage(): ?PkgShopPaymentIpnMessage
    {
        return $this->pkgShopPaymentIpnMessage;
    }

    public function setPkgShopPaymentIpnMessage(?PkgShopPaymentIpnMessage $pkgShopPaymentIpnMessage): self
    {
        $this->pkgShopPaymentIpnMessage = $pkgShopPaymentIpnMessage;

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


    // TCMSFieldBoolean
    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

        return $this;
    }


    // TCMSFieldDateTime
    public function getDoneDate(): ?DateTime
    {
        return $this->doneDate;
    }

    public function setDoneDate(?DateTime $doneDate): self
    {
        $this->doneDate = $doneDate;

        return $this;
    }


    // TCMSFieldBoolean
    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }


    // TCMSFieldNumber
    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }

    public function setAttemptCount(int $attemptCount): self
    {
        $this->attemptCount = $attemptCount;

        return $this;
    }


    // TCMSFieldDateTime
    public function getNextAttempt(): ?DateTime
    {
        return $this->nextAttempt;
    }

    public function setNextAttempt(?DateTime $nextAttempt): self
    {
        $this->nextAttempt = $nextAttempt;

        return $this;
    }


    // TCMSFieldText
    public function getLog(): string
    {
        return $this->log;
    }

    public function setLog(string $log): self
    {
        $this->log = $log;

        return $this;
    }


}
