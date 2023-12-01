<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;

class pkgShopArticleReviewModuleShopArticleReviewConfiguration {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookup
/** @var cmsTplModuleInstance|null - Belongs to module */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldBoolean
/** @var bool - Only signed in users are allowed to write reviews  */
private bool $AllowWriteReviewLoggedinUsersOnly = true, 
    // TCMSFieldBoolean
/** @var bool - Only signed in users are allowed to read reviews  */
private bool $AllowShowReviewLoggedinUsersOnly = false, 
    // TCMSFieldBoolean
/** @var bool - Manage reviews */
private bool $ManageReviews = false, 
    // TCMSFieldBoolean
/** @var bool - Reviews can be evaluated */
private bool $AllowRateReview = false, 
    // TCMSFieldBoolean
/** @var bool - Customers can notify reviews */
private bool $AllowReportReviews = false, 
    // TCMSFieldBoolean
/** @var bool - Customers can comment on reviews */
private bool $AllowCommentReviews = false, 
    // TCMSFieldNumber
/** @var int - Number of evaluation credits */
private int $RatingCount = 5, 
    // TCMSFieldNumber
/** @var int - Show number of reviews */
private int $CountShowReviews = 3, 
    // TCMSFieldOption
/** @var string - Name of the author */
private string $OptionShowAuthorName = 'full_name', 
    // TCMSFieldVarchar
/** @var string - Heading */
private string $Title = '', 
    // TCMSFieldWYSIWYG
/** @var string - Introduction */
private string $IntroText = '', 
    // TCMSFieldWYSIWYG
/** @var string - Closing text */
private string $OutroText = ''  ) {}

  public function getId(): string
  {
    return $this->id;
  }
  public function setId(string $id): self
  {
    $this->id = $id;
    return $this;
  }

  public function getCmsident(): ?int
  {
    return $this->cmsident;
  }
  public function setCmsident(int $cmsident): self
  {
    $this->cmsident = $cmsident;
    return $this;
  }
    // TCMSFieldLookup
public function getCmsTplModuleInstance(): ?cmsTplModuleInstance
{
    return $this->CmsTplModuleInstance;
}

public function setCmsTplModuleInstance(?cmsTplModuleInstance $CmsTplModuleInstance): self
{
    $this->CmsTplModuleInstance = $CmsTplModuleInstance;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowWriteReviewLoggedinUsersOnly(): bool
{
    return $this->AllowWriteReviewLoggedinUsersOnly;
}
public function setallowWriteReviewLoggedinUsersOnly(bool $AllowWriteReviewLoggedinUsersOnly): self
{
    $this->AllowWriteReviewLoggedinUsersOnly = $AllowWriteReviewLoggedinUsersOnly;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowShowReviewLoggedinUsersOnly(): bool
{
    return $this->AllowShowReviewLoggedinUsersOnly;
}
public function setallowShowReviewLoggedinUsersOnly(bool $AllowShowReviewLoggedinUsersOnly): self
{
    $this->AllowShowReviewLoggedinUsersOnly = $AllowShowReviewLoggedinUsersOnly;

    return $this;
}


  
    // TCMSFieldBoolean
public function ismanageReviews(): bool
{
    return $this->ManageReviews;
}
public function setmanageReviews(bool $ManageReviews): self
{
    $this->ManageReviews = $ManageReviews;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowRateReview(): bool
{
    return $this->AllowRateReview;
}
public function setallowRateReview(bool $AllowRateReview): self
{
    $this->AllowRateReview = $AllowRateReview;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowReportReviews(): bool
{
    return $this->AllowReportReviews;
}
public function setallowReportReviews(bool $AllowReportReviews): self
{
    $this->AllowReportReviews = $AllowReportReviews;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowCommentReviews(): bool
{
    return $this->AllowCommentReviews;
}
public function setallowCommentReviews(bool $AllowCommentReviews): self
{
    $this->AllowCommentReviews = $AllowCommentReviews;

    return $this;
}


  
    // TCMSFieldNumber
public function getratingCount(): int
{
    return $this->RatingCount;
}
public function setratingCount(int $RatingCount): self
{
    $this->RatingCount = $RatingCount;

    return $this;
}


  
    // TCMSFieldNumber
public function getcountShowReviews(): int
{
    return $this->CountShowReviews;
}
public function setcountShowReviews(int $CountShowReviews): self
{
    $this->CountShowReviews = $CountShowReviews;

    return $this;
}


  
    // TCMSFieldOption
public function getoptionShowAuthorName(): string
{
    return $this->OptionShowAuthorName;
}
public function setoptionShowAuthorName(string $OptionShowAuthorName): self
{
    $this->OptionShowAuthorName = $OptionShowAuthorName;

    return $this;
}


  
    // TCMSFieldVarchar
public function gettitle(): string
{
    return $this->Title;
}
public function settitle(string $Title): self
{
    $this->Title = $Title;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getintroText(): string
{
    return $this->IntroText;
}
public function setintroText(string $IntroText): self
{
    $this->IntroText = $IntroText;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getoutroText(): string
{
    return $this->OutroText;
}
public function setoutroText(string $OutroText): self
{
    $this->OutroText = $OutroText;

    return $this;
}


  
}
