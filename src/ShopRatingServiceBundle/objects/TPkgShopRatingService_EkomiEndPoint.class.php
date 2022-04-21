<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ShopRatingService\Util\CacheUtil;

class TPkgShopRatingService_EkomiEndPoint extends TdbPkgShopRatingService
{
    public $bDebug = true;

    protected $sCsvFilePath = '';

    /**
     * Don't send usual rating email, but send a copy of the order email to eKomi.
     *
     * @param $oUser
     * @param $aOrder
     *
     * @return bool
     */
    public function SendShopRatingEmail($oUser, $aOrder)
    {
        return true;
    }

    /**
     * @return bool
     *
     * @psalm-suppress NullArgument
     * @FIXME Second argument to `fgetcsv` does not accept `null` anymore as of PHP 5.x. Supplying `0` should behave exactly the same.
     */
    public function Import()
    {
        $bSuccess = false;
        $cacheDir = $this->getCacheUtil()->getCacheDirectory();
        if (null === $cacheDir) {
            $this->WriteLogEntry("Cache directory $cacheDir could not be found or written.", __LINE__);

            return false;
        }
        $this->sCsvFilePath = $cacheDir.'ekomi_ratings.csv';
        //http://api.ekomi.de/get_feedback.php?interface_id=ID&interface_pw=PASSWORD&type=csv
        if ($this->FetchCSVFile()) {
            if (false !== ($handle = fopen($this->sCsvFilePath, 'r'))) {
                while ($aCSV = fgetcsv($handle, null, ',', '"')) {
                    $this->ImportLine($aCSV);
                }
                fclose($handle);
                $this->UpdateMainScroeValue();
                $bSuccess = true;
            }
        }

        return $bSuccess;
    }

    /**
     * @param array $aCSV
     */
    protected function ImportLine($aCSV)
    {
        /*
         *   [0] => 1336027185
             [1] => 9341446
             [2] => 5
             [3] => Ich bin 100% zufrieden, die Ware kam schnell und bruchsicher verpackt bei mir an. Leider hatte ich zuviele Teile bestellt, die ich sofort zurück geschickt habe. Es gab keine Probleme, habe mein Geld sofort zurück erhalten.
             [4] =>
         */
        $sRemoteKey = md5($aCSV[0].$aCSV[1]);
        $sQuery = "SELECT COUNT(*) AS item_count FROM pkg_shop_rating_service_rating WHERE remote_key = '".MySqlLegacySupport::getInstance()->real_escape_string($sRemoteKey)."'";
        $aCheck = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($sQuery));
        if ($aCheck['item_count'] < 1) {
            $sInsertSQL = "INSERT INTO `pkg_shop_rating_service_rating` SET
                `id` = '".TTools::GetUUID()."',
                `pkg_shop_rating_service_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."',
                `remote_key` = '".MySqlLegacySupport::getInstance()->real_escape_string($sRemoteKey)."',
                `score` = '".MySqlLegacySupport::getInstance()->real_escape_string($aCSV[2])."',
                `rawdata` = '".MySqlLegacySupport::getInstance()->real_escape_string(TTools::mb_safe_serialize($aCSV))."',
                `rating_text` = '".MySqlLegacySupport::getInstance()->real_escape_string(str_replace('\n', '<br />', $aCSV[3]))."',
                `rating_date` = '".date('Y-m-d H:i:s', $aCSV[0])."'
            ";
            MySqlLegacySupport::getInstance()->query($sInsertSQL);
        }
    }

    /**
     * Fetches the csv data from the api and saves it to the rating cache.
     *
     * @return bool
     */
    protected function FetchCSVFile()
    {
        $bFileWritten = false;

        $sCSVData = file_get_contents($this->fieldRatingUrl);

        if (!empty($sCSVData)) {
            if (false !== file_put_contents($this->sCsvFilePath, $sCSVData)) {
                $bFileWritten = true;
            } else {
                $this->WriteLogEntry('Could not read CSV from '.$this->fieldRatingUrl, __LINE__);
            }
        } else {
            $this->WriteLogEntry($this->sCsvFilePath.' could not be written.', __LINE__);
        }

        return $bFileWritten;
    }

    /**
     * @param string $sMessage
     * @param int    $iLine
     */
    protected function WriteLogEntry($sMessage, $iLine)
    {
        if ($this->bDebug) {
            echo $sMessage.' in Line '.$iLine.'<br /><br />';
        }
        TTools::WriteLogEntrySimple($sMessage, 4, __FILE__, $iLine);
    }

    /**
     * @return CacheUtil
     */
    private function getCacheUtil()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_rating_service.util.cache');
    }
}
