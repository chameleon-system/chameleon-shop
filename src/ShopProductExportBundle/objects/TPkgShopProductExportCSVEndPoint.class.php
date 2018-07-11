<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopProductExportCSVEndPoint extends TPkgShopProductExportBase
{
    /**
     * delimiter for output for each field.
     *
     * @var string
     */
    protected $sDelimiter = "\t";

    /**
     * encloses each field value with the given string.
     *
     * @var string
     */
    protected $sEnclosure = '';

    /**
     * returns the list of all available fields as array.
     *
     * @return array
     */
    protected function GetFields()
    {
        return array();
    }

    /**
     * get the fields and write it on top of the file (first line).
     */
    protected function PreArticleListHandling()
    {
        $aFields = $this->GetFields();
        $this->Write($this->sEnclosure.implode($this->sEnclosure.$this->sDelimiter.$this->sEnclosure, $aFields).$this->sEnclosure."\n");
    }

    /**
     * loop through the article list and handle each article.
     */
    protected function HandleArticleList()
    {
        $aFields = $this->GetFields();
        $oArticleList = $this->GetArticleList();
        $iCount = 0;
        if (!is_null($oArticleList)) {
            /** @var $oArticle TdbShopArticle */
            while ($oArticle = &$oArticleList->Next() && !$this->BreakUp($iCount)) {
                $oArticle = $this->PreProcessArticle($oArticle);
                $this->HandleArticle($oArticle, $aFields);
                ++$iCount;
            }
        }

        return;
    }

    /**
     * do work for one article
     * loops through the available fields and calls GetFieldValue method for each field.
     *
     * @param TdbShopArticle $oArticle
     * @param array          $aFields
     *
     * @return string
     */
    protected function HandleArticle(&$oArticle, &$aFields)
    {
        $iStart = microtime(true);
        static $iCount = 0;
        static $sMemUsageBeforeArticleProcessed = null;
        static $sMemUsageTmp = null;
        ++$iCount;
        if (null === $sMemUsageBeforeArticleProcessed) {
            $sMemUsageBeforeArticleProcessed = memory_get_usage();
            $sMemUsageTmp = $sMemUsageBeforeArticleProcessed;
        }

        $aFieldValues = array();
        reset($aFields);
        foreach ($aFields as $sFieldName) {
            $sTmpMemBefore = memory_get_usage();
            $aFieldValues[] = $this->GetFieldValue($sFieldName, $oArticle);
            $sTmpMemAfter = memory_get_usage();
            $sTmpDiff = $sTmpMemAfter - $sTmpMemBefore;
        }
        $sLine = $this->sEnclosure.implode($this->sEnclosure.$this->sDelimiter.$this->sEnclosure, $aFieldValues).$this->sEnclosure;

        $this->Write($sLine."\n");

        $iLogCount = 1000;
//        $iLogCount = 100;

        if (0 === $iCount % $iLogCount) {
            $sMemUsageAfterArticleProcessed = memory_get_usage();

            $sMemDifference = $sMemUsageAfterArticleProcessed - $sMemUsageBeforeArticleProcessed;
            $sMemDifference = $sMemDifference / 1024;
            $sMemDifference = $sMemDifference / 1024;

            $sMemDifferenceTmp = $sMemUsageAfterArticleProcessed - $sMemUsageTmp;
            $sMemDifferenceTmp = $sMemDifferenceTmp / 1024;
            $sMemDifferenceTmp = $sMemDifferenceTmp / 1024;

            if ($this->GetDebug()) {
                TTools::WriteLogEntry('memory difference after processing '.$iLogCount.' articles: '.$sMemDifference.'MB ('.$sMemDifferenceTmp.'MB for '.$iLogCount.' articles) - total article count: '.$iCount, 1, __FILE__, __LINE__);
            }

            $sMemUsageTmp = $sMemUsageAfterArticleProcessed;
        }
        $iEnd = microtime(true);
        $iTime = $iEnd - $iStart;
        if ($this->GetDebug()) {
            TTools::WriteLogEntry(
                "\n start ".$iStart.
                    "\n end ".$iEnd.
                    "\n time for one article: ".$iTime,
                1,
                __FILE__,
                __LINE__,
                'exportTime.log'
            );
        }

        return $sLine;
    }

    /**
     * returns value for the given field name
     * could be done by a switch case for each field handling.
     *
     * @param string         $sFieldName
     * @param TdbShopArticle $oArticle
     *
     * @return mixed
     */
    protected function GetFieldValue($sFieldName, &$oArticle)
    {
        return '';
    }

    /**
     * Clean double blanks in content and replace delimiter to avoid errors.
     *
     * @param $sValue
     *
     * @return string
     */
    protected function CleanContent($sValue)
    {
        $sValue = parent::CleanContent($sValue);
        $sValue = preg_replace('/\ +/i', ' ', $sValue);
        if (',' != $this->sDelimiter && "\t" != $this->sDelimiter && "\n" != $this->sDelimiter) {
            $sValue = str_replace($this->sDelimiter, ',', $sValue);
        } else {
            $sValue = str_replace($this->sDelimiter, ' ', $sValue);
        }

        return $sValue;
    }
}
