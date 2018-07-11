<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Controller;

use ChameleonSystem\ShopBundle\Interfaces\ShopSearchSuggestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchSuggestController
{
    /**
     * @var ShopSearchSuggestInterface
     */
    private $shopSearchSuggest;

    /**
     * @param ShopSearchSuggestInterface $shopSearchSuggest
     */
    public function __construct(ShopSearchSuggestInterface $shopSearchSuggest)
    {
        $this->shopSearchSuggest = $shopSearchSuggest;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $suggestions = $this->shopSearchSuggest->getSearchSuggestions($request->query->get('query'));
        $retValue = array(
            'options' => $suggestions,
        );

        $response = new Response(json_encode($retValue));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
