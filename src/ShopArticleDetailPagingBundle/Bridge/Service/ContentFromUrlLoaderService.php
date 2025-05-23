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
     * @var HttpKernelInterface
     */
    private $kernel;
    /**
     * @var RequestStack
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
        $masterRequest = $this->requestStack->getMainRequest();
        $request = Request::create($url, 'GET', [], $masterRequest->cookies->all(), [], $masterRequest->server->all());
        if (true === $masterRequest->hasSession()) {
            $request->setSession($masterRequest->getSession());
        }

        $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST, false);

        return $response->getContent();
    }
}
