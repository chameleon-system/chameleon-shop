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
    /**
     * @var string
     */
    private $context = null;

    /**
     * @var TdbDataExtranetUser
     */
    private $extranetUser = null;

    /**
     * @var string|null
     */
    private $ip = null;


    /**
     * @param string $sContext
     */
    public function __construct($sContext)
    {
        $this->context = $sContext;
        $this->extranetUser = TdbDataExtranetUser::GetInstance();
        $request = $this->getCurrentRequest();
        $this->ip = null === $request ? '' : $request->getClientIp();
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return \TdbDataExtranetUser|null
     */
    public function getExtranetUser()
    {
        return $this->extranetUser;
    }

    /**
     * @return string|null
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
