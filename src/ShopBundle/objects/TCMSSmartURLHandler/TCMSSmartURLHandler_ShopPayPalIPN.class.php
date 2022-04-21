<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopBundle\Exception\ConfigurationException;
use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerFactoryInterface;
use Psr\Log\LoggerInterface;

class TCMSSmartURLHandler_ShopPayPalIPN extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $logger = $this->getLogger();

        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();
        if (false !== strpos($oURLData->sRelativeURL, TShopPaymentHandlerPayPal_PayViaLink::URL_IDENTIFIER_IPN)) {
            $oGlobal = TGlobal::instance();
            $sOrderId = null;
            $sPaymentHandlerId = null;
            if (!$oGlobal->UserDataExists('custom')) {
                $logger->error(
                    'PayPal IPN: parameter "custom" missing from paypal IPN response: '.print_r(
                        $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                        true
                    )
                );
            } else {
                $sCustomParameters = $oGlobal->GetUserData('custom', array(), TCMSUserInput::FILTER_DEFAULT);
                $aCustomParameters = explode(',', $sCustomParameters);
                if (2 != count($aCustomParameters)) {
                    $logger->error(
                        'PayPal IPN: parameter "custom" invalid from paypal IPN response: '.print_r(
                            $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                            true
                        )
                    );
                } else {
                    $sPaymentHandlerId = $aCustomParameters[0];
                    $sOrderId = $aCustomParameters[1];
                }
            }
            if (!is_null($sOrderId) && !is_null($sPaymentHandlerId)) {
                $oOrder = TdbShopOrder::GetNewInstance();
                $orderLoaded = $oOrder->Load($sOrderId);
                if ($orderLoaded) {
                    try {
                        /** @var TShopPaymentHandlerPayPal_PayViaLink $oPaymentHandler */
                        $oPaymentHandler = $this->getShopPaymentHandlerFactory()->createPaymentHandler(
                            $sPaymentHandlerId,
                            $oOrder->fieldCmsPortalId
                        );
                        if ($oPaymentHandler->ProcessIPNRequest(
                            $oOrder,
                            $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE)
                        )
                        ) {
                            header('HTTP/1.1 200 OK');
                            die(0);
                        } else {
                            header(
                                'HTTP/1.1 200 OK'
                            ); // also return a 200 on error - to prevent paypal from resending the request
                            die(0);
                        }
                    } catch (ConfigurationException $e) {
                        $logger->error(
                            "PayPal IPN: failed to load payment handler {$sPaymentHandlerId}: ".print_r(
                                $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                                true
                            )
                        );
                        $this->handleError();
                    }
                } else {
                    $logger->error(
                        "PayPal IPN: failed to load order [{$sOrderId}]: ".print_r(
                            $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                            true
                        )
                    );
                    $this->handleError();
                }
            } else {
                $logger->error(
                    'PayPal IPN: parameter "custom" missing from paypal IPN response: '.print_r(
                        $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                        true
                    )
                );
                $this->handleError();
            }
        }

        return $iPageId;
    }

    private function handleError()
    {
        header('HTTP/1.1 400 Bad Request');
        die(0);
    }

    /**
     * @return ShopPaymentHandlerFactoryInterface
     */
    private function getShopPaymentHandlerFactory()
    {
        return ServiceLocator::get('chameleon_system_shop.payment.handler_factory');
    }

    private function getLogger(): LoggerInterface
    {
        return ServiceLocator::get('monolog.logger.order_payment_ipn');
    }
}
