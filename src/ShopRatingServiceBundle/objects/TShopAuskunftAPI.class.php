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
 * TShopAuskunftAPI Class to fetch data.
 *
/**/
class TShopAuskunftAPI
{
    use \ChameleonSystem\CoreBundle\BackwardsCompatibilityShims\NamedConstructorSupport;

    /** @var resource */
    protected $parser;

    /** @var bool  */
    protected $bRatingListInProgress = false;

    /** @var array<string, string> */
    protected $aRatingItem = array();

    /** @var bool */
    protected $bCriteriaInProgress = false;

    /** @var array array<string, string> */
    protected $aCriteriaItem = array();

    /**
     * Current active tag
     * @var string
     */
    protected $current = '';

    /** @var bool */
    protected $inside_data = false;

    /** @var array<string, mixed> */
    public $aAPIData = array();

    /** @var string|null */
    public $xmlParsingError;

    /**
     * Constructor.
     *
     * @psalm-suppress UndefinedFunction
     *
     * @FIXME callbacks should be specified as `[$this, 'function']`
     */
    public function __construct()
    {
        if (!($this->parser = xml_parser_create())) {
            die('Cannot create parser');
        }

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'start_tag', 'end_tag');
        xml_set_character_data_handler($this->parser, 'tag_contents');
    }

    /**
     * @deprecated Named constructors are deprecated and will be removed with PHP8. When calling from a parent, please use `parent::__construct` instead.
     * @see self::__construct
     * @return void
     */
    public function TShopAuskunftAPI()
    {
        $this->callConstructorAndLogDeprecation(func_get_args());
    }

    //-------------------------------------------------------------------------

    /**
     * Get content from URL.
     *
     * @param string $sURL
     *
     * @return string
     */
    public function ReadFromURI($sURL)
    {
        $sBuf = '';
        $fp = @fopen($sURL, 'r');
        if (!$fp) {
            echo "<!-- shopauskunft - no connection! -->\n";
        } else {
            stream_set_timeout($fp, 5);
            while (!feof($fp)) {
                $sBuf .= fread($fp, 128);
            }
            fclose($fp);
        }

        return $sBuf;
    }

    /**
     * @see https://www.php.net/manual/en/function.xml-set-element-handler.php
     *
     * @param resource $parser
     * @param string $name
     * @param array<string, string> $attribs
     *
     * @return void
     */
    public function start_tag($parser, $name, $attribs)
    {
        $this->current = $name;
        $this->inside_data = false;

        //if ($name == "RATINGLIST") { echo "<pre>liste begin\n"; }
        //if ($name == "DEEPLINK") { echo "<a href=\""; }

        if ('RATINGLIST' == $name) {
            $this->bRatingListInProgress = true;
        }
        if ('CRITERIA' == $name) {
            $this->bCriteriaInProgress = true;
        }

        if ('RATING' == $name) {
            if (is_array($attribs)) {
                foreach ($attribs as $key => $val) {
                    //echo strtolower($key)."=\"".$val."\"";
                    if ('id' === strtolower($key)) {
                        $this->aRatingItem['id'] = $val;
                    }
                }
            }
        }
    }

    /**
     * @see https://www.php.net/manual/en/function.xml-set-element-handler.php
     * @param resource $parser
     * @param string $name
     *
     * @return void
     */
    public function end_tag($parser, $name)
    {
        //if ($name == "RATINGLIST") { echo "liste end</pre>"; }
        //if ($name == "DEEPLINK") { echo "\">LINK</a>"; }

        if ('RATINGLIST' == $name) {
            $this->bRatingListInProgress = false;
        }
        if ('RATING' == $name) {
            $this->aAPIData['Shopauskunft']['Shop']['ratinglist'][] = $this->aRatingItem;
        }

        if ('CRITERIA' == $name) {
            $this->bCriteriaInProgress = false;
        }
        if ('CRITERION1' == $name) {
            $this->aAPIData['Shopauskunft']['Shop']['criteria']['criterion1'] = $this->aCriteriaItem;
        }
        if ('CRITERION2' == $name) {
            $this->aAPIData['Shopauskunft']['Shop']['criteria']['criterion2'] = $this->aCriteriaItem;
        }
        if ('CRITERION3' == $name) {
            $this->aAPIData['Shopauskunft']['Shop']['criteria']['criterion3'] = $this->aCriteriaItem;
        }
        if ('CRITERION4' == $name) {
            $this->aAPIData['Shopauskunft']['Shop']['criteria']['criterion4'] = $this->aCriteriaItem;
        }
        if ('CRITERION5' == $name) {
            $this->aAPIData['Shopauskunft']['Shop']['criteria']['criterion5'] = $this->aCriteriaItem;
        }
        if ('CRITERION6' == $name) {
            $this->aAPIData['Shopauskunft']['Shop']['criteria']['criterion6'] = $this->aCriteriaItem;
        }

        $this->current = $name;
        $this->inside_data = false;
    }

    /**
     * @see https://www.php.net/manual/en/function.xml-set-character-data-handler.php
     *
     * @param resource $parser
     * @param string $data
     *
     * @return void
     */
    public function tag_contents($parser, $data)
    {
        if (strlen(trim($data)) > 0) {
            if ('SHOPAUSKUNFTID' == $this->current) {
                $this->aAPIData['Shopauskunft']['Shop']['shopauskunftID'] = $data;
            }
            if ('PARTNERMERCHANTID' == $this->current) {
                $this->aAPIData['Shopauskunft']['Shop']['partnerMerchantID'] = $data;
            }
            if ('SYMPATHY' == $this->current) {
                $this->aAPIData['Shopauskunft']['Shop']['sympathy'] = $data;
            }
            if ('DEEPLINK' == $this->current) {
                if (!$this->bRatingListInProgress) {
                    $this->aAPIData['Shopauskunft']['Shop']['deeplink'] = $data;
                }
            }
            if ('SEAL' == $this->current) {
                $this->aAPIData['Shopauskunft']['Shop']['seal'] = $data;
            }
            if ('NAME' == $this->current) {
                $this->aAPIData['Shopauskunft']['Shop']['name'] = $data;
            }

            //address
            if (isset($this->aAPIData['Shopauskunft']['Shop']['address'])) {
                $aAddr = $this->aAPIData['Shopauskunft']['Shop']['address'];
            } else {
                $aAddr = array();
                $this->aAPIData['Shopauskunft']['Shop']['address'] = $aAddr;
            }
            if ('DOMAIN' == $this->current) {
                $aAddr['domain'] = $data;
            }
            if ('EMAIL' == $this->current) {
                $aAddr['email'] = $data;
            }
            if ('STREET' == $this->current) {
                $aAddr['street'] = $data;
            }
            if ('ZIP' == $this->current) {
                $aAddr['zip'] = $data;
            }
            if ('CITY' == $this->current) {
                $aAddr['city'] = $data;
            }
            if ('PHONE' == $this->current) {
                $aAddr['phone'] = $data;
            }
            if ('FAX' == $this->current) {
                $aAddr['fax'] = $data;
            }
            if (isset($aAddr)) {
                $this->aAPIData['Shopauskunft']['Shop']['address'] = $aAddr;
            }

            //rating_summary
            if (isset($this->aAPIData['Shopauskunft']['Shop']['rating_summary'])) {
                $aRatSum = $this->aAPIData['Shopauskunft']['Shop']['rating_summary'];
            }
            if ('SCORE_OVERALL' == $this->current) {
                $aRatSum['score_overall'] = $data;
            }
            if ('RATING_OVERALL' == $this->current) {
                $aRatSum['rating_overall'] = $data;
            }
            if ('RATED_FIRST' == $this->current) {
                $aRatSum['rated_first'] = $data;
            }
            if ('RATED_LAST' == $this->current) {
                $aRatSum['rated_last'] = $data;
            }
            if ('TOTAL' == $this->current) {
                $aRatSum['total'] = $data;
            }
            if ('POSITIVE_TOTAL' == $this->current) {
                $aRatSum['positive_total'] = $data;
            }
            if ('NEUTRAL_TOTAL' == $this->current) {
                $aRatSum['neutral_total'] = $data;
            }
            if ('NEGATIVE_TOTAL' == $this->current) {
                $aRatSum['negative_total'] = $data;
            }
            if ('CRITERION1_AVG' == $this->current) {
                $aRatSum['criterion1_avg'] = $data;
            }
            if ('CRITERION2_AVG' == $this->current) {
                $aRatSum['criterion2_avg'] = $data;
            }
            if ('CRITERION3_AVG' == $this->current) {
                $aRatSum['criterion3_avg'] = $data;
            }
            if ('CRITERION4_AVG' == $this->current) {
                $aRatSum['criterion4_avg'] = $data;
            }
            if ('CRITERION5_AVG' == $this->current) {
                $aRatSum['criterion5_avg'] = $data;
            }
            if ('CRITERION6_AVG' == $this->current) {
                $aRatSum['criterion6_avg'] = $data;
            }
            if (isset($aRatSum)) {
                $this->aAPIData['Shopauskunft']['Shop']['rating_summary'] = $aRatSum;
            }

            //rating
            if ('SCORETEXT' == $this->current) {
                $this->aRatingItem['scoretext'] = $data;
            }
            if ('SCORE' == $this->current) {
                $this->aRatingItem['score'] = $data;
            }

            if ('TEXT' == $this->current) {
                if ($this->inside_data) {
                    $this->aRatingItem['text'] .= $data; // need to concatenate data!!!
                } else {
                    $this->aRatingItem['text'] = $data;
                }
            }

            if ('BNR' == $this->current) {
                $this->aRatingItem['bnr'] = $data;
            }
            if ('DATE' == $this->current) {
                $this->aRatingItem['date'] = $data;
            }

            if ('CRITERION1' == $this->current) {
                $this->aRatingItem['criterion1'] = $data;
            }
            if ('CRITERION2' == $this->current) {
                $this->aRatingItem['criterion2'] = $data;
            }
            if ('CRITERION3' == $this->current) {
                $this->aRatingItem['criterion3'] = $data;
            }
            if ('CRITERION4' == $this->current) {
                $this->aRatingItem['criterion4'] = $data;
            }
            if ('CRITERION5' == $this->current) {
                $this->aRatingItem['criterion5'] = $data;
            }
            if ('CRITERION6' == $this->current) {
                $this->aRatingItem['criterion6'] = $data;
            }

            if ($this->bRatingListInProgress) {
                if ('DEEPLINK' == $this->current) {
                    $this->aRatingItem['deeplink'] = $data;
                }
            }

            //legend
            if (isset($this->aAPIData['Shopauskunft']['Shop']['legend'])) {
                $aLegend = $this->aAPIData['Shopauskunft']['Shop']['legend'];
            }
            if ('TIME' == $this->current) {
                $aLegend['time'] = $data;
            }

            //criteria
            if ($this->bCriteriaInProgress) {
                //if ( in_array($this->current, array('CRITERION1', 'CRITERION2', 'CRITERION3', 'CRITERION4', 'CRITERION5', 'CRITERION6'))) {}
                if ('NAME' == $this->current) {
                    $this->aCriteriaItem['name'] = $data;
                }
                if ('DESC' == $this->current) {
                    $this->aCriteriaItem['desc'] = $data;
                }
            }
            //criteria-end

            if (isset($aLegend)) {
                $this->aAPIData['Shopauskunft']['Shop']['legend'] = $aLegend;
            }
            //legend-end
        }

        $this->inside_data = true;
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    public function parse($data)
    {
        if (!empty($data)) {
            //$data = eregi_replace(">"."[[:space:]]+"."< ",">< ",$data);
            $data = preg_replace("/>\s+</i", '><', $data);
            if (!xml_parse($this->parser, $data, true)) {
                $this->xmlParsingError = '';
                $this->xmlParsingError = xml_error_string(xml_get_error_code($this->parser));
                $this->xmlParsingError .= xml_get_current_line_number($this->parser);

                return false;
            //+++ die($reason);
            } else {
                return true;
            }
        } else {
            $this->xmlParsingError = 'No data to parse!';

            return false;
        }
    }

    /**
     * @param null $data
     *
     * @return void
     */
    public function ParseXML($data = null)
    {
        /*
      $encoding = 'UTF-8';

          $php_errormsg="";
          $this->result="";
          $this->evalCode="";
          $values="";
          if (!$data)
          return 'Cannot open xml document: ' . (isset($php_errormsg) ? $php_errormsg : $file);

          $parser = xml_parser_create($encoding);
          xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
          xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
          $ok = xml_parse_into_struct($parser, $data, $values);
          if (!$ok) {
              $errmsg = sprintf("XML parse error %d '%s' at line %d, column %d (byte index %d)",
              xml_get_error_code($parser),
              xml_error_string(xml_get_error_code($parser)),
              xml_get_current_line_number($parser),
              xml_get_current_column_number($parser),
              xml_get_current_byte_index($parser));
          }

          xml_parser_free($parser);
          if (!$ok)
          return $errmsg;
          */
    }
}

if (!function_exists('file_put_contents')) {
    /**
     * @FIXME This should be safe to remove
     *
     * @param string $filename
     * @param string $data
     *
     * @return bool|int
     */
    function file_put_contents($filename, $data)
    {
        $f = @fopen($filename, 'wb');
        if (!$f) {
            return false;
        } else {
            $bytes = fwrite($f, $data);
            fclose($f);

            return $bytes;
        }
    }
}
