<?php
class GridFieldDeletedDeleteAction extends GridFieldDeleteAction {
    /**
     * Gets the content for the column, this basically says if it's deleted from the stage you can't delete it
     * @param GridField $gridField Grid Field Reference
     * @param DataObject $record Current data object being rendered
     * @param string $columnName Name of the column
     * @return string The HTML for the column
     */
    public function getColumnContent($gridField, $record, $columnName) {
        if(!SS_Object::has_extension($gridField->getModelClass(), 'Versioned')) {
            user_error($gridField->getModelClass().' does not have the Versioned extension', E_USER_WARNING);
            
            return;
        }
        
        if($gridField->State->ListDisplayMode->ShowDeletedItems=='Y' && $record->getIsDeletedFromStage()) {
            return;
        }
        
        return parent::getColumnContent($gridField, $record, $columnName);
    }
}
?>