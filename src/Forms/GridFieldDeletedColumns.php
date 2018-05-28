<?php

namespace WebbuildersGroup\GridFieldDeletedItems\Forms;


use Object;

use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\GridField\GridFieldDataColumns;


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
        
        if($gridField->State->ListDisplayMode->ShowDeletedItems=='Y' && Object::has_extension($gridField->getModelClass(), Versioned::class) && $record->getIsDeletedFromStage()) {
            Requirements::css(GRIDFIELD_DELETED_DIR.'/css/GridFieldDeletedColumns.css');
            
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