<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopSearchFieldWeight extends TAdbShopSearchFieldWeight
{
    /**
     * create field index for the row. Note: we assume the index tables exists.
     *
     * @param array $aRowData
     *
     * @return void
     */
    public function CreateIndexTick($aRowData)
    {
        // make sure the index table exists... if it does not, create it
        if (array_key_exists($this->fieldFieldNameInQuery, $aRowData) && array_key_exists('xxx_shop_article_id', $aRowData)) {
            $sFieldValue = $aRowData[$this->fieldFieldNameInQuery];
            $iArticleId = $aRowData['xxx_shop_article_id'];

            $aIndexTableNames = TdbShopSearchIndexer::GetAllIndexTableNames();
            foreach ($aIndexTableNames as $sTableName => $iSubstringLength) {
                $this->ProcessIndex($sFieldValue, $iArticleId, $sTableName, $iSubstringLength);
            }
        }
    }

    /**
     * @static
     *
     * @param string $sTableName
     *
     * @return string
     */
    public static function GetTmpFileNameForTableImport($sTableName)
    {
        /** @var array<string, string> $aFileNames */
        static $aFileNames = array();

        if (!isset($aFileNames[$sTableName])) {
            $aFileNames[$sTableName] = CMS_TMP_DIR.'/cms_pkg_search_index_'.$sTableName;
        }

        return $aFileNames[$sTableName];
    }

    /**
     * @param string $sTableName
     * @param null|string $sMode
     *
     * @psalm-param 'close'|null $sMode
     *
     * @return false|null|resource
     */
    public static function GetFilePointer($sTableName, $sMode = null)
    {
        /**
         * Uses the table name as a string and the opened resource as a value.
         * @var array<string, resource|false> $aPointer
         */
        static $aPointer = array();

        $pPointer = null;
        switch ($sMode) {
            case 'close':
                reset($aPointer);
                foreach (array_keys($aPointer) as $pointer) {

                    /**
                     * @psalm-suppress InvalidArgument
                     * @FIXME This passes the table name to `fclose` - we probably want to iterate over `array_values` here.
                     */
                    fclose($pointer);
                }
                break;
            default:
                if (!array_key_exists($sTableName, $aPointer)) {
                    $sBasePath = TdbShopSearchFieldWeight::GetTmpFileNameForTableImport($sTableName);
                    $aPointer[$sTableName] = false;
                    if (!file_exists($sBasePath)) {
                        $fp = fopen($sBasePath, 'wb');
                        if (false !== $fp) {
                            fclose($fp);
                            $aPointer[$sTableName] = fopen($sBasePath, 'ab');
                        }
                    } else {
                        $aPointer[$sTableName] = fopen($sBasePath, 'ab');
                    }
                }
                $pPointer = $aPointer[$sTableName];
                break;
        }

        return $pPointer;
    }

    /**
     * process index for field value and a given substring length.
     *
     * @param string $sFieldValue
     * @param int    $iArticleId
     * @param string $sTableName
     * @param int    $iSubstringLength
     *
     * @return void
     */
    protected function ProcessIndex($sFieldValue, $iArticleId, $sTableName, $iSubstringLength)
    {
        $oShop = $this->GetFieldShop();
        $aInserts = array();
        $aInsertsCompleteWords = array();

        $aWords = TdbShopSearchIndexer::PrepareSearchWords($sFieldValue);
        foreach ($aWords as $sWord) {
            $iPos = 0;
            $done = false;
            $iCurrentWordLength = mb_strlen($sWord);
            if ($iCurrentWordLength < $iSubstringLength) {
                continue;
            }
            $bIsCompleteWord = ($iSubstringLength == $iCurrentWordLength);
            if (false === $this->fieldIndexPartialWords && false === $bIsCompleteWord) {
                continue;
            }
            do {
                $done = ($iPos > $iCurrentWordLength - $iSubstringLength);
                if (false === $done) {
                    $sSubStr = mb_substr($sWord, $iPos, $iSubstringLength);
                    if (!array_key_exists($sSubStr, $aInserts)) {
                        $aInserts[$sSubStr] = array('count' => 0, 'weight' => 0);
                    }
                    $dWeight = ($iSubstringLength / $oShop->fieldShopSearchMaxIndexLength) * $oShop->fieldShopSearchWordLengthFactor * ($this->fieldWeight);
                    // is this a complete word?
                    $bIsCompleteWord = (mb_strlen($sSubStr) == $iCurrentWordLength);

                    if ($bIsCompleteWord) {
                        $dWeight = $dWeight * (1 + $oShop->fieldShopSearchWordBonus);
                    }

                    $bAllowInsert = false;
                    if (!$bIsCompleteWord) {
                        // if this is not a complete word, and partial indexing is active, we allow insert
                        if ($this->fieldIndexPartialWords) {
                            $bAllowInsert = true;
                        }
                    } else {
                        // if this is a complete word, we allow index only the word is not in the black list
                        $bAllowInsert = $this->AllowInsertOfThisCompleteWord($sSubStr);
                    }
                    if ($bAllowInsert) {
                        $aInserts[$sSubStr]['count'] = $aInserts[$sSubStr]['count'] + 1;
                        $aInserts[$sSubStr]['weight'] = $aInserts[$sSubStr]['weight'] + $dWeight / $aInserts[$sSubStr]['count'];
                        if ($bIsCompleteWord) {
                            // insert soundex

                            if (!array_key_exists($sSubStr, $aInsertsCompleteWords)) {
                                $aInsertsCompleteWords[$sSubStr] = array('count' => 0, 'weight' => 0);
                            }
                            $aInsertsCompleteWords[$sSubStr]['count'] = $aInsertsCompleteWords[$sSubStr]['count'] + 1;
                            $aInsertsCompleteWords[$sSubStr]['weight'] = $aInsertsCompleteWords[$sSubStr]['weight'] + ($dWeight / (1 + $oShop->fieldShopSearchWordBonus));
                        }
                    }
                }
                ++$iPos;
            } while (!$done);
        }

        // now insert sections into database;
        foreach ($aInserts as $sSubString => $aSubStringInfo) {
            $this->AddIndexToTable($sTableName, $iArticleId, $sSubString, $aSubStringInfo['count'], $aSubStringInfo['weight']);
        }

        foreach ($aInsertsCompleteWords as $sSubString => $aSubStringInfo) {
            $this->InsertSoundex($iArticleId, $sSubString, $aSubStringInfo['count'], $aSubStringInfo['weight']);
        }
    }

    /**
     * @param string $sTableName
     * @param int $iArticleId
     * @param string $sSubString
     * @param int $iCount
     * @param float $dWeight
     *
     * @return void
     */
    protected function AddIndexToTable($sTableName, $iArticleId, $sSubString, $iCount, $dWeight)
    {
        $sSubString = trim($sSubString);
        $sTableName = '_tmp'.$sTableName;
        if ($iCount > 0 && $dWeight > 0 && !empty($sSubString)) {
            $aTmpData = array();
            $aTmpData['shop_article_id'] = $iArticleId;
            $aTmpData['substring'] = $sSubString;
            $aTmpData['occurrences'] = $iCount;
            $aTmpData['weight'] = $dWeight;
            $aTmpData['shop_search_field_weight_id'] = $this->id;
            $aTmpData['cms_language_id'] = $this->sqlData['cms_language_id'];
            if (CMS_SEARCH_INDEX_USE_LOAD_FILE) {
                $pPointer = self::GetFilePointer($sTableName);
                if (false !== $pPointer) {
                    fwrite($pPointer, implode("\t", $aTmpData)."\n");
                }
            } else {
                TdbShopSearchFieldWeight::AddQueryBlock(true, $sTableName, $aTmpData);
            }
        }
    }

    /**
     * @param bool $bCollect
     * @param null|string $sTableName
     * @param array<string, mixed>|null $aData
     *
     * @return void
     */
    public static function AddQueryBlock($bCollect, $sTableName, $aData)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        static $aQueries = [];
        static $aQSize = [];

        if ($bCollect) {
            if (!array_key_exists($sTableName, $aQueries)) {
                $aQueries[$sTableName] = "INSERT INTO `{$sTableName}` (shop_article_id, substring, occurrences, weight, shop_search_field_weight_id, cms_language_id) VALUES ";
                $aQSize[$sTableName] = 0;
            }

            $quotedSubstring = $connection->quote($aData['substring']);

            $aQueries[$sTableName] .= "('{$aData['shop_article_id']}',{$quotedSubstring},'{$aData['occurrences']}','{$aData['weight']}','{$aData['shop_search_field_weight_id']}','{$aData['cms_language_id']}'),";
            ++$aQSize[$sTableName];

            if ($aQSize[$sTableName] > 1000) {
                $sQuery = mb_substr($aQueries[$sTableName], 0, -1);
                $connection->executeStatement($sQuery);
                unset($aQueries[$sTableName], $aQSize[$sTableName]);
            }
        } else {
            reset($aQueries);
            foreach ($aQueries as $sTableName => $sSourceQuery) {
                if (true === empty($sSourceQuery)) {
                    continue;
                }
                $sQuery = mb_substr($sSourceQuery, 0, -1);
                $connection->executeStatement($sQuery);
                $aQueries[$sTableName] = '';
                $aQSize[$sTableName] = 0;
            }
        }
    }
    /**
     * insert soundex for word.
     *
     * @param string $sOriginalWord
     * @param float  $dOriginalWeight
     * @param int $iArticleId
     * @param int $iCount
     *
     * @return void
     */
    protected function InsertSoundex($iArticleId, $sOriginalWord, $iCount, $dOriginalWeight)
    {
        $sSoundEx = TdbShopSearchIndexer::GetSoundexForWord($sOriginalWord);

        $sSoundEx = TdbShopSearchIndexer::PrepareSearchWord($sSoundEx); // clean and cut the word
        if (!empty($sSoundEx) && '0000' != $sSoundEx) {
            $oShop = $this->GetFieldShop();
            // now add to index
            $sIndexTable = TdbShopSearchIndexer::GetIndexTableNameForIndexLength(mb_strlen($sSoundEx));
            $dWeight = $dOriginalWeight * $oShop->fieldShopSearchSoundexPenalty;
            $this->AddIndexToTable($sIndexTable, $iArticleId, $sSoundEx, $iCount, $dWeight);
        }
    }

    /**
     * return true if the complete word should be indexed.
     *
     * @param string $sCompleteWord
     *
     * @return bool
     */
    protected function AllowInsertOfThisCompleteWord($sCompleteWord)
    {
        return true;
    }
}
