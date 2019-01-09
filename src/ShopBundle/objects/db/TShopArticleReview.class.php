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

class TShopArticleReview extends TAdbShopArticleReview
{
    const VIEW_PATH = 'pkgShop/views/db/TShopArticleReview';
    const MSG_CONSUMER_BASE_NAME = 'tshoparticlereview';
    const INPUT_BASE_NAME = 'aReviewData';
    const SESSION_REVIEWED_KEY_NAME = 'sReviewSessionIdentifier';

    /**
     * return the age of the review as a string.
     *
     * @return string
     */
    public function GetReviewAgeAsString()
    {
        $iNow = time();
        $dYear = date('Y', $iNow);
        $dMonth = date('n', $iNow);
        $dDay = date('j', $iNow);
        $dHour = date('G', $iNow);
        $dMin = date('i', $iNow);
        if ('0' == substr($dMin, 0, 1)) {
            $dMin = substr($dMin, 1);
        }

        $iPreviewDate = strtotime($this->fieldDatecreated);
        $dPreviewYear = date('Y', $iPreviewDate);
        $dPreviewMonth = date('n', $iPreviewDate);
        $dPreviewDay = date('j', $iPreviewDate);
        $dPreviewHour = date('G', $iPreviewDate);
        $dPreviewMin = date('i', $iPreviewDate);
        if ('0' == substr($dPreviewMin, 0, 1)) {
            $dPreviewMin = substr($dPreviewMin, 1);
        }

        $iAgeYear = $dYear - $dPreviewYear;
        $iAgeMonth = $dMonth - $dPreviewMonth;
        $iAgeDay = $dDay - $dPreviewDay;
        $iAgeHour = $dHour - $dPreviewHour;
        $iAgeMin = $dMin - $dPreviewMin;
        if ($iAgeMin >= 30) {
            $iAgeHour += 0.5;
        }

        $aAgeParts = array();
        $oLocal = &TCMSLocal::GetActive();
        if ($iAgeYear > 0) {
            $sName = 'Jahr';
            if ($iAgeYear > 1) {
                $sName = 'Jahren';
            }
            $aAgeParts[] = $iAgeYear.' '.$sName;
        }
        if ($iAgeMonth > 0) {
            $sName = 'Monat';
            if ($iAgeMonth > 1) {
                $sName = 'Monaten';
            }
            $aAgeParts[] = $iAgeMonth.' '.$sName;
        }
        if ($iAgeDay > 0) {
            $sName = 'Tag';
            if ($iAgeDay > 1) {
                $sName = 'Tagen';
            }
            $aAgeParts[] = $iAgeDay.' '.$sName;
        }
        if ($iAgeHour > 0) {
            if (1 == $iAgeHour) {
                $aAgeParts[] = $iAgeDay.' Stunde';
            } elseif ($iAgeHour - floor($iAgeHour) > 0) {
                $aAgeParts[] = $oLocal->FormatNumber($iAgeHour, 1).' Stunden';
            } else {
                $aAgeParts[] = $iAgeHour.' Stunden';
            }
        }

        $sAgeString = implode(', ', $aAgeParts);

        if (empty($sAgeString)) {
            $sAgeString = 'weniger als einer halben Stunde';
        }

        return $sAgeString;
    }

    /**
     * used to display an article.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array(), $bAllowCache = true)
    {
        $oView = new TViewParser();
        $oView->AddVar('oReview', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
    }

    protected function GetCacheTrigger($id, $aCallTimeVars = array())
    {
        $aCacheTrigger = $this->GetCacheRelatedTables($id);

        return $aCacheTrigger;
    }

    protected function GetCacheRelatedTables($id)
    {
        $aCacheRelatedTables = parent::GetCacheRelatedTables($id);
        $aCacheRelatedTables[] = array('table' => 'shop_article', 'id' => $this->fieldShopArticleId);
        $aCacheRelatedTables[] = array('table' => 'data_extranet_user', 'id' => $this->fieldDataExtranetUserId);

        return $aCacheRelatedTables;
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
        $aViewVariables = array();

        return $aViewVariables;
    }

    /**
     * load data from row, only allowing user-changeable fields.
     *
     * @param array $aRow
     */
    public function LoadFromRowProtected($aRow)
    {
        $whitelist = $this->getFieldWhitelistForLoadByRow();
        $safeData = [];
        foreach ($aRow as $key => $val) {
            if (\in_array($key, $whitelist, true)) {
                $safeData[$key] = $val;
            }
        }
        $user = TdbDataExtranetUser::GetInstance();
        if ($user->IsLoggedIn()) {
            $safeData['data_extranet_user_id'] = $user->id;
        }
        $this->LoadFromRow($safeData);
    }

    protected function getFieldWhitelistForLoadByRow(): array
    {
        return ['author_name', 'rating', 'comment', 'author_email', 'title', 'send_comment_notification', 'shop_article_id', 'captcha-question'];
    }

    /**
     * send a review notification to the shop owner.
     */
    public function SendNewReviewNotification()
    {
        $oMail = TDataMailProfile::GetProfile('shop-new-review');
        $aData = $this->sqlData;
        $oArticle = &$this->GetFieldShopArticle();
        $aData['ArtikelName'] = $oArticle->GetName();
        $oMail->AddDataArray($aData);
        $oMail->SendUsingObjectView('emails', 'Customer');
    }

    /**
     * is called before the item is saved. $this->sqlData will hold the new data
     * while the original is still in the database.
     *
     * @param bool $bIsInsert - set to true if this is an insert
     */
    protected function PreSaveHook($bIsInsert)
    {
        if ($bIsInsert) {
            $request = $this->getCurrentRequest();
            $sUserIp = null === $request ? '' : $request->getClientIp();
            if (CHAMELEON_PKG_SHOP_REVIEWS_ANONYMIZE_IP) {
                $sUserIp = md5($sUserIp);
            }
            $this->sqlData['user_ip'] = $sUserIp;
        }
    }

    protected function PostInsertHook()
    {
        parent::PostInsertHook();
        if (!TGlobal::IsCMSMode()) {
            $_SESSION[TdbShopArticleReview::SESSION_REVIEWED_KEY_NAME][] = $this->sqlData['shop_article_id'];
        }
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }
}
