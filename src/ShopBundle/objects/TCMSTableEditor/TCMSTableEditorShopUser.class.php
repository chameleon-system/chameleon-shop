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
 * special handling for the shop users - we want to sync the newsletter info with the user info.
/**/
class TCMSTableEditorShopUser extends TableEditorExtranetUser
{
    /**
     * gets called after save if all posted data was valid.
     *
     * @param TIterator  $oFields    holds an iterator of all field classes from DB table with the posted values or default if no post data is present
     * @param TCMSRecord $oPostTable holds the record object of all posted data
     */
    protected function PostSaveHook($oFields, $oPostTable)
    {
        $this->UpdateNewsletterInfo($oPostTable);
        parent::PostSaveHook($oFields, $oPostTable);
    }

    /**
     * @param TCMSRecord $oPostTable
     *
     * @return void
     */
    protected function UpdateNewsletterInfo($oPostTable)
    {
        $query = "SELECT * FROM `pkg_newsletter_user` WHERE `data_extranet_user_id` = '".\MySqlLegacySupport::getInstance()->real_escape_string($oPostTable->id)."'";
        if ($aRow = \MySqlLegacySupport::getInstance()->fetch_assoc(\MySqlLegacySupport::getInstance()->query($query))) {
            $oUser = TdbDataExtranetUser::GetNewInstance();
            /** @var $oUser TdbDataExtranetUser */
            if ($oUser->Load($oPostTable->id)) {
                $oTableConf = new TCMSTableConf();
                /** @var $oTableConf TCMSTableConf */
                $oTableConf->LoadFromField('name', 'pkg_newsletter_user');
                $oEditor = new TCMSTableEditorManager();
                /** @var $oEditor TCMSTableEditorTree */
                $oEditor->Init($oTableConf->id, $aRow['id']);
                $aRow['email'] = $oUser->GetUserEMail();
                $aRow['data_extranet_salutation_id'] = $oUser->fieldDataExtranetSalutationId;
                $aRow['lastname'] = $oUser->fieldLastname;
                $aRow['firstname'] = $oUser->fieldFirstname;
                $oEditor->AllowEditByAll(true);
                $oEditor->Save($aRow);
                $oEditor->AllowEditByAll(false);
            }
        }
    }

    /**
     * @param string $iUserId
     *
     * @return void
     */
    protected function DeleteNewsletterInfo($iUserId)
    {
        $query = "SELECT * FROM `pkg_newsletter_user` WHERE `data_extranet_user_id` = '".\MySqlLegacySupport::getInstance()->real_escape_string($iUserId)."'";
        if ($aRow = \MySqlLegacySupport::getInstance()->fetch_assoc(\MySqlLegacySupport::getInstance()->query($query))) {
            $oTableConf = new TCMSTableConf();
            /** @var $oTableConf TCMSTableConf */
            $oTableConf->LoadFromField('name', 'pkg_newsletter_user');
            $oEditor = new TCMSTableEditorManager();
            /** @var $oEditor TCMSTableEditorTree */
            $oEditor->Init($oTableConf->id, $aRow['id']);
            $oEditor->Delete($aRow['id']);
        }
    }

    /**
     * {@inheritdoc}
     *
     * Also deletes newsletter subscriptions.
     */
    public function Delete($sId = null)
    {
        if (null !== $sId) {
            $this->DeleteNewsletterInfo($sId);
        }
        parent::Delete($sId);
    }
}
