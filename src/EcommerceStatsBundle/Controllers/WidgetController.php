<?php
/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStatsBundle\Controllers;

use ChameleonSystem\CmsDashboardBundle\Bridge\Chameleon\Dashboard\Widgets\DashboardWidgetInterface;
use Psr\Container\ContainerInterface;

class WidgetController
{
    public function __construct(private readonly ContainerInterface $container)
    {

    }


    public function callWidgetMethod(string $widgetServiceId, string $methodName)
    {
        $widgetService = $this->getWidgetService($widgetServiceId);

        if (!method_exists($widgetService, $methodName)) {
            throw new \InvalidArgumentException('Method '.$methodName.' does not exist on widget service '.$widgetServiceId);
        }

        return $widgetService->$methodName();
    }

    private function getWidgetService(string $widgetServiceId): DashboardWidgetInterface
    {
        $widgetService = $this->container->get($widgetServiceId);

        if (false === $widgetService instanceof DashboardWidgetInterface) {
            throw new \InvalidArgumentException('Service '.$widgetServiceId.' does not implement WidgetServiceInterface');
        }

        return $widgetService;
    }

}