<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopArticleReviewList extends TAdbShopArticleReviewList
{
    const VIEW_PATH = 'pkgShop/views/db/TShopArticleReviewList';

    /**
     * set to the owning article id, if list is generated via GetPublishedReviews.
     *
     * @var string
     */
    protected $iArticleId = null;

    /**
     * return the average score for the review list.
     *
     * @return float
     */
    public function GetAverageScore()
    {
        $dAvgScore = 0;
        if ($this->Length() > 0) {
            $iPt = $this->getItemPointer();
            $this->GoToStart();
            $dScore = 0;
            while ($oitem = $this->Next()) {
                $dScore += $oitem->fieldRating;
            }
            $this->setItemPointer($iPt);

            $dAvgScore = $dScore / $this->Length();
        }

        return $dAvgScore;
    }

    /**
     * return all published items.
     *
     * @param int $iArticle
     * @param int $iLanguage
     *
     * @return TdbShopArticleReviewList
     */
    public static function GetPublishedReviews($iArticle, $iLanguage = null)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedArticleId = $connection->quote($iArticle);

        $sFilter = "`shop_article_review`.`shop_article_id` = {$quotedArticleId} ";
        $sFilter .= "AND `shop_article_review`.`publish` = '1'";

        $sQuery = self::GetDefaultQuery($iLanguage, $sFilter);
        $oList = TdbShopArticleReviewList::GetList($sQuery, $iLanguage);
        $oList->SetOwningArticleId($iArticle);

        return $oList;
    }
    /**
     * return all published items.
     *
     * @param int $iUserId
     * @param int $iLanguage
     *
     * @return TdbShopArticleReviewList
     */
    public static function GetPublishedReviewsForUser($iUserId, $iLanguage = null)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedUserId = $connection->quote($iUserId);

        $sFilter = "`shop_article_review`.`data_extranet_user_id` = {$quotedUserId} ";
        $sFilter .= "AND `shop_article_review`.`publish` = '1'";

        $sQuery = self::GetDefaultQuery($iLanguage, $sFilter);
        $oList = TdbShopArticleReviewList::GetList($sQuery, $iLanguage);

        return $oList;
    }
    /**
     * use the method to set the owning article id when generating a list for only one article.
     *
     * @param string $iArticleId
     *
     * @return void
     */
    public function SetOwningArticleId($iArticleId)
    {
        $this->iArticleId = $iArticleId;
    }

    /**
     * used to display an article.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     * @param bool $bAllowCache
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Core', $aCallTimeVars = array(), $bAllowCache = true)
    {
        $oView = new TViewParser();
        $oView->AddVar('oReviewList', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $oView->AddVar('iArticleId', $this->iArticleId);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
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
        return array();
    }
}
