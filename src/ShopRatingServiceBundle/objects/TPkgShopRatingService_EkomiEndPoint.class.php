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
    /** @var bool */
    public $bDebug = true;

    /** @var string */
    protected $sCsvFilePath = '';

    /**
     * Don't send usual rating email, but send a copy of the order email to eKomi.
     *
     * @param array<string, mixed> $aOrder
     * @param TdbDataExtranetUser $oUser
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
     *
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
        // http://api.ekomi.de/get_feedback.php?interface_id=ID&interface_pw=PASSWORD&type=csv
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
     *
     * @return void
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
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sRemoteKey = md5($aCSV[0].$aCSV[1]);
        $quotedRemoteKey = $connection->quote($sRemoteKey);

        $sQuery = "SELECT COUNT(*) AS item_count FROM pkg_shop_rating_service_rating WHERE remote_key = {$quotedRemoteKey}";
        $aCheck = $connection->fetchAssociative($sQuery);

        if ($aCheck && $aCheck['item_count'] < 1) {
            $data = [
                'id' => TTools::GetUUID(),
                'pkg_shop_rating_service_id' => $this->id,
                'remote_key' => $sRemoteKey,
                'score' => $aCSV[2],
                'rawdata' => TTools::mb_safe_serialize($aCSV),
                'rating_text' => str_replace('\n', '<br />', $aCSV[3]),
                'rating_date' => date('Y-m-d H:i:s', $aCSV[0]),
            ];

            $connection->insert('pkg_shop_rating_service_rating', $data);
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
     * @param int $iLine
     *
     * @return void
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
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_rating_service.util.cache');
    }
}
