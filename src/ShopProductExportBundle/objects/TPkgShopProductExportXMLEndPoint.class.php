<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopProductExportXMLEndPoint extends TPkgShopProductExportBase
{
    /**
     * loop through the article list and handle each article.
     */
    protected function HandleArticleList()
    {
        $oArticleList = $this->GetArticleList();
        $iCount = 0;
        if (!is_null($oArticleList)) {
            /** @var $oArticle TdbShopArticle */
            while ($oArticle = &$oArticleList->Next() && !$this->BreakUp($iCount)) {
                $oArticle = $this->PreProcessArticle($oArticle);
                $this->HandleArticle($oArticle);
                ++$iCount;
            }
        }

        return;
    }

    /**
     * do work for one article.
     *
     * @param TdbShopArticle $oArticle
     *
     * @return void
     */
    protected function HandleArticle(&$oArticle)
    {
    }

    /**
     * clean up the content replace all characters that could cause errors in the xml.
     *
     * @param $sValue
     *
     * @return string
     */
    protected function CleanContent($sValue)
    {
        $sValue = parent::CleanContent($sValue);

        $sValue = str_replace('"', '&quot;', $sValue);
        $sValue = str_replace('&', '&amp;', $sValue);
        $sValue = str_replace('>', '&gt;', $sValue);
        $sValue = str_replace('<', '&lt;', $sValue);
        $sValue = str_replace("'", '&apos;', $sValue);

        return $sValue;
    }
}
