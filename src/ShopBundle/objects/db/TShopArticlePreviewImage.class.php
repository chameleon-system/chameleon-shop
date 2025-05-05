<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopArticlePreviewImage extends TShopArticlePreviewImageAutoParent
{
    public const VIEW_PATH = '/pkgShop/views/db/TShopArticlePreviewImage';

    /**
     * the connected article.
     *
     * @var TdbShopArticle
     */
    protected $oArticle;
    /**
     * the preview image object.
     *
     * @var TCMSImage
     */
    protected $oImage;
    /**
     * the image size of the preview image.
     *
     * @var TdbShopArticleImageSize
     */
    protected $oImageSize;

    /**
     * return the image preview object. if the shop did not define one for this size,
     * then we create a virtual instance based on the first image of the article.
     *
     * @param TdbShopArticle $oArticle - the article object
     * @param string $sInternalName - image size name
     *
     * @return bool
     */
    public function LoadByName($oArticle, $sInternalName)
    {
        $bLoaded = false;
        $this->oArticle = $oArticle;
        $this->oImageSize = TdbShopArticleImageSize::GetNewInstance();
        /* @var $oImageSize TdbShopArticleImageSize */
        if ($this->oImageSize->LoadFromField('name_internal', $sInternalName)) {
            if ($this->LoadFromFields(['shop_article_id' => $oArticle->id, 'shop_article_image_size_id' => $this->oImageSize->id])) {
                $bLoaded = true;
            } else {
                $oPrimaryImage = $oArticle->GetPrimaryImage();
                /** @var $oPrimaryImage TdbShopArticleImage */
                if (!is_null($oPrimaryImage)) {
                    $aData = ['cms_media_id' => $oPrimaryImage->sqlData['cms_media_id'], 'shop_article_image_size_id' => $this->oImageSize->id, 'shop_article_id' => $oArticle->id];
                    $this->LoadFromRow($aData);
                    $bLoaded = true;
                }
            }
        }

        return $bLoaded;
    }

    /**
     * render the preview image using the defined view (found in self::VIEW_PATH).
     *
     * @param string $sView - the view
     * @param string $type - Core, Custom-Core, Customer
     * @param string[] $aEffects
     * @param bool $bHideNewMarker
     *
     * @return string
     */
    public function Render($sView = 'simple', $type = 'Core', $aEffects = [], $bHideNewMarker = false)
    {
        $oView = new TViewParser();

        $oArticle = $this->GetArticleObject();
        $oArticleSize = $this->GetImageSizeObject();
        $oImage = $this->GetImageObject();

        $oView->AddVar('oArticle', $oArticle);
        $oView->AddVar('oImageSize', $oArticleSize);
        $oView->AddVar('oImage', $oImage);
        $oView->AddVar('oPreviewImage', $this);
        $oView->AddVar('bHideNewMarker', $bHideNewMarker);
        $oThumb = $this->GetImageThumbnailObject($aEffects);
        $oView->AddVar('oImageThumbnail', $oThumb);

        return $oView->RenderObjectPackageView($sView, self::VIEW_PATH, $type);
    }

    /**
     * fetch the connected article
     * Note: we provide a separate method for this (instead of using GetLookup) because we want
     * to make sure that these objects are properly cached.
     *
     * @return TdbShopArticle|null
     */
    public function GetArticleObject()
    {
        if (is_null($this->oArticle)) {
            $this->oArticle = TdbShopArticle::GetNewInstance();
            if (!$this->oArticle->Load($this->sqlData['shop_article_id'])) {
                $this->oArticle = null;
            }
        }

        return $this->oArticle;
    }

    /**
     * fetch the connected image object
     * Note: we provide a separate method for this (instead of using GetLookup) because we want
     * to make sure that these objects are properly cached.
     *
     * @return TCMSImage|null
     */
    public function GetImageObject()
    {
        if (is_null($this->oImage)) {
            $this->oImage = $this->GetImage(0, 'cms_media_id', true);
        }

        return $this->oImage;
    }

    /**
     * return thumbnail for current size.
     *
     * @param string[] $aEffects
     *
     * @return TCMSImage
     */
    public function GetImageThumbnailObject($aEffects = [])
    {
        $oImageSize = $this->GetImageSizeObject();
        $oImage = $this->GetImageObject();
        $oThumb = null;
        TdbCmsConfigImagemagick::SetEnableEffects(true);
        $bEnableEffects = TdbCmsConfigImagemagick::GetEnableEffects();

        if ($oImageSize->fieldForceSize) {
            $oThumb = $oImage->GetForcedSizeThumbnail($oImageSize->fieldWidth, $oImageSize->fieldHeight);
        } else {
            $oThumb = $oImage->GetThumbnail($oImageSize->fieldWidth, $oImageSize->fieldHeight, true, $aEffects);
        }
        TdbCmsConfigImagemagick::SetEnableEffects($bEnableEffects);

        return $oThumb;
    }

    /**
     * fetch the image size definition for this preview image
     * Note: we provide a separate method for this (instead of using GetLookup) because we want
     * to make sure that these objects are properly cached.
     *
     * @return TdbShopArticleImageSize|null
     */
    public function GetImageSizeObject()
    {
        if (is_null($this->oImageSize)) {
            $this->oImageSize = TdbShopArticleImageSize::GetNewInstance();
            if (!$this->oImageSize->Load($this->sqlData['shop_article_image_size_id'])) {
                $this->oImageSize = null;
            }
        }

        return $this->oImageSize;
    }
}
