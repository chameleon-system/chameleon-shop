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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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
     * @var Environment
     */
    private $twigEnvironment;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Environment $twigEnvironment,
        LoggerInterface $logger)
    {
        $this->twigEnvironment = $twigEnvironment;
        $this->logger = $logger;
    }

    /**
     * handleRequest will be invoked on kernel.request and add the hidden fields to the replacer.
     * On error it will log to the request channel and move on. It will not halt execution.
     *
     * @param GetResponseEvent $event
     */
    public function handleRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $queryParameters = $request->query->all();
        $paramsToRender = $this->filterKeys($queryParameters, ['basket']);
        $paramsToRender = $this->flattenQueryParameters($paramsToRender);

        try {
            $hiddenFieldsHtml = $this->twigEnvironment->render(
                self::HIDDEN_FIELDS_SNIPPET,
                ['values' => $paramsToRender]
            );
            \TTools::AddStaticPageVariables([self::BASKET_HIDDEN_FIELDS_PLACEHOLDER => $hiddenFieldsHtml]);
        } catch(Error $error) {
            $this->logger->error(
                sprintf('Error rendering hidden fields for basket forms: %s', $error->getMessage()),
                ['exception' => $error]
            );
        }
    }

    /**
     * flattenQueryParameters will flatten the query to a form that can be rendered to an html form.
     *
     * Example:
     * [
     *   "foo" => ["bar" => "baz"]
     * ]
     * will become:
     * [
     *   "foo[bar]" => "baz"
     * ]
     *
     * It will work recursively for deeper nested structures.
     *
     * @param array $queryParameters
     * @return array
     */
    private function flattenQueryParameters(array $queryParameters): array
    {
        $params = [];
        foreach ($queryParameters as $key => $value) {
            if (is_string($value)) {
                $params[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $subParameters = [];
                foreach ($value as $subKey => $subValue) {
                    $subParameters[sprintf('%s[%s]', $key, $subKey)] = $subValue;
                }
                $subRendered = $this->flattenQueryParameters($subParameters);
                foreach ($subRendered as $subKeyRendered => $subValueRendered) {
                    $params[$subKeyRendered] = $subValueRendered;
                }
            }
        }

        return $params;
    }

    /**
     * filterKeys will filter all the keys given.
     * Use this method before flattening the list with flattenQueryParameters.
     *
     * @param array $input
     * @param array $filter
     * @return array
     */
    private function filterKeys(array $input, array $filter): array
    {
        return array_filter(
            $input,
            static function (string $key) use ($filter): bool {
                return !in_array($key, $filter, true);
                },
            ARRAY_FILTER_USE_KEY
        );
    }
}
