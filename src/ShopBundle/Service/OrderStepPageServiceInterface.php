<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Service;

/**
 * SystemPageServiceInterface defines a service that provides methods to get information on system pages.
 */
interface OrderStepPageServiceInterface
{
    /**
     * Returns a URL to the page associated with the passed order step for the passed portal in the passed language.
     * Note that this link might be absolute if it requires HTTPS access.
     *
     * @param array $parameters an array of key-value parameters to add to the URL. You may also pass the 'domain' parameter
     *                          to generate the URL for a domain other than the default. When doing this, ask an implementation
     *                          of ChameleonSystem\CoreBundle\Util\RoutingUtilInterface::getHostRequirementPlaceholder()
     *                          for the exact name of the domain parameter. The domain parameter has no effect if the
     *                          resulting URL is relative.
     * @param \TdbCmsPortal|null $portal if null, the active portal is used
     * @param \TdbCmsLanguage|null $language if null, the active language is used
     *
     * @return string
     */
    public function getLinkToOrderStepPageRelative(
        \TShopOrderStep $orderStep,
        array $parameters = [],
        ?\TdbCmsPortal $portal = null,
        ?\TdbCmsLanguage $language = null
    );

    /**
     * Returns a URL to the page associated with the passed order step for the passed portal in the passed language.
     *
     * @param array $parameters an array of key-value parameters to add to the URL. You may also pass the 'domain' parameter
     *                          to generate the URL for a domain other than the default. When doing this, ask an implementation
     *                          of ChameleonSystem\CoreBundle\Util\RoutingUtilInterface::getHostRequirementPlaceholder()
     *                          for the exact name of the domain parameter.
     * @param \TdbCmsPortal|null $portal if null, the active portal is used
     * @param \TdbCmsLanguage|null $language if null, the active language is used
     * @param bool $forceSecure if true, the resulting URL will be an HTTPS URL in any case
     *
     * @return string
     */
    public function getLinkToOrderStepPageAbsolute(
        \TShopOrderStep $orderStep,
        array $parameters = [],
        ?\TdbCmsPortal $portal = null,
        ?\TdbCmsLanguage $language = null,
        $forceSecure = false
    );
}
