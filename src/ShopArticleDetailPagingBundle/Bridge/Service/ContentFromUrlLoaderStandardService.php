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

use ChameleonSystem\ShopArticleDetailPagingBundle\Exception\ContentLoadingException;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ContentFromUrlLoaderServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentFromUrlLoaderStandardService implements ContentFromUrlLoaderServiceInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function load($url)
    {
        if ('http://' !== substr($url, 0, 7) && 'https://' !== substr($url, 0, 8)) {
            $url = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost().$url;
        }

        // need to pass the session as cookie (the session may not be accepted via get)
        // we don't pass all headers, since we do not provide the same functionality as the browser does
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Cookie: '.urlencode($session->getName()).'='.urlencode($session->getId())."\r\n",
            ),
        );

        $context = stream_context_create($opts);

        $content = @file_get_contents($url, false, $context);
        if (null === $content) {
            throw new ContentLoadingException('Content loading failed.');
        }

        return $content;
    }
}
