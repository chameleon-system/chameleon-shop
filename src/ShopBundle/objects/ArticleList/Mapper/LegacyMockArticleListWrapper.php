<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Mapper;

use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;

class LegacyMockArticleListWrapper extends \TdbShopArticleList
{
    /**
     * @var ResultDataInterface
     */
    private $result;
    /**
     * @var int
     */
    private $currentPage;
    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var string
     */
    private $listPagerUrl;

    /**
     * @var string
     */
    private $sModuleSpotName;

    /**
     * @param ResultDataInterface $result
     * @param int                 $currentPage
     * @param int                 $pageSize
     * @param string              $listPagerUrl
     * @param string              $sModuleSpotName
     */
    public function __construct(ResultDataInterface $result, $currentPage, $pageSize, $listPagerUrl, $sModuleSpotName)
    {
        $this->result = $result;
        $this->currentPage = $currentPage;
        $this->pageSize = $pageSize;
        $this->listPagerUrl = $listPagerUrl;
        $this->sModuleSpotName = $sModuleSpotName;
    }

    /**
     * @return int
     */
    public function GetCurrentPageNumber()
    {
        return $this->currentPage + 1;
    }

    /**
     * @return int
     */
    public function GetTotalPageCount()
    {
        return $this->result->getNumberOfPages();
    }

    /**
     * @param int $iPageNumber
     *
     * @return string
     */
    public function GetPageJumpLink($iPageNumber)
    {
        return str_replace('_pageNumber_', '{[pageNumber0]}', $this->listPagerUrl);
    }

    /**
     * @param int  $iPageNumber
     * @param bool $bGetAsJSFunction
     *
     * @return string
     */
    public function GetPageJumpLinkAsAJAXCall($iPageNumber, $bGetAsJSFunction = true)
    {
        $listPageUrl = $this->GetPageJumpLink($iPageNumber);
        $listPageUrl .= \TTools::GetArrayAsURL(array(
                'module_fnc' => array($this->sModuleSpotName => 'ExecuteAjaxCall'),
                '_fnc' => 'getRenderedList',
            ), '&');

        return $listPageUrl;
    }

    /**
     * @return int
     */
    public function GetStartRecordNumber()
    {
        if ($this->pageSize < 1) {
            return 1;
        }

        return $this->currentPage * $this->pageSize;
    }

    /**
     * @return int
     */
    public function Length()
    {
        return $this->result->count();
    }

    /**
     * @return int
     */
    public function GetPageSize()
    {
        return $this->pageSize;
    }
}
