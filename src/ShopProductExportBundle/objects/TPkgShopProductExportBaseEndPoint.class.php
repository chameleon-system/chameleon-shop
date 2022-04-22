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
use ChameleonSystem\ShopProductExportBundle\Interfaces\ShopProductExportHandlerInterface;
use Doctrine\DBAL\Connection;
use esono\pkgCmsCache\CacheInterface;

class TPkgShopProductExportBaseEndPoint implements ShopProductExportHandlerInterface
{
    /**
     * article list loaded by module or something else that calls this class.
     *
     * @var TIterator|null
     */
    protected $oArticleList = null;

    /**
     * absolute path to the cache file.
     *
     * @var string|null
     */
    protected $sCacheFile = null;

    /**
     * false if the export is in cache so load the cache file and pass it to the output
     * true if there is no cache enabled or cache item is out of date.
     *
     * @var bool
     */
    protected $bGenerateFile = true;

    /**
     * only set if cached file is set - output will be written into file instead of output buffer.
     *
     * @var bool|resource
     */
    protected $fpFile = false;

    /**
     * set to true on debug state.
     *
     * @var bool
     */
    protected $bDebug = false;

    /**
     * key will be accessed by exports - value should be system_name of the attribute that will be fetched.
     *
     * @var array
     */
    protected $aAttributes = array();

    /**
     * hook is called before an article is exported.
     *
     * @param TdbShopArticle $oArticle
     *
     * @return TdbShopArticle
     */
    protected function PreProcessArticle($oArticle)
    {
        return $oArticle;
    }

    /**
     * do any initialization work that needs to be done before you want to run the export.
     *
     * @return void
     */
    public function Init()
    {
    }

    /**
     * Run the export. returns true if the export was successful, otherwise false
     * this method should not be overwritten in child classes.
     *
     * @return bool
     */
    public function Run()
    {
        $bSuccess = false;

        set_time_limit(1800);
        $this->getCache()->disable();
        TCacheManagerRuntimeCache::SetEnableAutoCaching(false);
        if ($this->Prepare()) {
            $bSuccess = $this->Perform();
        }
        $this->Cleanup($bSuccess);
        $this->getCache()->enable();
        TCacheManagerRuntimeCache::SetEnableAutoCaching(true);

        return $bSuccess;
    }

    /**
     * prepare the export - setup temp tables etc
     * return false if the preparation failed.
     *
     * @return bool
     */
    protected function Prepare()
    {
        $bPreparationOk = true;

        header('Content-type: '.$this->getContentType().'; charset=utf-8');

        $this->SetUpAttributes();

        return $bPreparationOk;
    }

    /**
     * method is called once on prepare of export
     * set attributes system names specific keys that will be used by exports
     * by default we set the ean and mpnr
     * you can overwrite these keys (and add custom keys) with the help of the virtual class manager.
     *
     * e.g.$this->aAttributes['brand'] = 'hersteller';
     *
     * @return void
     */
    protected function SetUpAttributes()
    {
        $this->aAttributes['ean'] = 'ean';
        $this->aAttributes['mpnr'] = 'mpnr';
    }

    /**
     * perform the actual export work. return true if the export was successful, otherwise false.
     *
     * @return bool
     */
    protected function Perform()
    {
        $bSuccess = true;

        $this->PreArticleListHandling();
        $this->HandleArticleList();
        $this->PostArticleListHandling();

        return $bSuccess;
    }

    /**
     * stuff you want to do before looping through $this->oArticleList.
     *
     * @return void
     */
    protected function PreArticleListHandling()
    {
    }

    /**
     * generally you want to loop through the article list by while ($oArticle =& $oArticleList->Next())
     * and do work with your articles.
     *
     * @see the csv export base class how you could do this
     *
     * @return void
     */
    protected function HandleArticleList()
    {
    }

    /**
     * return false to exit the article while loop - method is called on every article
     * for example you can use this method to limit the time the export needs to generate by jumping out of the loop
     * on a specific count (iCount variable is passed every turn)
     * if return value is true the loop will continue.
     *
     * @param int $iCount
     *
     * @return bool
     */
    protected function BreakUp($iCount)
    {
        if ($this->GetDebug()) {
            if ($iCount < 100) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * used to calculate delivery costs for an article (if needed)
     * the delivery costs may differ depending on the field name or you may want to be able to pass and check some other things
     * so an additional data array can be passed
     * returns false if requested payment not exists or not active.
     *
     * @param TdbShopArticle $oArticle
     * @param array          $aAdditionalData
     *
     * @return string|false
     */
    protected function GetDeliveryCosts(&$oArticle, $aAdditionalData = array())
    {
        $sDeliveryCosts = 0.00;
        $oShop = TdbShop::GetInstance();
        $sLanguage = 'DE';
        if (array_key_exists('langauge_iso_name', $aAdditionalData)) {
            $sLanguage = $aAdditionalData['langauge_iso_name'];
        }
        $oDefaultShippingGroup = $oShop->GetFieldShopShippingGroup();
        $oDeCountry = TdbDataCountry::GetInstanceForIsoCode($sLanguage);
        if (!is_null($oDefaultShippingGroup) && !is_null($oDeCountry)) {
            $oBasketArticleList = new TShopBasketArticleList();
            $oBasketArticle = new TShopBasketArticle();
            if ($oBasketArticle->Load($oArticle->id)) {
                $oBasketArticle->ChangeAmount(1);
                $oBasketArticleList->AddItem($oBasketArticle);
                $sDeliveryCosts = $oDefaultShippingGroup->GetShippingCostsForBasketArticleListAndCountry($oBasketArticleList, $oDeCountry->id);
            }
        }
        if (array_key_exists('payment_identifier', $aAdditionalData)) {
            $oPaymentMethod = TdbShopPaymentMethod::GetNewInstance();
            if ($oPaymentMethod->LoadFromField('name_internal', $aAdditionalData['payment_identifier']) && true === $oPaymentMethod->fieldActive) {
                $sDeliveryCosts = $sDeliveryCosts + $oPaymentMethod->GetPrice();
            } else {
                $sDeliveryCosts = false;
            }
        }
        if (false !== $sDeliveryCosts) {
            $sDeliveryCosts = number_format($sDeliveryCosts, 2, '.', '');
        }

        return $sDeliveryCosts;
    }

    /**
     * load attribute by system_name field and return the name.
     *
     * @param string $sIdentifier
     *
     * @return string
     */
    public function getAttributeName($sIdentifier)
    {
        /** @var array<string, string> $aAttributesForSystemNames */
        static $aAttributesForSystemNames = null;

        /** @var array<string, string> $aAttributesForIds */
        static $aAttributesForIds = null;

        if (null === $aAttributesForSystemNames && null === $aAttributesForIds) {
            $oAttributesList = TdbShopAttributeList::GetList();
            while ($oAttribute = &$oAttributesList->Next()) {
                $sName = $oAttribute->GetName();
                $aAttributesForSystemNames[$oAttribute->fieldSystemName] = $sName;
                $aAttributesForIds[$oAttribute->id] = $sName;
            }
        }
        if (isset($aAttributesForSystemNames[$sIdentifier])) {
            return $aAttributesForSystemNames[$sIdentifier];
        } elseif (isset($aAttributesForIds[$sIdentifier])) {
            return $aAttributesForIds[$sIdentifier];
        } else {
            return '';
        }
    }

    /**
     * returns an array with a nested array of values for each requested attribute.
     *
     * @param TdbShopArticle $oArticle
     * @param array          $aAttributeNames
     * @param string         $sFieldName
     *
     * @return array|null
     */
    public function GetArticleAttributeValueListForAttributeNames(
        &$oArticle,
        $aAttributeNames,
        $sFieldName = 'system_name'
    ) {
        $aList = array();
        foreach ($aAttributeNames as $sAttributeName) {
            $aList[$sAttributeName] = $this->GetArticleAttributeValueForAttributeName(
                $oArticle,
                $sAttributeName,
                $sFieldName,
                false
            );
        }

        return $aList;
    }

    /**
     * returns null if requested attribute does not exist
     * returns empty value if requested attribute exists but article has no value for requested attribute
     * returns array of values for requested attribute if $bReturnFirstOccurrence is false.
     *
     * @param TdbShopArticle $oArticle
     * @param string         $sAttributeName
     * @param string         $sFieldName
     * @param bool           $bReturnFirstOccurrence
     *
     * @return string|array|null
     */
    protected function GetArticleAttributeValueForAttributeName(
        &$oArticle,
        $sAttributeName,
        $sFieldName = 'system_name',
        $bReturnFirstOccurrence = true
    ) {
        /**
         * fallback due to refactoring
         * parameter has changed from $bSystemName that forced loading by field "system_name" to $sFieldName.
         */
        if (true === is_bool($sFieldName)) {
            $sFieldName = (true === $sFieldName) ? ('system_name') : ('name');
        }
        static $sArticleIdentifier = null;
        static $aArticleAttributes = null;
        // identifier has changed - new article - clear attributes
        if (null == $sArticleIdentifier || $sArticleIdentifier != $oArticle->id) {
            $sArticleIdentifier = $oArticle->id;
            $aArticleAttributes = null;
        }

        if (null === $aArticleAttributes) {
            $aArticleAttributes = array_flip(array_values($this->aAttributes));
            array_walk(
                $aArticleAttributes,
                function (&$val, $key) {
                    $val = array();
                }
            );

            $databaseConnection = $this->getDatabaseConnection();
            $quotedFieldName = $databaseConnection->quoteIdentifier($sFieldName);
            $quotedArticleIdentifier = $databaseConnection->quote($sArticleIdentifier);
            $sQuery = "SELECT `shop_attribute_value`.*,
                              `shop_attribute`.$quotedFieldName AS selected_attribute_9000
                         FROM `shop_attribute_value`
                    LEFT JOIN `shop_attribute` ON `shop_attribute_value`.`shop_attribute_id` = `shop_attribute`.`id`
                    LEFT JOIN `shop_article_shop_attribute_value_mlt` ON `shop_attribute_value`.`id` = `shop_article_shop_attribute_value_mlt`.`target_id`
                        WHERE `shop_article_shop_attribute_value_mlt`.`source_id` = $quotedArticleIdentifier
            ";
            $aConditions = $this->FilterArticleAttributeValueList();
            if (count($aConditions) > 0) {
                $sQuery .= ' AND '.implode(' AND ', $aConditions);
            }

            $rResult = MySqlLegacySupport::getInstance()->query($sQuery);
            while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($rResult)) {
                $_attribute = $aRow['selected_attribute_9000'];
                if (isset($aArticleAttributes[$_attribute])) {
                    $aArticleAttributes[$_attribute][] = $aRow['name'];
                }
            }
        }

        $bReturnValue = null;

        if (!is_array($aArticleAttributes) || !isset($aArticleAttributes[$sAttributeName]) || !is_array($aArticleAttributes[$sAttributeName])) {
            return null;
        }

        if ($bReturnFirstOccurrence) {
            $bReturnValue = current($aArticleAttributes[$sAttributeName]);
        } elseif (isset($aArticleAttributes[$sAttributeName])) {
            $bReturnValue = $aArticleAttributes[$sAttributeName];
        }

        if (false === $bReturnValue || '' === $bReturnValue) {
            $bReturnValue = null;
        }

        return $bReturnValue;
    }

    /**
     * add where conditions.
     *
     * @return array
     */
    protected function FilterArticleAttributeValueList()
    {
        return array();
    }

    /**
     * returns the link to the article image
     * you can set iWidth to get thumbnail links.
     *
     * @param int|null       $iWidth
     * @param TdbShopArticle $oArticle
     *
     * @return string
     */
    protected function getArticleImageLink(&$oArticle, $iWidth = null)
    {
        $sLink = '';
        $oArticleImage = $oArticle->GetPrimaryImage();
        if (null !== $oArticleImage) {
            $oImage = $oArticleImage->GetImage(0, 'cms_media_id');
            if (null !== $oImage) {
                if (is_int($iWidth) && $iWidth > 0) {
                    $oThumbnail = $oImage->GetThumbnail($iWidth, 1000);
                    $sLink = $oThumbnail->GetFullURL();
                } else {
                    $sLink = $oImage->GetFullURL();
                }
            }
        }

        return $sLink;
    }

    /**
     * clean up the content e.g. for a field that comes from the database or something else
     * basically we only trim the content you should extend that method for each of your export types.
     *
     * @param string $sValue
     *
     * @return string
     */
    protected function CleanContent($sValue)
    {
        $sValue = trim($sValue);

        return $sValue;
    }

    /**
     * stuff you want to do after looping through $this->oArticleList.
     *
     * @return void
     */
    protected function PostArticleListHandling()
    {
    }

    /**
     * this method is always called at the end of Run (even if the export failed) to do any cleanup work
     * copy tmp cache file to real cache file and delete tmp cache file. Do this only when export has written the tmp cache file.
     *
     * @param bool $bSuccess - set to true if the export was successful
     *
     * @return bool
     */
    protected function Cleanup($bSuccess)
    {
        return $bSuccess;
    }

    /**
     * sends input to output buffer.
     *
     * @param string $sInput
     *
     * @return void
     */
    protected function Write($sInput)
    {
        echo $sInput;
        flush();
    }

    /**
     * setter for $this->oArticleList.
     *
     * @param TdbShopArticleList $oArticleList
     *
     * @return void
     */
    public function SetArticleList(TIterator $oArticleList)
    {
        $this->oArticleList = $oArticleList;
    }

    /**
     * getter for $this->oArticleList.
     *
     * @return TIterator|null
     */
    public function GetArticleList()
    {
        return $this->oArticleList;
    }

    /**
     * getter for $this->sCacheFile.
     *
     * @return string|null
     */
    public function GetCacheFile()
    {
        return $this->sCacheFile;
    }

    /**
     * getter for $this->bGenerateFile.
     *
     * @return bool
     */
    public function GetGenerateFile()
    {
        return $this->bGenerateFile;
    }

    /**
     * setter for $this->bDebug.
     *
     * @param bool $bDebug
     *
     * @return void
     */
    public function SetDebug($bDebug)
    {
        $this->bDebug = $bDebug;
    }

    /**
     * getter for $this->bDebug.
     *
     * @return bool
     */
    public function GetDebug()
    {
        return $this->bDebug;
    }

    /**
     * return the content type in the format that the header method understands (e.g. text/plain).
     *
     * @return string
     */
    protected function getContentType()
    {
        return 'text/plain';
    }

    /**
     * Returns tmp cache file name generated from cache file name.
     *
     * @static
     *
     * @param string $sCacheFileName
     *
     * @return bool|mixed|string
     */
    public static function GetTmpCacheFileFromCacheFileName($sCacheFileName)
    {
        $sCacheTmpFileName = false;
        $aFileInfo = pathinfo($sCacheFileName);
        if (is_array($aFileInfo) && isset($aFileInfo['filename'])) {
            $sCacheTmpFileName = 'generate_tmp_'.$aFileInfo['filename'];
            $sCacheTmpFileName = str_replace($aFileInfo['filename'], $sCacheTmpFileName, $sCacheFileName);
        }

        return $sCacheTmpFileName;
    }

    /**
     * return list of all attributes (by default) to restrict this method overwrite.
     *
     * @return array|null
     */
    public function GetAllowedAttributes()
    {
        static $aAttributes = null;

        if (null === $aAttributes) {
            $aAttributes = array();
            $oAttributeList = TdbShopAttributeList::GetList();
            $oAttributeList->GoToStart();
            while ($oAttribute = &$oAttributeList->Next()) {
                $aAttributes[] = $oAttribute->fieldSystemName;
            }
        }

        return $aAttributes;
    }

    /**
     * loads list of available payment methods.
     *
     * @return TdbShopPaymentMethodList|null
     */
    protected function getAvailablePaymentList()
    {
        static $oPaymentList = null;

        if (null === $oPaymentList) {
            $sQuery = "SELECT * FROM `shop_payment_method` WHERE `shop_payment_method`.`active` = '1'";
            $oPaymentList = TdbShopPaymentMethodList::GetList($sQuery);
        }

        return $oPaymentList;
    }

    /**
     * returns comma separated string of payment methods.
     *
     * @return string
     */
    protected function getAvailablePaymentListAsString()
    {
        static $sPaymentMethods = null;

        if (null === $sPaymentMethods) {
            $sPaymentMethods = '';
            $oPaymentList = $this->getAvailablePaymentList();
            if (null !== $oPaymentList) {
                if ($oPaymentList->Length() > 0) {
                    while ($oPaymentMethod = $oPaymentList->Next()) {
                        if ('' != $sPaymentMethods) {
                            $sPaymentMethods .= ', ';
                        }
                        $sPaymentMethods .= $oPaymentMethod->GetName();
                    }
                }
            }
        }

        if (!is_string($sPaymentMethods)) {
            $sPaymentMethods = '';
        }

        return $sPaymentMethods;
    }

    /**
     * @return Connection
     */
    private function getDatabaseConnection()
    {
        return ServiceLocator::get('database_connection');
    }

    private function getCache(): CacheInterface
    {
        return ServiceLocator::get('chameleon_system_core.cache');
    }
}
