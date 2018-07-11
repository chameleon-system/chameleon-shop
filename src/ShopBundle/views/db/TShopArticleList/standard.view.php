<?php

/** @var oArticleList TdbShopArticleList */
while ($oArticle = $oArticleList->Next()) {
    echo $oArticle->Render();
}
