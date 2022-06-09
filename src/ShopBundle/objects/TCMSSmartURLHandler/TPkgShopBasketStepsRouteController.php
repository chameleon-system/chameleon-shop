<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Controller\ChameleonControllerInterface;
use Symfony\Component\HttpFoundation\Request;

class TPkgShopBasketStepsRouteController extends \esono\pkgCmsRouting\AbstractRouteController
{
    /**
     * @var ChameleonControllerInterface
     */
    private $mainController;

    /**
     * @return void
     */
    public function setMainController(ChameleonControllerInterface $mainController)
    {
        $this->mainController = $mainController;
    }

    /**
     * @param Request $request
     * @param string $basketStepId
     * @param string $basketStepSystemName
     * @param string $defaultCheckoutPageId
     * @param string $stepCheckoutPageId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function basketStep(Request $request, $basketStepId, $basketStepSystemName, $defaultCheckoutPageId, $stepCheckoutPageId)
    {
        if ('/' === substr($basketStepSystemName, -1)) {
            $basketStepSystemName = substr($basketStepSystemName, 0, -1);
        }
        $request->attributes->set(MTShopOrderWizardCore::URL_PARAM_STEP_SYSTEM_NAME, $basketStepSystemName);
        $request->attributes->set('pagedef', $stepCheckoutPageId);

        return $this->mainController->__invoke();
    }
}
