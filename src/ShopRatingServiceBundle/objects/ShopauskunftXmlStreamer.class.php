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
    private $ratingServiceId = null;

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
            $query = "INSERT INTO pkg_shop_rating_service_rating
						   SET id = '".$uuid."',
							   pkg_shop_rating_service_id = '".MySqlLegacySupport::getInstance()->real_escape_string($this->ratingServiceId)."',
							   remote_key = '".MySqlLegacySupport::getInstance()->real_escape_string($ratingId)."',
							   score = '".MySqlLegacySupport::getInstance()->real_escape_string($averageScore)."',
							   rawdata = '".MySqlLegacySupport::getInstance()->real_escape_string($xmlString)."',
							   rating_user  = '".MySqlLegacySupport::getInstance()->real_escape_string($rating->evaluator->username)."',
							   rating_text = '".MySqlLegacySupport::getInstance()->real_escape_string($rating->text)."',
							   rating_date = '".MySqlLegacySupport::getInstance()->real_escape_string($dateMysqlFormat)."'";
            MySqlLegacySupport::getInstance()->query($query);
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
        $ratingId = MySqlLegacySupport::getInstance()->real_escape_string($ratingId);

        $sQuery = "SELECT COUNT(*) AS item_count FROM pkg_shop_rating_service_rating WHERE remote_key = '".$ratingId."' ";
        $result = MySqlLegacySupport::getInstance()->query($sQuery);
        if ($result) {
            $row = MySqlLegacySupport::getInstance()->fetch_object($result);
            if ($row->item_count < 1) {
                return false;
            }
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
    protected function getCriterionsAverageScore(\SimpleXMLElement $rating)
    {
        $criterions = array('criterion1', 'criterion2', 'criterion3', 'criterion4', 'criterion5', 'criterion6');
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
