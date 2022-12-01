<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Request;

class TPkgShopPaymentIpnTrigger extends TPkgShopPaymentIpnTriggerAutoParent
{
    /**
     * @param TdbPkgShopPaymentIpnMessageTrigger $oMessageTrigger
     *
     * @return void
     */
    public function runTrigger(TdbPkgShopPaymentIpnMessageTrigger $oMessageTrigger)
    {
        $oIpnMessage = $oMessageTrigger->GetFieldPkgShopPaymentIpnMessage();

        $sURL = $this->fieldTargetUrl;
        $aPayload = $oIpnMessage->fieldPayload;

        $oToHost = new TPkgCmsCoreSendToHost($sURL);
        $oToHost
            ->setPayload($aPayload)
            ->setMethod(TPkgCmsCoreSendToHost::METHOD_POST);

        try {
            $oToHost->executeRequest();
            if (200 !== $oToHost->getLastResponseCode()) {
                throw new TPkgCmsException(
                    'IPN invalid response header code ['.$oToHost->getLastResponseCode().'] (expected 200)'
                );
            }
            if ('OK' !== $oToHost->getLastResponseBody()) {
                throw new TPkgCmsException(
                    'IPN invalid response body - expecting "OK"'
                );
            }
            $aUpdate = array(
                'done' => '1',
                'done_date' => date('Y-m-d H:i:s'),
                'success' => '1',
                'attempt_count' => $oMessageTrigger->fieldAttemptCount + 1,
                'log' => $oMessageTrigger->fieldLog."\n"
                    .$this->getLogHeader(__FILE__, __LINE__)."\n"
                    .'REQUEST: '.$oToHost->getLastRequest()."\n"
                    .'RESPONSE: '.$oToHost->getLastResponseHeader().$oToHost->getLastResponseBody()
                    ."\n-----------------------------------------------------------------\n",
            );
            $oMessageTrigger->AllowEditByAll(true);
            $oMessageTrigger->SaveFieldsFast($aUpdate);
        } catch (TPkgCmsException $e) {
            $aUpdate = array(
                'attempt_count' => $oMessageTrigger->fieldAttemptCount + 1,
                'log' => $oMessageTrigger->fieldLog."\n"
                    .$this->getLogHeader(__FILE__, __LINE__)."\n"
                    .'REQUEST: '.$oToHost->getLastRequest()."\n"
                    .'RESPONSE: '.$oToHost->getLastResponseHeader().$oToHost->getLastResponseBody()
                    .'EXCEPTION: '.(string) $e
                    ."\n-----------------------------------------------------------------\n",
            );
            $iNextAttempt = $this->getNextAttemptTimestamp($oMessageTrigger->fieldAttemptCount + 1);
            if (false === $iNextAttempt) {
                $aUpdate['done'] = '1';
                $aUpdate['done_date'] = '1';
                $aUpdate['success'] = '0';
            } else {
                $aUpdate['next_attempt'] = date('Y-m-d H:i:s', $iNextAttempt);
            }

            $oMessageTrigger->AllowEditByAll(true);
            $oMessageTrigger->SaveFieldsFast($aUpdate);
        }
    }

    /**
     * @param string $sCallFromFile
     * @param int    $iLineNumber
     *
     * @return string
     */
    private function getLogHeader($sCallFromFile, $iLineNumber)
    {
        $sOriginalURL = '';
        $sReferer = '';
        if (is_array($_SERVER) && array_key_exists('HTTP_REFERER', $_SERVER)) {
            $sReferer = $_SERVER['HTTP_REFERER'];
        }

        $sUserName = 'UNKNOWN USER';
        $request = $this->getCurrentRequest();

        $session = true === $request->hasSession() ? $request->getSession() : null;

        if (null !== $session && true === $session->isStarted()) {
            if (class_exists('TdbDataExtranetUser', false)) {
                $oUser = TdbDataExtranetUser::GetInstance();
                if ($oUser && !empty($oUser->fieldName)) {
                    $sUserName = $oUser->fieldName;
                }
            }
            $oSmartUrl = TCMSSmartURLData::GetActive();
            if ($oSmartUrl) {
                $sOriginalURL = $oSmartUrl->sOriginalURL;
            } else {
                $sOriginalURL = $_SERVER['REQUEST_URI'];
            }
        } else {
            // no session - fill using normal data
            $sOriginalURL = $_SERVER['REQUEST_URI'];
        }

        $aData = array(
            date('Y-m-d H:i:s'),
            'PID: '.TTools::GetProcessId(),
            $request->getClientIp(),
            $sUserName,
            $sReferer,
            $sOriginalURL,
            'CALL: '.$sCallFromFile,
            'LINE: '.$iLineNumber,
        );

        return implode('|', $aData);
    }

    /**
     * @param int $iAttemptNumber
     * @return int|false
     *
     * @psalm-param positive-int $iAttemptNumber
     * @psalm-return positive-int|false
     */
    private function getNextAttemptTimestamp($iAttemptNumber)
    {
        $aMapping = array(
            0,
            0,
            60 * 5,    // 5 min
            60 * 15,   // 15 min
            60 * 60,   // 1 hour
            60 * 60 * 4, // 4 hours
            60 * 60 * 4, // 4 hours
            60 * 60 * 8, // 8 hours
            60 * 60 * 24, // 24 hours
            60 * 60 * 24, // 24 hours
            60 * 60 * 24, // 24 hours
        );
        if ($iAttemptNumber >= count($aMapping) || $iAttemptNumber < 0) {
            return false;
        }

        return time() + $aMapping[$iAttemptNumber];
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }
}
