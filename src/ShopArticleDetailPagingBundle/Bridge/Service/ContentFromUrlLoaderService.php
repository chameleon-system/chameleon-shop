<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Bridge\Service;

use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ContentFromUrlLoaderServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ContentFromUrlLoaderService implements ContentFromUrlLoaderServiceInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    private $kernel;
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(HttpKernelInterface $kernel, RequestStack $requestStack)
    {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $url
     *
     * @return string|false
     */
    public function load($url)
    {
        $masterRequest = $this->requestStack->getMasterRequest();
        $request = Request::create($url, 'GET', array(), $masterRequest->cookies->all(), array(), $masterRequest->server->all());
        if (true === $masterRequest->hasSession()) {
            $request->setSession($masterRequest->getSession());
        }

        $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST, false);

        return $response->getContent();
    }
}
