<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * import the order status from a csv. the system will send a mail to users
 * to inform them of their order changes.
/**/
class TShopInterface_ImportOrderStatus extends TCMSInterfaceManagerBase
{
    protected $sFileName = '';
    protected $sPathName = '';
    protected $fp = null;
    protected $aHeaderFields = array();
    protected $sLineSplit = ';';

    protected function PrepareImport()
    {
        $this->sPathName = PATH_CMS_CUSTOMER_DATA.'/'.$this->GetParameter('sPath').'/';
        $this->sFileName = $this->sPathName.$this->GetParameter('sFileName');
        $bEverythingOk = true;
        if (!is_file($this->sFileName)) {
            $bEverythingOk = false;
            echo '<br />Couldn\'t find: '.$this->sFileName;
            echo '<br />Remember to set sPath and sFileName Parameters!';
        } else {
        }

        return $bEverythingOk;
    }

    public function PerformImport()
    {
        $this->fp = fopen($this->sFileName, 'rb');

        // get first line as the header line
        $this->aHeaderFields = $this->GetLine();
        while ($aRow = $this->GetLine()) {
            // prepare data
            $aTmpData = array();
            reset($this->aHeaderFields);
            foreach ($this->aHeaderFields as $iFieldIndex => $sFieldName) {
                if (array_key_exists($iFieldIndex, $aRow)) {
                    $aTmpData[$sFieldName] = trim($aRow[$iFieldIndex]);
                }
            }
            $this->ProcessRow($aTmpData);
        }
    }

    /**
     * process one row from the file.
     *
     * @param array $aRow
     */
    protected function ProcessRow($aRow)
    {
        // update order
        $sOrderNr = '';
        $sStatusCode = '';
        if (array_key_exists($this->GetParameter('sFieldOrderNr'), $aRow)) {
            $sOrderNr = $aRow[$this->GetParameter('sFieldOrderNr')];
        }
        if (array_key_exists($this->GetParameter('sFieldStatusCode'), $aRow)) {
            $sStatusCode = $aRow[$this->GetParameter('sFieldStatusCode')];
        }
        if (!empty($sOrderNr) && !empty($sStatusCode)) {
            $sDate = date('Y-m-d H:i:s');
            $oOrder = TdbShopOrder::GetNewInstance();
            if ($oOrder->LoadFromField('ordernumber', $sOrderNr)) { // order found.
                $oStatusData = new TPkgShopOrderStatusData($oOrder, $sStatusCode, time());
                $oStatus = new TPkgShopOrderStatusManager();
                $oStatus->addStatus($oStatusData);
            }
        }
    }

    /**
     * get a line from the file.
     *
     * @return array
     */
    protected function GetLine()
    {
        $sLine = fgets($this->fp, 2048 * 16);
        if (false === $sLine) {
            return $sLine;
        }
        // find out if the line contains an uneven number of '"'. if so, we assume, that the line contains a line break
        $sLine = trim($sLine);
        $aTmpParts = explode('"', $sLine);
        if (1 != (count($aTmpParts) % 2)) {
            // need to read until we find the first '"'...
            $bDone = false;
            do {
                $sTmpLine = fgets($this->fp, 2048 * 16);
                if (false === $sTmpLine) {
                    $bDone = true;
                } else {
                    $sTmpLine = trim($sTmpLine);
                    $sLine .= "\n".$sTmpLine;
                    $aTmpParts = explode('"', $sTmpLine);
                    if (1 != (count($aTmpParts) % 2)) {
                        $bDone = true;
                    }
                }
            } while (!$bDone);
        }
        $sLine = utf8_encode($sLine);
        $aParts = explode($this->sLineSplit, $sLine);

        // strip quotes
        foreach (array_keys($aParts) as $sIndex) {
            $aParts[$sIndex] = trim($aParts[$sIndex]);
            if ('"' == substr($aParts[$sIndex], 0, 1) && '"' == substr($aParts[$sIndex], -1)) {
                $aParts[$sIndex] = substr($aParts[$sIndex], 1, -1);
            }
        }
        reset($aParts);

        return $aParts;
    }
}
