<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\pkgShop;

use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentConfigFactory;
use ChameleonSystem\CoreBundle\Interfaces\FlashMessageServiceInterface;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use TdbDataExtranetUser;
use TPkgCmsException_LogAndMessage;

class AmazonPaymentBasket extends \ChameleonSystemAmazonPaymentBundlepkgShopAmazonPaymentBasketAutoParent
{
    private $amazonOrderReferenceValue = null;
    private $amazonOrderReferenceId = null;

    /**
     * If an error occurs during the Amazon Pay checkout, this variable is set to true.
     *
     * @var bool
     */
    private $errorDuringAmazonCheckout = false;

    /**
     * @return null|string
     */
    public function getAmazonOrderReferenceId()
    {
        return $this->amazonOrderReferenceId;
    }

    /**
     * @param null                $amazonOrderReferenceId
     * @param TdbDataExtranetUser $user                   the user for whom the Amazon payment will be enabled.
     *                                                    Note that the user object will be changed in memory, but not persistent
     *
     * @throws TPkgCmsException_LogAndMessage
     */
    public function setAmazonOrderReferenceId($amazonOrderReferenceId, TdbDataExtranetUser $user)
    {
        $this->amazonOrderReferenceId = $amazonOrderReferenceId;
        if (null === $this->amazonOrderReferenceId) {
            $this->resetAmazonPaymentReferenceData();
            $user->setAmazonPaymentEnabled(false);
        } else {
            try {
                $this->updateAmazonOrderReferenceDetails();
                $user->setAmazonPaymentEnabled(true);
            } catch (TPkgCmsException_LogAndMessage $e) {
                $this->resetAmazonPaymentReferenceData();
                $user->setAmazonPaymentEnabled(false);
                throw $e;
            }
        }
        $this->errorDuringAmazonCheckout = false;
    }

    public function resetAmazonPaymentReferenceData()
    {
        $this->amazonOrderReferenceId = null;
        $this->amazonOrderReferenceValue = null;
    }

    private function setAmazonPaymentError()
    {
        $this->resetAmazonPaymentReferenceData();
        $this->errorDuringAmazonCheckout = true;
    }

    /**
     * @return bool
     */
    public function hasAmazonPaymentError()
    {
        return $this->errorDuringAmazonCheckout;
    }

    public function RecalculateBasket()
    {
        parent::RecalculateBasket();
        if (null !== $this->amazonOrderReferenceValue && round($this->amazonOrderReferenceValue, 2) === round($this->dCostTotal, 2)) {
            return;
        }

        if (null === $this->getAmazonOrderReferenceId()) {
            return;
        }
        try {
            $this->updateAmazonOrderReferenceDetails();
            $this->amazonOrderReferenceValue = $this->dCostTotal;
        } catch (TPkgCmsException_LogAndMessage $e) {
            $activeUser = $this->getExtranetUserProvider()->getActiveUser();
            $activeUser->setAmazonPaymentEnabled(false);
            $this->setAmazonPaymentError();
            $this->getFlashMessageService()->addMessage('amazonPayment', AmazonPayment::ERROR_CODE_API_ERROR);

            return;
        }
    }

    /**
     * @throws TPkgCmsException_LogAndMessage
     */
    protected function updateAmazonOrderReferenceDetails()
    {
        if (null !== $this->getAmazonOrderReferenceId()) {
            $amazonOrderRefObject = AmazonPaymentConfigFactory::createConfig($this->getActivePortalId())->amazonOrderReferenceObjectFactory($this->getAmazonOrderReferenceId());
            $amazonOrderRefObject->setOrderReferenceOrderValue($this->dCostTotal);
        }
    }

    private function getActivePortalId()
    {
        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');

        return $portalDomainService->getActivePortal()->id;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    /**
     * @return FlashMessageServiceInterface
     */
    private function getFlashMessageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.flash_messages');
    }
}
