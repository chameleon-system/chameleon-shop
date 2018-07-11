<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

class TPkgShopPaymentTransactionContextEndPoint
{
    private $context = null;
    private $extranetUser = null;
    private $ip = null;
    private $cmsUser = null;

    public function __construct($sContext)
    {
        $this->context = $sContext;
        $this->extranetUser = TdbDataExtranetUser::GetInstance();
        $request = $this->getCurrentRequest();
        $this->ip = null === $request ? '' : $request->getClientIp();
        $this->cmsUser = TCMSUser::GetActiveUser();
    }

    /**
     * @return null|\TdbCmsUser
     */
    public function getCmsUser()
    {
        return $this->cmsUser;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return null|\TdbDataExtranetUser
     */
    public function getExtranetUser()
    {
        return $this->extranetUser;
    }

    /**
     * @return null|string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }
}
