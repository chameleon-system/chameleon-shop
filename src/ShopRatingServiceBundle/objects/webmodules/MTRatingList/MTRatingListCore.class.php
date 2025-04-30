<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

/**
 *  Module class for RatingList-Module.
 */
class MTRatingListCore extends TUserCustomModelBase
{
    /**
     * @var int
     */
    protected $iActivePage = 0;

    /**
     * @var int
     */
    protected $iPageSize = 3;

    /**
     * @var int[]
     */
    protected $aPageSizes = [20, 40, 60]; // array(3,6,9,-1); -1=All

    /**
     * @var array{name: string, id: string}[]
     */
    protected $aPageSort = [['name' => 'Neueste zuerst', 'id' => 'new_first'], ['name' => 'Beste zuerst', 'id' => 'best_first'], ['name' => 'Schlechteste zuerst', 'id' => 'bad_first']];

    /**
     * @var string
     */
    protected $sActivePageSort = '';

    public function Init()
    {
        parent::Init();

        // get page size from session
        $this->iPageSize = intval($this->GetModuleSessionParameter('pagesize', $this->iPageSize));

        /* @psalm-suppress InvalidPropertyAssignmentValue */
        $this->iActivePage = $this->GetUserInput('page', $this->iActivePage, TCMSUserInput::FILTER_INT);

        /* @psalm-suppress InvalidPropertyAssignmentValue */
        $this->iPageSize = $this->GetUserInput('pagesize', $this->iPageSize, TCMSUserInput::FILTER_INT);

        if (!in_array($this->iPageSize, $this->aPageSizes)) {
            $this->iPageSize = $this->aPageSizes[0];
        }
        $this->SetModuleSessionParameter('pagesize', $this->iPageSize);
        $this->SetActivePageSort();
    }

    /**
     * Set active page sort, load from user input, session, default.
     *
     * @return void
     */
    protected function SetActivePageSort()
    {
        $this->sActivePageSort = $this->GetUserInput('pagesort');
        if (empty($this->sActivePageSort)) {
            // load default
            $this->sActivePageSort = $this->GetPageSortDefaultValue(); // default
            $this->SetModuleSessionParameter('pagesort', $this->sActivePageSort);
        } else {
            $bFound = false;
            foreach ($this->aPageSort as $iPsKey => $aPsVal) {
                if (in_array($this->sActivePageSort, $aPsVal)) {
                    $bFound = true;
                }
            }
            if (!$bFound) {
                // try to load from session
                $this->sActivePageSort = strtolower(trim($this->GetModuleSessionParameter('pagesort', false)));
                if (!$this->sActivePageSort) {
                    $this->sActivePageSort = $this->aPageSort[1]['id'];
                } // default
                $this->SetModuleSessionParameter('pagesort', $this->sActivePageSort);
            }
        }
    }

    /**
     * @return string
     */
    protected function GetPageSortDefaultValue()
    {
        return $this->aPageSort[1]['id'];
    }

    /**
     * @param string $sName
     */
    protected function GetModuleSessionParameter($sName, $vDefault = null)
    {
        $sResult = $vDefault;
        if (array_key_exists('cmsMTRatingListCore', $_SESSION) && array_key_exists($sName, $_SESSION['cmsMTRatingListCore'])) {
            $sResult = $_SESSION['cmsMTRatingListCore'][$sName];
        }

        return $sResult;
    }

    /**
     * @param string $sName
     *
     * @return void
     */
    protected function SetModuleSessionParameter($sName, $sValue)
    {
        if (!array_key_exists('cmsMTRatingListCore', $_SESSION)) {
            $_SESSION['cmsMTRatingListCore'] = [];
        }
        $_SESSION['cmsMTRatingListCore'][$sName] = $sValue;
    }

    public function Execute()
    {
        parent::Execute();

        $sOrderBy = ' `rating_date` DESC';
        switch ($this->sActivePageSort) {
            case $this->aPageSort[0]['id']:
                $sOrderBy = ' `rating_date` DESC';
                break;
            case $this->aPageSort[1]['id']:
                $sOrderBy = ' `score` DESC, `rating_date` DESC ';
                break;
            case $this->aPageSort[2]['id']:
                $sOrderBy = ' `score` ASC';
                break;
        }
        $sQuery = 'SELECT * FROM `pkg_shop_rating_service_rating` ORDER BY '.$sOrderBy;

        $oRatingItemList = TdbPkgShopRatingServiceRatingList::GetList($sQuery);
        if ($this->iPageSize > 0) {
            $oRatingItemList->SetPagingInfo($this->iActivePage * $this->iPageSize, $this->iPageSize);
        } else {
            $oRatingItemList->SetPagingInfo(0, $this->iPageSize);
        }
        $this->data['oRatingItemList'] = $oRatingItemList;

        $aArrowButtons = [];
        $aArrowButtons['fw'] = new stdClass();
        $aArrowButtons['fw']->bIsActive = true;
        $aArrowButtons['bw'] = new stdClass();
        $aArrowButtons['bw']->bIsActive = true;

        $oActivePage = $this->getActivePageService()->getActivePage();

        foreach ($this->aPageSort as $iPsKey => $aPsVal) {
            $aPageParam = [$this->sModuleSpotName => ['pagesort' => $aPsVal['id']]];
            $oPageLink = new stdClass();
            $oPageLink->sSortName = $aPsVal['name'];
            $oPageLink->sSortId = $aPsVal['id'];
            $oPageLink->bIsActive = ($aPsVal['id'] == $this->sActivePageSort);
            $oPageLink->sLink = $oActivePage->GetRealURLPlain($aPageParam);
            $aPageSort[] = $oPageLink;
        }

        $aPageNavi = [];
        if ($oRatingItemList->GetTotalPageCount() > 1) {
            $iActivePage = $oRatingItemList->GetCurrentPageNumber();
            $iMax = $oRatingItemList->GetTotalPageCount();
            for ($i = 0; $i < $iMax; ++$i) {
                $aPageParam = [$this->sModuleSpotName => ['page' => $i]];
                $oPageLink = new stdClass();
                $oPageLink->iPageNumber = $i;
                $oPageLink->bIsActive = ($i == $iActivePage - 1);
                $oPageLink->sLink = $oActivePage->GetRealURLPlain($aPageParam);
                $aPageNavi[] = $oPageLink;

                if ($oPageLink->bIsActive && (0 == $i)) {
                    $aArrowButtons['bw']->bIsActive = false;
                } else {
                    $aPageParam = [$this->sModuleSpotName => ['page' => $i - 1]];
                    $aArrowButtons['bw']->sLink = $oActivePage->GetRealURLPlain($aPageParam);
                }
                if ($oPageLink->bIsActive && ($i == $iMax - 1)) {
                    $aArrowButtons['fw']->bIsActive = false;
                } else {
                    $aPageParam = [$this->sModuleSpotName => ['page' => $i]];
                    $aArrowButtons['fw']->sLink = $oActivePage->GetRealURLPlain($aPageParam);
                }
            }
        }

        $aPageSizeNavi = [];
        foreach ($this->aPageSizes as $iPageSize) {
            $aPageSizeParam = [$this->sModuleSpotName => ['pagesize' => $iPageSize]];
            $oPageSizeLink = new stdClass();
            $oPageSizeLink->iPageSize = $iPageSize;
            $oPageSizeLink->bIsActive = ($iPageSize == $oRatingItemList->GetPageSize());
            $oPageSizeLink->sLink = $oActivePage->GetRealURLPlain($aPageSizeParam);
            $aPageSizeNavi[] = $oPageSizeLink;
        }

        $this->data['aPageSort'] = $aPageSort;
        $this->data['aPageNavi'] = $aPageNavi;
        $this->data['aArrowButtons'] = $aArrowButtons;
        $this->data['aPageSizeNavi'] = $aPageSizeNavi;

        return $this->data;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
