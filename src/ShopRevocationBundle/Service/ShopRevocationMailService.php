<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

use ChameleonSystem\ShopRevocationBundle\DataModel\RevocationMailDataModel;
use Psr\Log\LoggerInterface;

class ShopRevocationMailService
{
    private const CUSTOMER_MAIL_PROFILE = 'revocation_customer';
    private const SHOP_OWNER_MAIL_PROFILE = 'revocation_shop_owner';
    private const MAIL_TEMPLATE_SUBTYPE = 'emails';
    private const MAIL_TEMPLATE_TYPE = 'Customer';

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array<string, string> $customerAttachments
     * @param array<string, string> $shopOwnerAttachments
     */
    public function sendRevocationMails(
        RevocationMailDataModel $mailData,
        array $customerAttachments = [],
        array $shopOwnerAttachments = []
    ): void {
        $this->sendCustomerConfirmation($mailData, $customerAttachments);
        $this->sendShopOwnerNotification($mailData, $shopOwnerAttachments);
    }

    /**
     * @param array<string, string> $attachments
     */
    protected function sendCustomerConfirmation(RevocationMailDataModel $mailData, array $attachments = []): void
    {
        $mailProfile = $this->getMailProfile(self::CUSTOMER_MAIL_PROFILE, $mailData->getCustomerEmail());
        if (null === $mailProfile) {
            return;
        }

        $mailProfile->ChangeToAddress($mailData->getCustomerEmail(), $mailData->getCustomerName());
        $this->populateMailProfile($mailProfile, $mailData);

        $this->sendMailProfile($mailProfile, self::CUSTOMER_MAIL_PROFILE, $mailData, $mailData->getCustomerEmail(), $attachments);
    }

    /**
     * @param array<string, string> $attachments
     */
    protected function sendShopOwnerNotification(RevocationMailDataModel $mailData, array $attachments = []): void
    {
        $mailProfile = $this->getMailProfile(self::SHOP_OWNER_MAIL_PROFILE);
        if (null === $mailProfile) {
            return;
        }

        $recipientEmail = trim((string) $mailProfile->fieldMailto);
        if ('' === $recipientEmail) {
            $this->logger->warning('Skipped revocation mail because the configured mail profile has no recipient.', [
                'mailProfile' => self::SHOP_OWNER_MAIL_PROFILE,
                'revocationRecordId' => $mailData->getRevocationRecordId(),
                'orderId' => $mailData->getOrderId(),
                'orderNumber' => $mailData->getOrderNumber(),
            ]);

            return;
        }

        $this->populateMailProfile($mailProfile, $mailData);

        $this->sendMailProfile($mailProfile, self::SHOP_OWNER_MAIL_PROFILE, $mailData, $recipientEmail, $attachments);
    }

    protected function populateMailProfile(\TdbDataMailProfile $mailProfile, RevocationMailDataModel $mailData): void
    {
        $mailProfile->AddDataArray($mailData->getMailData());
    }

    protected function getMailProfile(string $profileIdcode, ?string $recipientEmail = null): ?\TdbDataMailProfile
    {
        $mailProfile = \TdbDataMailProfile::GetProfile($profileIdcode);
        if (null !== $mailProfile) {
            return $mailProfile;
        }

        $this->logger->warning('Skipped revocation mail because the mail profile could not be loaded.', [
            'mailProfile' => $profileIdcode,
            'recipientEmail' => $recipientEmail,
        ]);

        return null;
    }

    /**
     * @param array<string, string> $attachments
     */
    protected function sendMailProfile(
        \TdbDataMailProfile $mailProfile,
        string $profileIdcode,
        RevocationMailDataModel $mailData,
        string $recipientEmail,
        array $attachments = []
    ): void {
        try {
            if (true === $mailProfile->SendUsingObjectView(self::MAIL_TEMPLATE_SUBTYPE, self::MAIL_TEMPLATE_TYPE, $attachments)) {
                return;
            }

            $this->logger->warning('Failed to send revocation mail.', [
                'mailProfile' => $profileIdcode,
                'revocationRecordId' => $mailData->getRevocationRecordId(),
                'orderId' => $mailData->getOrderId(),
                'orderNumber' => $mailData->getOrderNumber(),
                'recipientEmail' => $recipientEmail,
            ]);
        } catch (\Throwable $exception) {
            $this->logger->warning('Failed to send revocation mail due to an exception.', [
                'mailProfile' => $profileIdcode,
                'revocationRecordId' => $mailData->getRevocationRecordId(),
                'orderId' => $mailData->getOrderId(),
                'orderNumber' => $mailData->getOrderNumber(),
                'recipientEmail' => $recipientEmail,
                'exception' => $exception,
            ]);
        }
    }
}
