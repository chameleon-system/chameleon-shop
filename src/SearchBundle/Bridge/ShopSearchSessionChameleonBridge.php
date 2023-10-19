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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ShopSearchSessionChameleonBridge implements ShopSearchSessionInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param array<string, mixed> $searchRequest
     * @return void
     */
    public function addSearch(array $searchRequest)
    {
        $session = $this->getSession();

        if (null === $session) {
            return;
        }
                
        $searchKey = md5($this->getArrayAsString($searchRequest));
        $searches = $session->get(ShopSearchSessionInterface::SESSION_KEY, []);
        $searches[] = $searchKey;
        $session->set(ShopSearchSessionInterface::SESSION_KEY, $searches);
    }

    /**
     * @param array<string, mixed> $searchRequest
     * @return bool
     */
    public function hasSearchedFor(array $searchRequest)
    {
        $session = $this->getSession();
        
        if (null === $session) {
            return false;
        }
        
        $searchKey = md5($this->getArrayAsString($searchRequest));

        $searches = $session->get(ShopSearchSessionInterface::SESSION_KEY, []);

        return in_array($searchKey, $searches);
    }

    /**
     * @param array $data
     */
    private function getArrayAsString($data): string
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

    private function getSession(): ?SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if (null === $request) {
            return null;
        }
                
        return $request->getSession();
    }
}
