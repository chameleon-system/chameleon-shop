<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopMultiWarehouseBundle\ProductAvailability\ProductAvailabilityStringifyInterface;
use ChameleonSystem\ShopMultiWarehouseBundle\StockAccess\StockServiceInterface;
use ChameleonSystem\ShopMultiWarehouseBundle\StockAccessConfiguration\StockAccessConfigurationFactoryInterface;
use ChameleonSystem\ShopMultiWarehouseBundle\TargetAddress\TargetAddressProviderInterface;

class TShopStockMessage extends TAdbShopStockMessage
{
    /**
     * @var null|array{amount: int, range: float, oTrigger: TdbShopStockMessageTrigger}[]
     */
    protected $aMessagesForQuantity = null;

    /**
     * @var string|null
     */
    private $sArticleKey = null;

    /**
     * @param null $sData
     * @param null $sLanguage
     *
     * @return TdbShopStockMessage
     */
    public static function GetNewInstance($sData = null, $sLanguage = null)
    {
        $oInstance = parent::GetNewInstance($sData, $sLanguage);
        if ($oInstance && is_array($oInstance->sqlData)) {
            if (!empty($oInstance->sqlData['class_name'])) {
                $sClassName = $oInstance->sqlData['class_name'];
                $oNewInstance = new $sClassName();
                $oNewInstance->LoadFromRow($oInstance->sqlData);
                $oInstance = $oNewInstance;
            }
        }

        return $oInstance;
    }

    /**
     * renders.
     *
     * @return string
     */
    public function GetShopStockMessage()
    {
        $oShopStockMessageTrigger = null;
        $sMessage = $this->RenderStockMessage();
        if (is_object($this->GetArticle()) && property_exists($this->GetArticle(), 'dAmount') && is_null($this->aMessagesForQuantity)) {
            /**
             * @psalm-suppress UndefinedPropertyFetch - We are explicitly checking if the property exists above
             */
            $this->aMessagesForQuantity = $this->GetMessagesFromTriggerForQuantity($this->GetArticle()->dAmount);
        }
        if (is_array($this->aMessagesForQuantity)) {
            reset($this->aMessagesForQuantity);
            $aOther = array();
            foreach (array_keys($this->aMessagesForQuantity) as $iMessageIndex) {
                $aMessage = $this->aMessagesForQuantity[$iMessageIndex];
                $iAmount = $this->aMessagesForQuantity[$iMessageIndex]['amount'];
                if ($iAmount > 0) {
                    $aOther[] = $this->RenderShippingMessageFromTriggerForQuantity($aMessage);
                }
            }

            $sOther = implode('<br />', $aOther);
            if (!empty($sOther)) {
                $sMessage .= '<br />'.$sOther;
            }
        }
        $aMessageVariables = $this->GetMessageVariables($sMessage);

        $oStringReplace = new TPkgCmsStringUtilities_VariableInjection();
        $sMessage = $oStringReplace->replace($sMessage, $aMessageVariables);

        return $sMessage;
    }

    /**
     * return a css class for messages that depending on quantity
     * for example article has a stock of 10 but the user wants to buy 20 so the user gets the normal stock message AND the message that there is an other stock message / state for an amount of 10
     * these class is used for the message for the amount that could not be shipped yet.
     *
     * @param array $aMessageForQuantity
     *
     * @return string
     */
    protected function getCssClassForMessageForQuantity($aMessageForQuantity)
    {
        // return $aMessageForQuantity['css_class'];
        return '';
    }

    /**
     * renders the stock message.
     *
     * @return string
     */
    protected function RenderStockMessage()
    {
        return $this->fieldName;
    }

    /**
     * renders the trigger message.
     *
     * @param array $aMessage
     *
     * @return string
     */
    protected function RenderShippingMessageFromTriggerForQuantity($aMessage = array())
    {
        $sTriggerMessage = $aMessage['oTrigger']->fieldMessage;
        $sString = '<span class="'.$this->getCssClassForMessageForQuantity($aMessage).'">'.
            TGlobal::Translate(
                'chameleon_system_shop.stock_message.different_shipping_time_applies',
                array(
                    '%amount%' => $aMessage['amount'],
                    '%shippingMessage%' => $sTriggerMessage,
                )
            ).'</span>';

        return $sString;
    }

    /**
     * return array of variables that can be injected into the message text.
     *
     * @param string $sMessageString
     *
     * @return array
     */
    protected function GetMessageVariables($sMessageString)
    {
        if (is_object($this->GetArticle())) {
            return $this->GetArticle()->GetSQLWithTablePrefix();
        } else {
            return array();
        }
    }

    /**
     * @param TdbShopArticle $oArticle
     *
     * @return void
     */
    public function SetArticle($oArticle)
    {
        $this->sArticleKey = $oArticle->id;
        TCacheManagerRuntimeCache::SetContent($this->sArticleKey, $oArticle, 'TShopArticle', 3);
        $oShopStockMessageTrigger = &$this->GetFieldShopStockMessageTrigger();
        if (!is_null($oShopStockMessageTrigger)) {
            $this->fieldName = $oShopStockMessageTrigger->fieldMessage;
            $this->fieldClass = $oShopStockMessageTrigger->fieldCssClass;
        }
    }

    public function __destruct()
    {
        TCacheManagerRuntimeCache::UnsetKey($this->sArticleKey);
    }

    /**
     * @return TdbShopArticle|false
     */
    public function GetArticle()
    {
        if (null === $this->sArticleKey) {
            return false;
        }

        $oArticle = TCacheManagerRuntimeCache::GetContents($this->sArticleKey, 'TShopArticle');
        if (!$oArticle) {
            $oArticle = TdbShopArticle::GetNewInstance($this->sArticleKey);
        }

        return $oArticle;
    }

    /**
     * The method checks the ShopStockMessageTrigger for the current ShopStockMessage
     * if there is a Match it will return this matching one in the other case
     * it will return a null object.
     *
     * @return TdbShopStockMessageTrigger|null
     */
    public function &GetFieldShopStockMessageTrigger()
    {
        /** @var TdbShopStockMessageTrigger|null $oShopStockMessageTrigger */
        $oShopStockMessageTrigger = $this->GetFromInternalCache('oActive_shop_stock_message_trigger_id');

        if (is_null($oShopStockMessageTrigger)) {
            $sQuery = "SELECT *
                     FROM `shop_stock_message_trigger`
                    WHERE `shop_stock_message_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                      AND `amount` >= '".MySqlLegacySupport::getInstance()->real_escape_string($this->GetArticle()->getAvailableStock())."'
                 ORDER BY `amount` ASC
                    LIMIT 1
                  ";
            $oShopStockMessageTrigger = TdbShopStockMessageTrigger::GetNewInstance();
            /** @var $oShopStockMessageTrigger TdbShopStockMessageTrigger */
            $oTmp = MySqlLegacySupport::getInstance()->fetch_object(MySqlLegacySupport::getInstance()->query($sQuery));
            //if (!$oShopStockMessageTrigger->LoadFromRow(MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($sQuery)))) $oShopStockMessageTrigger = null;
            if (is_object($oTmp)) {
                if (!$oShopStockMessageTrigger->LoadFromField('id', $oTmp->id)) {
                    $oShopStockMessageTrigger = null;
                }
            } else {
                $oShopStockMessageTrigger = null;
            }
            $this->SetInternalCache('oActive_shop_stock_message_trigger_id', $oShopStockMessageTrigger);
        }

        return $oShopStockMessageTrigger;
    }

    /**
     * returns an array of trigger messages with the quantity for each message the is relevant if the user
     * tries to order dQuantityRequested.
     * array has the form: array(array('amount'=>x,'range'=>y,'oTrigger'=>z),..).
     *
     * @param int $dQuantityRequested
     *
     * @return array{amount: int, range: float, oTrigger: TdbShopStockMessageTrigger}[]
     */
    protected function GetMessagesFromTriggerForQuantity($dQuantityRequested)
    {
        // todo - get messages from service

        // need to find range for every stock type first
        $aStock = array();
        $iTotalStock = $this->GetArticle()->getAvailableStock();
        $oTriggerList = $this->GetFieldShopStockMessageTriggerListOrdered(array('amount' => 'ASC'));
        $oActiveTrigger = $this->GetFieldShopStockMessageTrigger();
        if ($oActiveTrigger) {
            $oTriggerList->AddFilterString("`shop_stock_message_trigger`.`id` != '".MySqlLegacySupport::getInstance()->real_escape_string($oActiveTrigger->id)."'");
        }
        if ($oTriggerList->Length() > 0) {
            $oPrevious = null;
            $iPos = 0;
            while ($oTrigger = $oTriggerList->Next()) {
                if ($oTrigger->fieldAmount <= $iTotalStock) {
                    $dRange = 0;
                    if (!is_null($oPrevious)) {
                        $dRange = $oTrigger->fieldAmount - $oPrevious->fieldAmount;
                    } else {
                        $dRange = -1;
                    }
                    $aStock[$iPos] = array('oTrigger' => $oTrigger, 'dRange' => $dRange, 'amount' => 0);
                    $oPrevious = $oTrigger;
                    ++$iPos;
                }
            }
            if ($iPos > 0) {
                $dNewTotalStock = null;
                for ($iStockIndex = $iPos - 1; $iStockIndex >= 0; --$iStockIndex) {
                    if ($dQuantityRequested > 0) {
                        // get new total stock affected by the call
                        if (is_null($dNewTotalStock)) {
                            $dNewTotalStock = $aStock[$iStockIndex]['oTrigger']->fieldAmount;
                            $dQuantityRequested = $dQuantityRequested - ($iTotalStock - $dNewTotalStock);
                        }
                        $dRange = $aStock[$iStockIndex]['dRange'];
                        if (-1 == $dRange) {
                            $dRange = $dQuantityRequested;
                        }
                        $dAmountUsed = min($dRange, $dQuantityRequested);
                        $aStock[$iStockIndex]['amount'] = $dAmountUsed;
                        $dQuantityRequested = $dQuantityRequested - $dAmountUsed;
                        $aStock[$iStockIndex]['amount'] = $dAmountUsed;
                    }
                }
                reset($aStock);
            }
        }

        return $aStock;
    }

    public function &GetFieldShopStockMessageTriggerList()
    {
        return $this->GetFieldShopStockMessageTriggerListOrdered();
    }

    /**
     * @param array<string, string>|null $aOrderBy
     * @psalm-param array<string, 'ASC'|'DESC'>|null $aOrderBy
     * @return TdbShopStockMessageTriggerList
     */
    public function &GetFieldShopStockMessageTriggerListOrdered(array $aOrderBy = null)
    {
        $oTriggerList = $this->GetFromInternalCache('oShopStockMessageTriggerList');
        if (is_null($oTriggerList)) {
            $oTriggerList = parent::GetFieldShopStockMessageTriggerList();
            if ($aOrderBy) {
                $oTriggerList->ChangeOrderBy($aOrderBy);
            }
            $oTriggerList->bAllowItemCache = true;
            $this->SetInternalCache('oShopStockMessageTriggerList', $oTriggerList);
        } else {
            $oTriggerList->GoToStart();
        }

        return $oTriggerList;
    }
}
