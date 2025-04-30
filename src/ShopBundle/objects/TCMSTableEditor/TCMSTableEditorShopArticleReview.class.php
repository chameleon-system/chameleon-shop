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

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $iArticleId = null;
        $quotedId = $connection->quote($sId);

        $query = "SELECT * FROM `shop_article_review` WHERE `id` = {$quotedId}";
        if ($aReview = $connection->fetchAssociative($query)) {
            $iArticleId = $aReview['shop_article_id'];
        }

        parent::Delete($sId);

        if (!is_null($iArticleId)) {
            $this->UpdateArticleReviewStats($iArticleId);
        }
    }}
