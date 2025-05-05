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
     * @var RequestStack
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
     *
     * @throws ContentLoadingException
     */
    public function load($url)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new ContentLoadingException('Content loader needs current request; none found.');
        }

        if (false === $request->hasSession()) {
            throw new ContentLoadingException('Content loader needs current session; none found.');
        }

        $session = $request->getSession();

        if (0 !== strpos($url, 'http://') && 0 !== strpos($url, 'https://')) {
            $url = $request->getSchemeAndHttpHost().$url;
        }

        /*
         * We need to pass the session as cookie (the session may not be accepted via GET).
         * We don't pass all headers, since we do not provide the same functionality as the browser does.
         * The user agent is needed, as the session validation would otherwise fail if
         * CHAMELEON_SECURITY_EXTRANET_SESSION_USE_USER_AGENT_IN_KEY is set and that leads to a user logout.
         */
        $headers = [
            'Cookie' => urlencode($session->getName()).'='.urlencode($session->getId()),
            'User-Agent' => $request->headers->get('User-Agent'),
        ];
        $headerString = '';
        foreach ($headers as $name => $value) {
            $headerString .= sprintf("%s: %s\r\n", $name, $value);
        }
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => $headerString,
            ],
        ];

        $context = stream_context_create($opts);

        $content = @file_get_contents($url, false, $context);

        if (null === $content) {
            throw new ContentLoadingException('Content loading failed.');
        }

        return $content;
    }
}
