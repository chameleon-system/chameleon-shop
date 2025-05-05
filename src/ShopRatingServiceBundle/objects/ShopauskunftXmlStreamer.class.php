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
 * Class ShopauskunftXmlStreamer.
 */
class ShopauskunftXmlStreamer extends XmlStreamer
{
    /** @var string|null */
    private $ratingServiceId;

    /**
     * {@inheritdoc}
     */
    public function processNode($xmlString, $elementName, $nodeIndex)
    {
        $rating = simplexml_load_string($xmlString);
        $ratingId = (string) $rating->attributes()->id;

        if (!$this->checkIfRatingExists($ratingId)) {
            $averageScore = $this->getCriterionsAverageScore($rating);
            if ($averageScore < 0) {
                $averageScore = '';
            }

            $dateTimestamp = strtotime($rating->date);
            $dateMysqlFormat = date('Y-m-d 00:00:00', $dateTimestamp);

            $uuid = TTools::GetUUID();
            $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

            $quotedUuid = $connection->quote($uuid);
            $quotedRatingServiceId = $connection->quote($this->ratingServiceId);
            $quotedRatingId = $connection->quote($ratingId);
            $quotedAverageScore = $connection->quote($averageScore);
            $quotedXmlString = $connection->quote($xmlString);
            $quotedUsername = $connection->quote((string) $rating->evaluator->username);
            $quotedText = $connection->quote((string) $rating->text);
            $quotedDate = $connection->quote($dateMysqlFormat);

            $query = "INSERT INTO pkg_shop_rating_service_rating
                       SET id = {$quotedUuid},
                           pkg_shop_rating_service_id = {$quotedRatingServiceId},
                           remote_key = {$quotedRatingId},
                           score = {$quotedAverageScore},
                           rawdata = {$quotedXmlString},
                           rating_user = {$quotedUsername},
                           rating_text = {$quotedText},
                           rating_date = {$quotedDate}";
            $connection->executeStatement($query);
        }

        return true;
    }

    /**
     * checks if an rating exists.
     *
     * @param string $ratingId
     *
     * @return bool
     */
    private function checkIfRatingExists($ratingId)
    {
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
        $quotedRatingId = $connection->quote($ratingId);

        $query = "SELECT COUNT(*) AS item_count FROM pkg_shop_rating_service_rating WHERE remote_key = {$quotedRatingId}";
        $result = $connection->fetchAssociative($query);

        if ($result && $result['item_count'] < 1) {
            return false;
        }

        return true;
    }

    /**
     * Calculates the average score over all criterions.
     *
     * @param SimpleXMLElement $rating The rating
     *
     * @return float The average score
     */
    protected function getCriterionsAverageScore(SimpleXMLElement $rating)
    {
        $criterions = ['criterion1', 'criterion2', 'criterion3', 'criterion4', 'criterion5', 'criterion6'];
        $sum = 0;
        $divider = 0;
        foreach ($criterions as $criterion) {
            $criterionValue = (float) $rating->$criterion;
            if ($criterionValue > 0) {
                ++$divider;
                $sum += $criterionValue;
            }
        }
        $divider = (0 == $divider) ? count($criterions) : $divider;

        return $sum / $divider;
    }

    /**
     * @param string $ratingServiceId
     *
     * @return void
     */
    public function setRatingServiceId($ratingServiceId)
    {
        $this->ratingServiceId = $ratingServiceId;
    }
}
