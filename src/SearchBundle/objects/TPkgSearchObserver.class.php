<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class TPkgSearchObserver implements IPkgCmsEventObserver
{
    /**
     * search stats.
     *
     * @var array
     */
    private $aSearches = array();

    /**
     * @static
     *
     * @return TPkgSearchObserver
     */
    public static function &GetInstance()
    {
        static $oInstance = null;
        if (is_null($oInstance)) {
            $oInstance = new self();
            TPkgCmsEventManager::GetInstance()->RegisterObserver(TPkgCmsEvent::CONTEXT_CORE, TPkgCmsEvent::NAME_PRE_OUTPUT_CALLBACK_FUNCTION, $oInstance);
        }

        return $oInstance;
    }

    public function GetSearch($sSearchTypeIdentifier)
    {
        if (isset($this->aSearches[$sSearchTypeIdentifier])) {
            return $this->aSearches[$sSearchTypeIdentifier];
        } else {
            return null;
        }
    }

    public function AddSearch($sSearchTypeIdentifier, $iNumberOfResults)
    {
        $this->aSearches[$sSearchTypeIdentifier] = new stdClass();
        $this->aSearches[$sSearchTypeIdentifier]->iNumberOfResults = $iNumberOfResults;
    }

    /**
     * @param IPkgCmsEvent $oEvent
     *
     * @return \IPkgCmsEvent
     */
    public function PkgCmsEventNotify(IPkgCmsEvent $oEvent)
    {
        if (TPkgCmsEvent::CONTEXT_CORE === $oEvent->GetContext() && TPkgCmsEvent::NAME_PRE_OUTPUT_CALLBACK_FUNCTION === $oEvent->GetName()) {
            $oShop = $this->getShopService()->getActiveShop();
            if ($oShop->fieldRedirectToNotFoundPageProductSearchOnNoResults) {
                $iTotalResults = 0;
                foreach ($this->aSearches as $sSearchIdentifier => $oSearchData) {
                    $iTotalResults = $iTotalResults + $oSearchData->iNumberOfResults;
                }
                if (0 === $iTotalResults) {
                    $url = $this->getSystemPageService()->getLinkToSystemPageRelative('not-found-page-product-search', array(
                        'q' => $this->getInputFilterUtil()->getFilteredInput('q'),
                    ));
                    $this->getRedirect()->redirect($url);
                }
            }
        }

        return $oEvent;
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }

    /**
     * @return InputFilterUtilInterface
     */
    private function getInputFilterUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.input_filter');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
