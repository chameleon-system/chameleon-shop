Chameleon System ShopArticleReviewBundle
========================================

Overview
--------
The ShopArticleReviewBundle provides article review functionality for shop articles (product reviews). It integrates with the Chameleon System CMS and e-commerce modules to allow customers to view, write, rate, comment on, and report reviews for products.

Features
--------
- Display average rating and total review count for each article
- List published reviews with optional truncation and "show more" toggle
- Restrict reading or writing reviews to logged-in users
- Submit reviews with title, rating, comment, and configurable author display
- CAPTCHA support for guest reviews (requires CmsCaptchaBundle)
- Rate and report reviews (requires allowRateReview and allowReportReviews)
- Comment on reviews (requires pkgComment bundle)
- My Account view for users to manage their own reviews

Installation
------------
This bundle is included by default in the Chameleon System. To register manually:

```php
// in AppKernel::registerBundles() or bundles.php
new ChameleonSystem\ShopArticleReviewBundle\ChameleonSystemShopArticleReviewBundle(),
```

Configuration
-------------
Module configuration is managed in the CMS backend under Packages > Shop Article Review:

- Only signed-in users can write reviews (Allow Write Review for Logged-in Users Only)
- Only signed-in users can read reviews (Allow Read Reviews for Logged-in Users Only)
- Customers can rate reviews (Allow Rate Review)
- Customers can report reviews (Allow Report Reviews)
- Customers can comment on reviews (Allow Comment on Reviews)
- Number of rating stars (Rating Count)
- Number of reviews displayed initially (Count Show Reviews)
- Author display type (Show Author Name): alias, alias_provided, full_name, initials, anonymous
- Module heading (Title)
- Introduction text (Intro Text)
- Closing text (Outro Text)

Usage
-----
### Display reviews on a product page
Place the review module in your page template

### My Account section
Use the provided view to display reviews written by the user.

### Template customization
Override default templates by creating custom view files in:

```
src/ShopArticleReviewBundle/views/WebModules/MTPkgShopArticleReview/
```

API Reference
-------------
**MTPkgShopArticleReviewCore** (module class):
- WriteReview()
- RateReview()
- ReportReview()
- DeleteReview()
- UnlockReview()
- EditReview()
- ChangeReviewReportNotificationState()
- WriteComment(), ReportComment(), RespondToComment(), EditComment(), DeleteComment() (requires pkgCommentBundle)

**TdbShopArticleReview** (review model):
- GetReviewCount(): int
- GetReviewAverageScore(): float
- Render(string $position, string $renderType, array $options = []): string
- SendReviewCommentNotification(TdbPkgComment $comment): void

**AuthorDisplayConstants**:
- AUTHOR_DISPLAY_TYPE_ALIAS
- AUTHOR_DISPLAY_TYPE_ALIAS_PROVIDED
- AUTHOR_DISPLAY_TYPE_FULL_NAME
- AUTHOR_DISPLAY_TYPE_INITIALS
- AUTHOR_DISPLAY_TYPE_ANONYMOUS

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root.
