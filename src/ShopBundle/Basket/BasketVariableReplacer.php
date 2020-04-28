<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Basket;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Error\Error;

/**
 * BasketVariableReplacer is used to add all request parameters as hidden fields to "Add to Basket"-forms.
 * This happens as a post process job to avoid caching those parameters.
 *
 * To successfully use it on a page the template for the basket button must include a placeholder in the form of:
 *
 * [{BASKETHIDDENFIELDS}]
 *
 * Background: the additional fields are added so that those parameters from the url do not get lost on the redirect
 * after the POST call to the server.
 * As we do not know which parameters might be needed by other modules on the page we must add all of them.
 * This isn't a security concern as the page has already been called with those parameters in the first place.
 */
final class BasketVariableReplacer
{
    public const BASKET_HIDDEN_FIELDS_PLACEHOLDER = 'BASKETHIDDENFIELDS';
    private const HIDDEN_FIELDS_SNIPPET = '@ChameleonSystemShop/snippets/ShopBundle/BasketForm/hiddenBasketFields.html.twig';

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Environment
     */
    private $twigEnvironment;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RequestStack $requestStack,
        Environment $twigEnvironment,
        LoggerInterface $logger)
    {
        $this->requestStack = $requestStack;
        $this->twigEnvironment = $twigEnvironment;
        $this->logger = $logger;
    }

    /**
     * handleRequest will be invoked on kernel.request and add the hidden fields to the replacer.
     * On error it will log to the request channel and move on. It will not halt execution.
     */
    public function handleRequest(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            $this->logger->error('Tried to get the current request, but there was none. Additional hidden fields for the basket form will not be added.');
            return;
        }
        $queryParameters = $request->query->all();
        try {
            $hiddenFieldsHtml = $this->twigEnvironment->render(
                self::HIDDEN_FIELDS_SNIPPET,
                ['values' => $queryParameters]
            );
            \TTools::AddStaticPageVariables([self::BASKET_HIDDEN_FIELDS_PLACEHOLDER => $hiddenFieldsHtml]);
        } catch(Error $error) {
            $this->logger->error('Error rendering hidden fields for basket forms: ' . $error->getMessage());
        }
    }
}
