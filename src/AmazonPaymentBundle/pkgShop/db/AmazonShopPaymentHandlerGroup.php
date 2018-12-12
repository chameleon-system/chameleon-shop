<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop\db;

use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\IPN\AmazonPaymentIPNInvalidException;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use TdbShopOrder;

class AmazonShopPaymentHandlerGroup extends \TdbShopPaymentHandlerGroup
{
    /**
     * @var AmazonPaymentGroupConfig
     */
    private $amazonConfig = null;

    /**
     * @var Request
     */
    private $request = null;

    /**
     * @var Connection
     */
    private $databaseConnection = null;

    /**
     * {@inheritdoc}
     */
    protected function getIPNHandlerChain()
    {
        return array(
            '\ChameleonSystem\AmazonPaymentBundle\IPN\AmazonIPNHandler',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function validateIPNRequestData(\TPkgShopPaymentIPNRequest $oRequest)
    {
        $payload = $oRequest->getRequestPayload();
        if (isset($payload['amazonNotificationObject']) && $payload['amazonNotificationObject'] instanceof \OffAmazonPaymentsNotifications_InvalidMessageException) {
            throw new AmazonPaymentIPNInvalidException($oRequest, 'there was an error parsing the Amazon IPN', 0, $payload['amazonNotificationObject']);
        }

        if (!isset($payload['amazonReferenceIdManager'])) {
            throw new AmazonPaymentIPNInvalidException($oRequest, 'there was an error parsing the Amazon IPN (missing amazonReferenceIdManager)', 0, $payload['amazonNotificationObject']);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderFromRequestData($aRequestData)
    {
        if (!is_array($aRequestData) || false === isset($aRequestData['amazonReferenceIdManager'])) {
            return null;
        }
        /** @var $mapper AmazonReferenceIdManager */
        $mapper = $aRequestData['amazonReferenceIdManager'];
        $order = TdbShopOrder::GetNewInstance();
        if ($order->Load($mapper->getShopOrderId())) {
            return $order;
        }

        return null;
    }

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }

    /**
     * {@inheritdoc}
     */
    public function processRawRequestData($aRequestData)
    {
        $request = $this->getRequest();
        $headers = $this->getRequestHeader();
        $body = $request->getContent();
        $aRequestData['raw'] = array('header' => $headers, 'body' => $body);
        try {
            $config = $this->getAmazonConfig($this->getActivePortalId());

            $client = $config->getAmazonIPNAPI();
            $aRequestData['amazonNotificationObject'] = $client->parseRawMessage($headers, $body);

            $localId = null;
            /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Notification */
            $amazonNotificationObject = $aRequestData['amazonNotificationObject'];
            switch ($amazonNotificationObject->getNotificationType()) {
                case 'OrderReferenceNotification':
                    /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_OrderReferenceNotification */
                    $details = $amazonNotificationObject->getOrderReference();
                    $idManager = $config->amazonReferenceIdManagerFactory(
                        $this->getDatabaseConnection(),
                        AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_AMAZON_ORDER_REFERENCE_ID,
                        $details->getAmazonOrderReferenceId()
                    );
                    $aRequestData['amazonReferenceIdManager'] = $idManager;
                    break;
                case 'AuthorizationNotification':
                    /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_AuthorizationNotification */
                    $details = $amazonNotificationObject->getAuthorizationDetails();
                    $idManager = $config->amazonReferenceIdManagerFactory(
                        $this->getDatabaseConnection(),
                        AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID,
                        $details->getAuthorizationReferenceId()
                    );
                    $aRequestData['amazonLocalReferenceId'] = $idManager->findFromLocalReferenceId(
                        $details->getAuthorizationReferenceId(),
                        IAmazonReferenceId::TYPE_AUTHORIZE
                    );
                    $aRequestData['amazonReferenceIdManager'] = $idManager;

                    break;
                case 'CaptureNotification':
                    /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_CaptureNotification */
                    $details = $amazonNotificationObject->getCaptureDetails();
                    $idManager = $config->amazonReferenceIdManagerFactory(
                        $this->getDatabaseConnection(),
                        AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID,
                        $details->getCaptureReferenceId()
                    );
                    $aRequestData['amazonLocalReferenceId'] = $idManager->findFromLocalReferenceId(
                        $details->getCaptureReferenceId(),
                        IAmazonReferenceId::TYPE_CAPTURE
                    );
                    $aRequestData['amazonReferenceIdManager'] = $idManager;
                    break;
                case 'RefundNotification':
                    /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Model_RefundNotification */
                    $details = $amazonNotificationObject->getRefundDetails();
                    $idManager = $config->amazonReferenceIdManagerFactory(
                        $this->getDatabaseConnection(),
                        AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID,
                        $details->getRefundReferenceId()
                    );
                    $aRequestData['amazonLocalReferenceId'] = $idManager->findFromLocalReferenceId(
                        $details->getRefundReferenceId(),
                        IAmazonReferenceId::TYPE_REFUND
                    );
                    $aRequestData['amazonReferenceIdManager'] = $idManager;
                    break;
            }
        } catch (\OffAmazonPaymentsNotifications_InvalidMessageException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order_amazon')->error(
                'Amazon IPN failed '.$e->getMessage(),
                array('requestData' => $aRequestData, 'exception' => (string) $e)
            );

            return $aRequestData;
        } catch (\InvalidArgumentException $e) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order_amazon')->error(
                'Amazon IPN failed because no matching order was found',
                array('requestData' => $aRequestData)
            );

            return $aRequestData;
        }

        return $aRequestData;
    }

    /**
     * @return AmazonPaymentGroupConfig
     */
    protected function getAmazonConfig($portalId)
    {
        if (null !== $this->amazonConfig) {
            return $this->amazonConfig;
        }

        return AmazonPaymentConfigFactory::createConfig($portalId);
    }

    /**
     * @return array
     */
    protected function getRequestHeader()
    {
        $request = $this->getRequest();
        if (null === $request) {
            return array();
        }
        $headers = array();
        $headerKeys = $request->headers->keys();
        foreach ($headerKeys as $headerKey) {
            $headers[$headerKey] = $request->headers->get($headerKey);
        }

        return $headers;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (null !== $this->request) {
            return $this->request;
        }

        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getIPNStatus(\TPkgShopPaymentIPNRequest $oRequest)
    {
        $aPayload = $oRequest->getRequestPayload();
        /** @var $amazonNotificationObject \OffAmazonPaymentsNotifications_Notification */
        $amazonNotificationObject = $aPayload['amazonNotificationObject'];
        $oStatus = \TdbPkgShopPaymentIpnStatus::GetNewInstance();
        if (false === $oStatus->LoadFromFields(
                array(
                    'code' => $amazonNotificationObject->getNotificationType(),
                    'shop_payment_handler_group_id' => $this->id,
                )
            )
        ) {
            return null;
        }

        return $oStatus;
    }

    /**
     * @param Connection $connection
     */
    public function setDatabaseConnection(Connection $connection)
    {
        $this->databaseConnection = $connection;
    }

    protected function getDatabaseConnection()
    {
        if (null !== $this->databaseConnection) {
            return $this->databaseConnection;
        }

        return \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
    }
}
