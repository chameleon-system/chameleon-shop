<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopBundle\Service\OrderStepPageServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * definiert einen Bestellschritt.
/**/
class TShopOrderStep extends TShopOrderStepAutoParent
{
    const SESSION_KEY_NAME = 'TShopOrderStepdata';
    const SESSION_KEY_NAME_ORDER_SUCCESS = 'TShopOrderStepdataOrderSuccess';
    const VIEW_PATH = 'pkgShop/views/db/TShopOrderStep';

    /**
     * is used to mark the current active step within step lists.
     *
     * @var bool
     */
    public $bIsTheActiveStep = false;

    /**
     * Called from the init method of the calling module.
     * Visitor permissions for the requested step may be checked and the user redirected.
     *
     * @return void
     */
    public function Init()
    {
        $basket = $this->getShopService()->getActiveBasket();
        $basket->aCompletedOrderStepList[$this->fieldSystemname] = false;
        $this->CheckBasketContents();

        if (false === $this->AllowAccessToStep(true)) {
            $this->JumpToStep($this->GetPreviousStep());
        }
        if ('basket' !== $this->fieldSystemname && 'thankyou' !== $this->fieldSystemname) {
            $basket->CommitCopyToDatabase(false, $this->fieldSystemname);
        } // commit basket to database...
    }

    /**
     * Returns if the step is active and included in the order process.
     *
     * @return bool
     */
    public function IsActive()
    {
        return true;
    }

    /**
     * Returns if the user is allowed to view the requested step.
     *
     * @param bool $bRedirectToPreviousPermittedStep
     *
     * @return bool
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        return $this->IsActive();
    }

    /**
     * Method added to provide public access to the AllowAccessToStep method.
     *
     * @return bool
     */
    public function AllowAccessToStepPublic()
    {
        return $this->AllowAccessToStep();
    }

    /**
     * Redirects back to the basket if basket is currently empty.
     *
     * @return void
     */
    protected function CheckBasketContents()
    {
        if ('basket' !== $this->fieldSystemname) {
            $oBasket = $this->getShopService()->getActiveBasket();
            if ($oBasket->dTotalNumberOfArticles <= 0) {
                $oBasketStep = TdbShopOrderStep::GetStep('basket');
                $this->JumpToStep($oBasketStep);
            }
        }
    }

    /**
     * Returns the step with the systemname $sStepName (language id is taken form the active page object).
     *
     * @param string $sStepName
     *
     * @return TdbShopOrderStep|null
     */
    public static function GetStep($sStepName)
    {
        $oStep = null;
        $oStepData = null;
        if (is_null($sStepName) || false === $sStepName || empty($sStepName)) {
            // fetch the first step instead..
            $oSteps = TdbShopOrderStepList::GetList();
            if ($oSteps->Length() > 0) {
                /** @var TdbShopOrderStep $oStep */
                $oStep = $oSteps->Current();
            }
        } else {
            $oStepData = TdbShopOrderStep::GetNewInstance();
            /** @var $oStepData TdbShopOrderStep */
            if (!$oStepData->LoadFromField('systemname', $sStepName)) {
                $oStepData = null;
            }
        }
        if (!is_null($oStepData)) {
            $oStep = TdbShopOrderStep::GetNewInstance($oStepData->sqlData);
        }

        return $oStep;
    }

    /**
     * @param string|array|null $sData - either the id of the object to load, or the row with which the instance should be initialized
     * @param string|null $sLanguage - init with the language passed
     * @return TdbShopOrderStep|null
     */
    public static function GetNewInstance($sData = null, $sLanguage = null)
    {
        if (null === $sData) {
            return new TdbShopOrderStep(null, $sLanguage);
        }
        if (null !== $sData && false == is_array($sData)) {
            return TdbShopOrderStep::GetNewInstance($sData);
        }

        $class = 'TdbShopOrderStep';
        if (isset($sData['class']) && '' !== $sData['class']) {
            $class = $sData['class'];
        }
        $shopBasket = TShopBasket::GetInstance();
        $object = null;
        if (null !== $shopBasket) {
            $object = self::createClass($shopBasket, $class);
            $object->LoadFromRow($sData);
        }

        return $object;
    }

    /**
     * @param TShopBasket $basket
     * @param string      $classList comma seperated list of class names
     *
     * @return TdbShopOrderStep|null
     */
    private static function createClass(TShopBasket $basket, $classList)
    {
        $classList = str_replace(' ', '', $classList);
        $classes = explode(',', $classList);
        foreach ($classes as $class) {
            /** @var TdbShopOrderStep $classObject */
            $classObject = new $class();
            if (true === $classObject->isApplicableForBasket($basket)) {
                return $classObject;
            }
        }

        return null;
    }

    /**
     * Defines that this class may be used to handle basket calls if multiple classes can apply for a single order step.
     *
     * @param TShopBasket $basket
     *
     * @return bool
     */
    protected function isApplicableForBasket(TShopBasket $basket)
    {
        return true;
    }

    /**
     * Returns the calling step name (set by JumpToStep).
     *
     * @return string|null
     */
    public static function GetCallingStepName()
    {
        $sCallingStepName = null;
        if (array_key_exists(self::SESSION_KEY_NAME, $_SESSION)) {
            $sCallingStepName = $_SESSION[self::SESSION_KEY_NAME];
        }

        return $sCallingStepName;
    }

    /**
     * @param TdbShopOrderStep $oStep
     *                                Redirects to the specified step. The step calling this method will store its name in the session,
     *                                so that the new step knows where to return to.
     *
     * @return never
     */
    public function JumpToStep(TdbShopOrderStep $oStep)
    {
        $_SESSION[self::SESSION_KEY_NAME] = $this->fieldSystemname;
        $statusCode = 'POST' === $this->getRequest()->getMethod() ? Response::HTTP_SEE_OTHER : Response::HTTP_FOUND;
        $this->getRedirect()->redirect($this->getOrderStepPageService()->getLinkToOrderStepPageRelative($oStep), $statusCode);
    }

    /**
     * Reloads the current step.
     *
     * @return never
     */
    protected function ReloadCurrentStep()
    {
        $this->getRedirect()->redirect($this->getOrderStepPageService()->getLinkToOrderStepPageRelative($this));
    }

    /**
     * Returns the URL to this step.
     *
     * @param bool  $bDisableAccessCheck  If false, there will be an access check for the step. If this access check fails, the returned URL will be empty.
     * @param bool  $bForcePortalLink     - set to true if you want to include the domain
     * @param array $aAdditionalParameter
     *
     * @return string
     */
    public function GetStepURL($bDisableAccessCheck = true, $bForcePortalLink = false, $aAdditionalParameter = array())
    {
        $sOrderPage = '';
        if ($bDisableAccessCheck || $this->AllowAccessToStep()) {
            if ($bForcePortalLink) {
                $sOrderPage = $this->getOrderStepPageService()->getLinkToOrderStepPageAbsolute($this, $aAdditionalParameter);
            } else {
                $sOrderPage = $this->getOrderStepPageService()->getLinkToOrderStepPageRelative($this, $aAdditionalParameter);
            }
        }

        return $sOrderPage;
    }

    /**
     * Returns the URL required to load a step via ajax.
     *
     * @param bool $bDisableAccessCheck
     * @param bool $bForcePortalLink
     * @param array<string, mixed> $aAdditionalParameter
     *
     * @return string
     */
    public function GetStepURLReturnStepViaAjax($bDisableAccessCheck = true, $bForcePortalLink = false, $aAdditionalParameter = array())
    {
        $oGlobal = TGlobal::instance();
        $aAdditionalParameter['module_fnc'] = array($oGlobal->GetExecutingModulePointer()->sModuleSpotName => 'ExecuteAjaxCall');
        $aAdditionalParameter['_fnc'] = 'GetStepAsAjax';
        $aAdditionalParameter['sStepName'] = $this->fieldSystemname;

        return $this->GetStepURL($bDisableAccessCheck, $bForcePortalLink, $aAdditionalParameter);
    }

    /**
     * Returns the link to the previous step (or false if there is none).
     *
     * @return string|false
     */
    protected function GetReturnToLastStepURL()
    {
        $sLink = false;
        $oBackItem = $this->GetPreviousStep();
        if (!is_null($oBackItem)) {
            $sLink = $oBackItem->GetStepURL();
        }

        return $sLink;
    }

    /**
     * Executes the current step. Redirects to the next step in line if no errors occur.
     *
     * @return void
     */
    public function ExecuteStep()
    {
        if ($this->ProcessStep()) {
            $this->ProcessStepSuccessHook();
            $oBasket = TShopBasket::GetInstance();
            $oBasket->aCompletedOrderStepList[$this->fieldSystemname] = true;
            $oNextStep = $this->GetNextStep();
            $this->JumpToStep($oNextStep);
        }
    }

    /**
     * Called when method ProcessStep() was successful.
     *
     * @return void
     */
    protected function ProcessStepSuccessHook()
    {
    }

    /**
     * @return bool
     *
     * @throws LogicException if POST requests are compulsory for this step and the request method was not POST
     */
    protected function ProcessStep()
    {
        if ($this->isPostRequestCompulsoryForProcessStep() && Request::METHOD_POST !== $this->getRequest()->getMethod()) {
            throw new LogicException('Wizard step forms may only be submitted using the POST method. Please adjust your form accordingly.');
        }
        $bContinue = true;
        $request = $this->getRequest();
        if (null === $request || false === $request->hasSession()) {
            return false;
        }
        /** @var TPKgCmsSession $session */
        $session = $request->getSession();
        if (false === $session->restartSessionWithWriteLock()) {
            TTools::WriteLogEntry('unable to get session write lock', 1, __FILE__, __LINE__);
            $bContinue = false;
        }

        return $bContinue;
    }

    /**
     * Returns true if the step may only be processed in a POST request. By default this is always the case, but
     * subclasses may implement custom logic.
     *
     * @return bool
     */
    protected function isPostRequestCompulsoryForProcessStep()
    {
        return true;
    }

    /**
     * Returns name of next step in line.
     *
     * @return TdbShopOrderStep|null
     */
    public function GetNextStep()
    {
        static $oNextStep;
        if (!$oNextStep) {
            $oNextStep = TdbShopOrderStepList::GetNextStep($this);
        }

        return $oNextStep;
    }

    /**
     * Returns the previous step (null if this is the first step).
     *
     * @return TdbShopOrderStep|null
     */
    protected function GetPreviousStep()
    {
        static $oPreviousStep;
        if (!$oPreviousStep) {
            $oPreviousStep = TdbShopOrderStepList::GetPreviousStep($this);
        }

        return $oPreviousStep;
    }

    /**
     * Defines any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        return array('ExecuteStep');
    }

    /**
     * Defines any head includes the step needs.
     *
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        return array();
    }

    /**
     * @return string[]
     */
    public function GetHtmlFooterIncludes()
    {
        return array();
    }

    /**
     * Returns the view to use for the render method. Can be overwritten to return
     * a view different from the one set in the database.
     *
     * @return string
     */
    protected function GetRenderViewName()
    {
        return $this->fieldRenderViewName;
    }

    /**
     * Returns the view to use for the render method. Can be overwritten to return
     * a view different from the one set in the database (must return 'Core', 'Custom-Core' or 'Customer').
     *
     * @return string
     */
    protected function GetRenderViewType()
    {
        return $this->fieldRenderViewType;
    }

    /**
     * Returns variables that should be replaced in the description field.
     *
     * @return array
     */
    protected function GetDescriptionVariables()
    {
        return array();
    }

    /**
     * Returns the description of the step. Place any custom variables for the step into GetDescriptionVariables.
     *
     * @return string
     */
    public function GetDescription()
    {
        return $this->GetTextField('description', 600, true, $this->GetDescriptionVariables());
    }

    /**
     * Renders the requested step.
     *
     * @param array $aCallTimeVars - place any custom vars that you want to pass through the call here
     * @param null|string $sSpotName
     *
     * @return string
     */
    public function Render($sSpotName = null, $aCallTimeVars = array())
    {
        $oView = new TViewParser();

        $oView->AddVar('oShop', $this->getShopService()->getActiveShop());
        $oView->AddVar('oStep', $this);

        $oStepNext = $this->GetNextStep();
        $oStepPrevious = $this->GetPreviousStep();
        $oView->AddVar('oStepNext', $oStepNext);
        $oView->AddVar('oStepPrevious', $oStepPrevious);

        $sBackLink = $this->GetReturnToLastStepURL();
        $oView->AddVar('sBackLink', $sBackLink);

        $oView->AddVar('sSpotName', $sSpotName);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);

        $sViewName = $this->GetRenderViewName();
        $sViewType = $this->GetRenderViewType();
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, $this->getStepViewPath(), $sViewType);
    }

    /**
     * @return string
     */
    private function getStepViewPath()
    {
        $path = self::VIEW_PATH.'/';
        $class = get_class($this);
        // normalize namespace
        if ('/' === substr($class, 0, 1)) {
            $class = substr($class, 1);
        }
        $class = str_replace('\\', '', $class);

        return $path.$class;
    }

    /**
     * Adds any variables to the render method that may be required for the view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = $this->GetDescriptionVariables();

        return $aViewVariables;
    }

    /**
     * Called once the order is completed and saved before redirecting to the confirm step.
     *
     * @return void
     */
    public static function MarkOrderProcessAsCompleted()
    {
        $_SESSION[self::SESSION_KEY_NAME_ORDER_SUCCESS] = true;
    }

    /**
     * Resets the order process complete marker - allowing a new order process to start
     * (called by the thank you page render method).
     *
     * @return void
     */
    public static function ResetMarkOrderProcessAsCompleted()
    {
        if (array_key_exists(self::SESSION_KEY_NAME_ORDER_SUCCESS, $_SESSION)) {
            unset($_SESSION[self::SESSION_KEY_NAME_ORDER_SUCCESS]);
        }
    }

    /**
     * Returns if the confirm step has been completed successfully.
     *
     * @return bool
     */
    public static function OrderProcessHasBeenMarkedAsCompleted()
    {
        return array_key_exists(self::SESSION_KEY_NAME_ORDER_SUCCESS, $_SESSION) && true == $_SESSION[self::SESSION_KEY_NAME_ORDER_SUCCESS];
    }

    /**
     * Adds custom data to the basket after getting instance from session.
     *
     * @param TShopBasket $oBasket
     *
     * @return void
     */
    protected function addDataToBasket(TShopBasket $oBasket)
    {
    }

    /**
     * @return ShopServiceInterface
     */
    protected function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }

    /**
     * @return OrderStepPageServiceInterface
     */
    private function getOrderStepPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.order_step_page_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }

    /**
     * @return InputFilterUtilInterface
     */
    protected function getInputFilterUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.util.input_filter');
    }

    /**
     * @return Request|null
     */
    private function getRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }
}
