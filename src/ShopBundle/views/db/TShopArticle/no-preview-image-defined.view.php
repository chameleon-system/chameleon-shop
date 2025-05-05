<?php
/**
 * used in place of a preview image if an article requests an image even though that article has not
 * images. Defined variables:.
 *
 * @var $oArticle       TdbShopArticle - the calling article
 * @var $sImageSizeName string - the image size requested
 */
$translator = ChameleonSystem\CoreBundle\ServiceLocator::get('translator');
$message = $translator->trans('chameleon_system_shop.product.error_no_preview_image', [], ChameleonSystem\CoreBundle\i18n\TranslationConstants::DOMAIN_FRONTEND);
echo $message;
