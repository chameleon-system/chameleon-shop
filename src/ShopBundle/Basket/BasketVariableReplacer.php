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

use ChameleonSystem\CoreBundle\Event\FilterContentEvent;
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
     * @var Environment
     */
    private $twigEnvironment;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var array
     */
    private $paramsRuntimeCache;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        Environment $twigEnvironment,
        RequestStack $requestStack,
        LoggerInterface $logger
    )
    {
        $this->twigEnvironment = $twigEnvironment;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    /**
     * filterResponse will be invoked on chameleon_system_core.filter_content and add the hidden fields to the basket form.
     * On error it will log to the request channel and move on. It will not halt execution.
     *
     * @param FilterContentEvent $event
     */
    public function filterResponse(FilterContentEvent $event): void
    {
        $content = $event->getContent();
        if (false === strpos($content, self::BASKET_HIDDEN_FIELDS_PLACEHOLDER)) {
            return;
        }

        $replacer = new \TPkgCmsStringUtilities_VariableInjection();
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            $this->logger->info(
                sprintf('Could not render hidden fields for basket forms. Request was null.')
            );

            return;
        }

        if (null === $this->paramsRuntimeCache) {

            $queryParameters = $request->query->all();
            $this->paramsRuntimeCache = $this->filterKeys($queryParameters, [\MTShopBasketCoreEndpoint::URL_REQUEST_PARAMETER]);
            $this->paramsRuntimeCache = $this->flattenQueryParameters($this->paramsRuntimeCache);
        }

        $hiddenFieldsHtml = [];
        foreach ($this->paramsRuntimeCache as $name => $value ) {
            try {
                $hiddenFieldsHtml[] = $this->twigEnvironment->render(
                    self::HIDDEN_FIELDS_SNIPPET,
                    ['name' => $name, 'value' => $value]
                );
            } catch(Error $error) {
                $this->logger->error(
                    sprintf('Error rendering hidden field for basket forms. %s => %s, Error: %s', $name, $value, $error->getMessage()),
                    ['exception' => $error]
                );
            }
        }

        $content = $replacer->replace($content, [self::BASKET_HIDDEN_FIELDS_PLACEHOLDER => implode('', $hiddenFieldsHtml)]);

        $event->setContent($content);
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
