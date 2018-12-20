<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Forms;

use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

class GridFieldDeletedColumns extends GridFieldDataColumns {
    /**
     * Attributes for the element containing the content returned by {@link getColumnContent()}.
     * @param GridField $gridField
     * @param DataObject $record displayed in this row
     * @param string $columnName
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName) {
        $attributes=parent::getColumnAttributes($gridField, $record, $columnName);
        
        $isDeletedFromDraft=(!$record->hasMethod('isOnDraft') ? $record->isOnLiveOnly():!$record->isOnDraft());
        if($gridField->State->ListDisplayMode->ShowDeletedItems=='Y' && DataObject::has_extension($gridField->getModelClass(), Versioned::class) && $isDeletedFromDraft) {
            Requirements::css('webbuilders-group/silverstripe-gridfield-deleted-items: css/GridFieldDeletedColumns.css');
            
            if(array_key_exists('class', $attributes)) {
                $attributes['class'].=' deleted-record';
            }else {
                $attributes['class'].='deleted-record';
            }
        }
        
        
        return $attributes;
    }
}
?>