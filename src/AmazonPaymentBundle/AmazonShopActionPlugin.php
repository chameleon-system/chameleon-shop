<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle;

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use TdbShopOrderStep;
use TPkgCmsException_LogAndMessage;

class AmazonShopActionPlugin extends \AbstractPkgActionPlugin
{
    public function setAmazonOrderReferenceId($requestParameter)
    {
        $amazonOrderReferenceId = isset($requestParameter['amazonOrderReferenceId']) ? $requestParameter['amazonOrderReferenceId'] : null;
        if (null === $amazonOrderReferenceId) {
            return;
        }

        $basket = \TShopBasket::GetInstance();
        try {
            $basket->setAmazonOrderReferenceId($amazonOrderReferenceId, \TdbDataExtranetUser::GetInstance());
            $basket->aCompletedOrderStepList['user'] = true;
            $basketStep = TdbShopOrderStep::GetStep('shippingaddress');
            $this->getRedirect()->redirect($basketStep->GetStepURL());
        } catch (TPkgCmsException_LogAndMessage $e) {
            $this->handleError($e->getMessageCode(), $e->getMessage());
            $basketStep = TdbShopOrderStep::GetStep('basket');
            $this->getRedirect()->redirect($basketStep->GetStepURL());
        }
    }

    public function errorAmazonLogin($requestParameter)
    {
        $error = isset($requestParameter['error']) ? $requestParameter['error'] : null;
        $errorCode = isset($requestParameter['errorCode']) ? $requestParameter['errorCode'] : null;
        $this->handleError($error, $errorCode);
    }

    public function widgetError($requestParameter)
    {
        $error = isset($requestParameter['error']) ? $requestParameter['error'] : null;
        $errorCode = isset($requestParameter['errorCode']) ? $requestParameter['errorCode'] : null;
        $this->handleError($error, $errorCode);
    }

    private function handleError($error, $errorCode)
    {
        if (null !== $error) {
            \ChameleonSystem\CoreBundle\ServiceLocator::get('cmsPkgCore.logChannel.standard')->warning(
                'amazon error: '.$error,
                __FILE__,
                __LINE__,
                array('error' => $error, 'errorCode' => $errorCode)
            );
            $msgManager = \TCMSMessageManager::GetInstance();

            $msgManager->AddMessage(
                'amazonPayment',
                AmazonPayment::ERROR_CODE_API_ERROR
            );
        }

        $basket = \TShopBasket::GetInstance();
        $basket->resetAmazonPaymentReferenceData();
        $basket->aCompletedOrderStepList['user'] = false;

        $url = $this->getSystemPageService()->getLinkToSystemPageRelative('checkout');
        $this->getRedirect()->redirect($url);
    }

    /**
     * @return \ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }
}
