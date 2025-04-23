# Chameleon System ShopNewsletterSignupWithOrderBundle

## Setup

Add the following lines in the order confirm view to show the newsletter checkbox:

```php
<?php if ($bShowNewsletterSignup) { ?>
    <div class="newsletter">
        <label><input type="checkbox" value="1" <?php if ($newsletter) echo 'checked="checked"';?> name="aInput[newsletter]" /> <?=\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_newsletter.form.subscribe_to_all')?></label>
    </div>
<?php } ?>
```