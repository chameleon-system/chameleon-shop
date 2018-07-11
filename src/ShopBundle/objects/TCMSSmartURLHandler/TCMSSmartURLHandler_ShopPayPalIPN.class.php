<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Exception\ConfigurationException;
use ChameleonSystem\ShopBundle\Payment\PaymentHandler\Interfaces\ShopPaymentHandlerFactoryInterface;

class TCMSSmartURLHandler_ShopPayPalIPN extends TCMSSmartURLHandler
{
    public function GetPageDef()
    {
        $iPageId = false;
        $oURLData = &TCMSSmartURLData::GetActive();
        if (false !== strpos($oURLData->sRelativeURL, TShopPaymentHandlerPayPal_PayViaLink::URL_IDENTIFIER_IPN)) {
            $oGlobal = TGlobal::instance();
            $sOrderId = null;
            $sPaymentHandlerId = null;
            if (!$oGlobal->UserDataExists('custom')) {
                TTools::WriteLogEntrySimple(
                    'PayPal IPN: parameter "custom" missing from paypal IPN response: '.print_r(
                        $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                        true
                    ),
                    1,
                    __FILE__,
                    __LINE__,
                    TShopPaymentHandlerPayPal::LOG_FILE
                );
            } else {
                $sCustomParameters = $oGlobal->GetUserData('custom', array(), TCMSUserInput::FILTER_DEFAULT);
                $aCustomParameters = explode(',', $sCustomParameters);
                if (2 != count($aCustomParameters)) {
                    TTools::WriteLogEntrySimple(
                        'PayPal IPN: parameter "custom" invalid from paypal IPN response: '.print_r(
                            $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                            true
                        ),
                        1,
                        __FILE__,
                        __LINE__,
                        TShopPaymentHandlerPayPal::LOG_FILE
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
                        /** @var $oPaymentHandler TShopPaymentHandlerPayPal_PayViaLink */
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
                        TTools::WriteLogEntrySimple(
                            "PayPal IPN: failed to load payment handler {$sPaymentHandlerId}: ".print_r(
                                $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                                true
                            ),
                            1,
                            __FILE__,
                            __LINE__,
                            TShopPaymentHandlerPayPal::LOG_FILE
                        );
                        $this->handleError();
                    }
                } else {
                    TTools::WriteLogEntrySimple(
                        "PayPal IPN: failed to load order [{$sOrderId}]: ".print_r(
                            $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                            true
                        ),
                        1,
                        __FILE__,
                        __LINE__,
                        TShopPaymentHandlerPayPal::LOG_FILE
                    );
                    $this->handleError();
                }
            } else {
                TTools::WriteLogEntrySimple(
                    'PayPal IPN: parameter "custom" missing from paypal IPN response: '.print_r(
                        $oGlobal->GetUserData(null, array(), TCMSUserInput::FILTER_NONE),
                        true
                    ),
                    1,
                    __FILE__,
                    __LINE__,
                    TShopPaymentHandlerPayPal::LOG_FILE
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
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.payment.handler_factory');
    }
}
