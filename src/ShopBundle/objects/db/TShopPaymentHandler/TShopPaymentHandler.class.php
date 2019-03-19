<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopBundle\Exception\ConfigurationException;

/**
 * the paymenthandlers are used to handle the different payment methods. They ensure that the right
 * information is collected from the user, and that the payment is executed (as may be the case for online payment)
 * Note that the default handler has no functionality. it must be extended in order to do anything useful.
/**/
class TShopPaymentHandler extends TShopPaymentHandlerAutoParent
{
    const VIEW_PATH = 'pkgShop/views/db/TShopPaymentHandler';
    const URL_PAYMENT_USER_INPUT = 'aPayment';
    const CONTINUE_PAYMENT_EXECUTION_FLAG = '__cmspaymentcontinue';

    /**
     * @var IPkgShopOrderPaymentConfig
     */
    private $configData;

    /**
     * user data for payment.
     *
     * @var array
     */
    protected $aPaymentUserData;

    /**
     * set by the payment method using this handler. access the property using the getter and setter for it.
     *
     * @var string
     */
    protected $sOwningPaymentMethodId;

    /**
     * return the URL to which the user input is send. using this method you can redirect the input
     * to go directly to, for example, the payment provider. if you return an empty string, the default target (ie the payment
     * data entry step) will be used
     * Important: for this method to have any affect, you will need to make sure that each payment method has its
     * own form in the view.
     *
     * @return string
     */
    public function GetUserInputTargetURL()
    {
        return '';
    }

    /**
     * set the owning payment method.
     *
     * @param string $sPaymentMethodId
     */
    public function SetOwningPaymentMethodId($sPaymentMethodId)
    {
        $this->sOwningPaymentMethodId = $sPaymentMethodId;
    }

    /**
     * getter for the owning payment method.
     *
     * @return string|null
     */
    protected function &GetOwningPaymentMethodId()
    {
        return $this->sOwningPaymentMethodId;
    }

    /**
     * return an instance of the correct class type for the filter identified by $id
     * Do not call this method directly. Instead use the service chameleon_system_shop.payment.handler_factory to
     * create payment handlers.
     *
     * @param int $id
     *
     * @return TdbShopPaymentHandler
     */
    public static function &GetInstance($id)
    {
        $oInstance = null;
        $query = "SELECT * FROM `shop_payment_handler` WHERE `id` = '".MySqlLegacySupport::getInstance()->real_escape_string($id)."'";
        if ($row = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
            $oInstance = static::getInstanceFromDataRow($row);
        }

        return $oInstance;
    }

    /**
     * @param array       $row
     * @param string|null $languageId
     *
     * @return TdbShopPaymentHandler
     */
    public static function getInstanceFromDataRow(array $row, $languageId = null)
    {
        $className = $row['class'];
        /**
         * @var $instance TdbShopPaymentHandler
         */
        $instance = new $className();
        if (null !== $languageId) {
            $instance->SetLanguage($languageId);
        }
        $instance->LoadFromRow($row);

        return $instance;
    }

    /**
     * allows you to overwrite the payment data - use it when, for example, you load
     * the payment handler for an order.
     *
     * @param array $aPaymentUserData - assoc array of the parameter
     */
    public function SetPaymentUserData($aPaymentUserData)
    {
        $this->aPaymentUserData = $aPaymentUserData;
    }

    /**
     * html head includes to use in the shipping step if the payment method is available.
     *
     * @return array
     */
    public function GetHtmlHeadIncludes()
    {
        return array();
    }

    /**
     * @return bool
     */
    public function ShowNextStepbutton()
    {
        return true;
    }

    /**
     * @return IPkgShopOrderPaymentConfig
     */
    protected function getConfigData()
    {
        return $this->configData;
    }

    /**
     * @param IPkgShopOrderPaymentConfig $configData
     */
    public function setConfigData(IPkgShopOrderPaymentConfig $configData)
    {
        $this->configData = $configData;
    }

    /**
     * return a config parameter for the payment handler.
     *
     * @param string $sParameterName - the system name of the handler
     *
     * @return string
     *
     * @throws ConfigurationException
     */
    public function GetConfigParameter($sParameterName)
    {
        if (null === $this->configData) {
            throw new ConfigurationException('Payment handler is not configured. Please do only initialize a payment handler through the ShopPaymentHandlerFactory');
        }

        return $this->configData->getValue($sParameterName, false);
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->configData->getEnvironment();
    }

    /**
     * @return TdbShopPaymentHandlerParameterList
     */
    public function &GetFieldShopPaymentHandlerParameterList()
    {
        $oList = TdbShopPaymentHandlerParameterList::GetListForShopPaymentHandlerId($this->id, $this->iLanguageId);
        $oList->ChangeOrderBy(array('cms_portal_id' => 'ASC')); //make sure parameters without portal come first
        return $oList;
    }

    /**
     * Executes payment for an order. This method is called when the user confirmed the order.
     * If you need to transfer control to an external process (as may be required for 3d secure), use
     * self::SetExecutePaymentInterrupt(true) before passing control. This tells the shop to continue order execution
     * as soon as control is passed back to it. (Chameleon will call $this->PostExecutePaymentHook() as soon as control
     * is returned  - this is where you should check if the returned data is valid/invalid.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool true if the payment was executed successfully, else false
     */
    public function ExecutePayment(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        return true;
    }

    /**
     * Set the ExecutePaymentInterrupt. If it is set to active, then the next request to the
     * basket page will auto.
     *
     * @param bool $bActive
     */
    public static function SetExecutePaymentInterrupt($bActive)
    {
        if ($bActive) {
            $_SESSION[TdbShopPaymentHandler::CONTINUE_PAYMENT_EXECUTION_FLAG] = '1';
        } else {
            if (array_key_exists(TdbShopPaymentHandler::CONTINUE_PAYMENT_EXECUTION_FLAG, $_SESSION)) {
                $_SESSION[TdbShopPaymentHandler::CONTINUE_PAYMENT_EXECUTION_FLAG] = '0';
                unset($_SESSION[TdbShopPaymentHandler::CONTINUE_PAYMENT_EXECUTION_FLAG]);
            }
        }
    }

    /**
     * return true if the ExecutePayment was interrupted and needs to be continued...
     *
     * @return bool
     */
    public static function ExecutePaymentInterrupted()
    {
        return array_key_exists(TdbShopPaymentHandler::CONTINUE_PAYMENT_EXECUTION_FLAG, $_SESSION) && ('1' == $_SESSION[TdbShopPaymentHandler::CONTINUE_PAYMENT_EXECUTION_FLAG]);
    }

    /**
     * The method is called from TShopBasket AFTER ExecutePayment was successfully executed.
     * The method is ALSO called, if the payment handler passed execution to an external service from within the ExecutePayment
     * Method. Note: if you return false, then the system will return to the called order step and the
     * order will be cancled (shop_order.canceled = 1) - the article stock will be returned.
     *
     * if you return true, then the order is marked as paid.
     *
     * Note: system_order_payment_method_executed will be set to true either way
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function PostExecutePaymentHook(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = true;
        $aData = $oOrder->sqlData;
        $aData['system_order_payment_method_executed'] = '1';
        $aData['system_order_payment_method_executed_date'] = date('Y-m-d H:i:s');
        $oOrder->LoadFromRow($aData);
        $oOrder->AllowEditByAll(true);
        $oOrder->Save();

        return $bPaymentOk;
    }

    /**
     * method is called only if the user returns from an execute payment call that was interrupted. this is called before
     * PostExecutePaymentHook. If the method returns false, or if an exception is thrown, then the order is canceled.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer
     *
     * @throws TPkgCmsException_LogAndMessage
     *
     * @return bool
     */
    public function postExecutePaymentInterruptedHook(TdbShopOrder $oOrder, $sMessageConsumer)
    {
        return true;
    }

    /**
     * return true if the user data is valid
     * data is loaded from GetUserPaymentData().
     *
     * @return bool
     */
    public function ValidateUserInput()
    {
        $this->GetUserPaymentData(); // load user data...
        // then validate...

        return true;
    }

    /**
     * store user payment data in order.
     *
     * @param int $iOrderId
     */
    public function SaveUserPaymentDataToOrder($iOrderId)
    {
        $aUserPaymentData = $this->GetUserPaymentData();
        $aUserPaymentData = $this->PreSaveUserPaymentDataToOrderHook($aUserPaymentData);
        if (is_array($aUserPaymentData)) {
            $query = "DELETE FROM `shop_order_payment_method_parameter` WHERE `shop_order_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($iOrderId)."'";
            MySqlLegacySupport::getInstance()->query($query);
            foreach ($aUserPaymentData as $keyId => $keyVal) {
                $oPaymentParameter = TdbShopOrderPaymentMethodParameter::GetNewInstance();
                /** @var $oPaymentParameter TdbShopOrderPaymentMethodParameter */
                $aTmpData = array('shop_order_id' => $iOrderId, 'name' => $keyId, 'value' => $keyVal);
                $oPaymentParameter->AllowEditByAll(true);
                $oPaymentParameter->LoadFromRow($aTmpData);
                $oPaymentParameter->Save();
            }
        }
    }

    /**
     * hook is called before the payment data is committed to the database. use it to cleanup/filter/add data you may
     * want to include/exclude from the database.
     *
     * @param array $aPaymentData
     *
     * @return array
     */
    protected function PreSaveUserPaymentDataToOrderHook($aPaymentData)
    {
        return $aPaymentData;
    }

    /**
     * load user payment data.
     *
     * @return array
     */
    protected function GetUserPaymentData()
    {
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists(TdbShopPaymentHandler::URL_PAYMENT_USER_INPUT)) {
            $this->aPaymentUserData = $oGlobal->GetUserData(TdbShopPaymentHandler::URL_PAYMENT_USER_INPUT);
            if (!is_array($this->aPaymentUserData)) {
                $this->aPaymentUserData = array();
            }
        } elseif (is_null($this->aPaymentUserData)) {
            $this->aPaymentUserData = $this->GetDefaultUserPaymentData();
        }

        return $this->aPaymentUserData;
    }

    /**
     * return a variable from the user payment data. false if the variable is not found.
     *
     * @param $sItemName
     *
     * @return bool|string
     */
    public function GetUserPaymentDataItem($sItemName)
    {
        $aPaymentData = $this->GetUserPaymentData();
        $sReturn = false;
        if (is_array($aPaymentData) && isset($aPaymentData[$sItemName])) {
            $sReturn = $aPaymentData[$sItemName];
        }

        return $sReturn;
    }

    /**
     * public getter method that simply returns the aPaymentUserData array
     * without any loading logic as in GetUserPaymentData().
     *
     * @return array
     */
    public function GetUserPaymentDataWithoutLoading()
    {
        return $this->aPaymentUserData;
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array
     */
    protected function GetDefaultUserPaymentData()
    {
        return array();
    }

    /**
     * used to display the basket article list.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array())
    {
        $oView = new TViewParser();

        $oShop = TdbShop::GetInstance();
        $oView->AddVar('oShop', $oShop);
        $oView->AddVar('oPaymentHandler', $this);

        $aUserPaymentData = $this->GetUserPaymentData();
        $oView->AddVar('aUserPaymentData', $aUserPaymentData);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, $this->GetViewPath(), $sViewType);
    }

    /**
     * @return string
     */
    protected function GetViewPath()
    {
        return self::VIEW_PATH;
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        return array();
    }

    /**
     * Removes any non numerical characters from a string to sanitize
     * values like 680 501 01 as bank no. that will be converted to 68050101.
     *
     * @param string $sFieldContent
     *
     * @return string
     */
    protected function SanitizePaymentMethodParameterField($sFieldContent)
    {
        return preg_replace('/[^0-9]/i', '', $sFieldContent);
    }

    /**
     * method is called after the user selected his payment and submitted the payment page
     * return false if you want to send the user back to the payment selection page.
     *
     * @param string $sMessageConsumer - the name of the message handler that can display messages if an error occurs (assuming you return false)
     *
     * @return bool
     */
    public function PostSelectPaymentHook($sMessageConsumer)
    {
        return true;
    }

    /**
     * the method is called when an external payment handler returns successfully.
     */
    public function PostProcessExternalPaymentHandlerHook()
    {
        return true;
    }

    /**
     * return true if the the payment handler may be used by the payment method passed.
     * you can use this hook to disable payment methods based on basket contents, payment method, user data, ...
     *
     * @param TdbShopPaymentMethod $oPaymentMethod
     *
     * @return bool
     */
    public function AllowUse(TdbShopPaymentMethod &$oPaymentMethod)
    {
        return true;
    }

    /**
     * some payment methods (such as paypal) get a reference number from the external
     * service, that allows the shop owner to identify the payment executed in their
     * Webservice. Since it is sometimes necessary to provided this identifier.
     *
     * every payment method that provides such an identifier needs to overwrite this method
     *
     * returns an empty string, if the method has no identifier.
     *
     * @return string
     */
    public function GetExternalPaymentReferenceIdentifier()
    {
        return '';
    }

    /**
     * some payment methods allow the user to select a payment type (for example: a payment methods that accepts credit cards may accept visa and master card)
     * this method should return that identifier.
     */
    public function GetExternalPaymentTypeIdentifier()
    {
        return '';
    }

    /**
     * this hook is called by MTShopOrderWizardCore AFTER the user returned to
     * the wizard from an interrupted payment process (as my be the case when the
     * user was redirected to the external payment page) but BEFORE we redirect to the
     * thank you page. this gives us the chance to redirect somewhere else instead (such as
     * a page that breaks out of an iframe via js).
     */
    public function BeforeJumpToThankYouStepAfterInterruptedPaymentHook()
    {
    }

    /**
     * the hook is called only when returning from an external payment process to the basket
     * AND if the return failed (ie. user aborted, entered wrong data, etc). Normally there would be no
     * redirect - instead we would show the page that was requested via url (ie the confirm page)
     * this hook could be used, to auto redirect back to the payment page, or to break out of an iframe.
     */
    public function OnPaymentErrorAfterInterruptedPaymentHook()
    {
    }

    /**
     * return the currency identifier for the currency we pay in.
     *
     * @param $oPkgShopCurrency TdbPkgShopCurrency
     *
     * @return string
     */
    protected function GetCurrencyIdentifier($oPkgShopCurrency = null)
    {
        $sCurrencyCode = 'EUR'; //default
        if (!is_null($oPkgShopCurrency)) {
            $sCurrencyCode = $oPkgShopCurrency->fieldName;
        }

        return $sCurrencyCode;
    }

    /**
     * checks if payment is blocked for user selection.
     *
     * @return bool
     */
    public function isBlockForUserSelection()
    {
        return $this->fieldBlockUserSelection;
    }
}
