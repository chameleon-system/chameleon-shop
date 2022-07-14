<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\Bridge;

use ChameleonSystem\SearchBundle\Interfaces\ShopSearchSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ShopSearchSessionChameleonBridge implements ShopSearchSessionInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param array<string, mixed> $searchRequest
     * @return void
     */
    public function addSearch(array $searchRequest)
    {
        $searchKey = md5($this->getArrayAsString($searchRequest));
        $searches = $this->session->get(ShopSearchSessionInterface::SESSION_KEY, array());
        $searches[] = $searchKey;
        $this->session->set(ShopSearchSessionInterface::SESSION_KEY, $searches);
    }

    /**
     * @param array<string, mixed> $searchRequest
     * @return bool
     */
    public function hasSearchedFor(array $searchRequest)
    {
        $searchKey = md5($this->getArrayAsString($searchRequest));

        $searches = $this->session->get(ShopSearchSessionInterface::SESSION_KEY, array());

        return in_array($searchKey, $searches);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private function getArrayAsString($data)
    {
        $parts = array();
        ksort($data);
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->getArrayAsString($value);
            }
            $parts[] = $key.'='.$value;
        }

        return implode(',', $parts);
    }
}
