<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Forms;

use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\GridFieldArchiveAction;
use SilverStripe\Versioned\Versioned;

class GridFieldDeletedAchiveAction extends GridFieldArchiveAction
{
    /**
     * Gets the content for the column, this basically says if it's deleted from the stage you can't delete it
     * @param GridField $gridField Grid Field Reference
     * @param DataObject $record Current data object being rendered
     * @param string $columnName Name of the column
     * @return string The HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName)
    {
        if (!DataObject::has_extension($gridField->getModelClass(), Versioned::class)) {
            user_error($gridField->getModelClass() . ' does not have the Versioned extension', E_USER_WARNING);
            return;
        }

        $isDeletedFromDraft = (!$record->hasMethod('isOnDraft') ? $record->isOnLiveOnly() : !$record->isOnDraft());
        if ($gridField->State->ListDisplayMode->ShowDeletedItems == 'Y' && $isDeletedFromDraft) {
            return;
        }

        return parent::getColumnContent($gridField, $record, $columnName);
    }

    /**
     * Gets the group this menu item will belong to. A null value should indicate the button should not display.
     * @see {@link GridField_ActionMenu->getColumnContent()}
     * @param GridField $gridField
     * @param DataObject $record
     * @return string|null $group
     */
    public function getGroup($gridField, $record, $columnName)
    {
        if (!DataObject::has_extension($gridField->getModelClass(), Versioned::class)) {
            user_error($gridField->getModelClass() . ' does not have the Versioned extension', E_USER_WARNING);
            return;
        }

        $isDeletedFromDraft = (!$record->hasMethod('isOnDraft') ? $record->isOnLiveOnly() : !$record->isOnDraft());
        if ($gridField->State->ListDisplayMode->ShowDeletedItems == 'Y' && $isDeletedFromDraft) {
            return;
        }

        return parent::getGroup($gridField, $record, $columnName);
    }
}
