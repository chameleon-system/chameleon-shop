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
 * if we are deleting a bundle article we need to delete the related items
 * note: adding or changing an article will trigger no such change.
 * /**/
class TCMSShopTableEditor_ShopOrderItem extends TCMSTableEditor
{
    /**
     * {@inheritdoc}
     */
    public function Delete($sId = null)
    {
        /**
         * @var TShopOrderItem $item
         */
        $item = $this->oTable;
        if ($item->fieldIsBundle) {
            $oOrderItemTableConf = $item->GetTableConf();
            $oBundleArticles = $item->GetFieldShopOrderBundleArticleList();

            while ($oBundleArticle = $oBundleArticles->Next()) {
                // now delete connected order item
                $oOrderItemEditor = new TCMSTableEditorManager();
                /* @var $oOrderItemEditor TCMSTableEditorManager */
                $oOrderItemEditor->Init($oOrderItemTableConf->id, $oBundleArticle->fieldBundleArticleId);
                $oOrderItemEditor->Delete($oBundleArticle->fieldBundleArticleId);
            }
        }

        parent::Delete($sId);
    }
}
