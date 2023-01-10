<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSTableEditorShopArticleReview extends TCMSTableEditor
{
    /**
     * gets called after save if all posted data was valid.
     *
     * @param TIterator  $oFields    holds an iterator of all field classes from DB table with the posted values or default if no post data is present
     * @param TCMSRecord $oPostTable holds the record object of all posted data
     */
    protected function PostSaveHook($oFields, $oPostTable)
    {
        $this->UpdateArticleReviewStats($oPostTable->sqlData['shop_article_id']);
        parent::PostSaveHook($oFields, $oPostTable);
    }

    /**
     * updates the review stats for the article connected to the review item.
     *
     * @param string $iArticleId
     * @return void
     */
    protected function UpdateArticleReviewStats($iArticleId)
    {
        $oArticle = TdbShopArticle::GetNewInstance();
        /** @var $oArticle TdbShopArticle */
        if ($oArticle->Load($iArticleId)) {
            $oArticle->UpdateStatsReviews();
        }
    }

    /**
     * need to delete newsletter subscription
     * {@inheritdoc}
     */
    public function Delete($sId = null)
    {
        if (null === $sId) {
            parent::Delete($sId);

            return;
        }
        $iArticleId = null;
        $query = "SELECT * FROM shop_article_review WHERE `id` = '".\MySqlLegacySupport::getInstance()->real_escape_string($sId)."'";
        if ($aReview = \MySqlLegacySupport::getInstance()->fetch_assoc(\MySqlLegacySupport::getInstance()->query($query))) {
            $iArticleId = $aReview['shop_article_id'];
        }
        parent::Delete($sId);
        if (!is_null($iArticleId)) {
            $this->UpdateArticleReviewStats($iArticleId);
        }
    }
}
