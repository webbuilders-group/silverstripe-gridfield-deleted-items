<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Forms;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

class GridFieldDeletedRestoreButton implements GridField_ColumnProvider, GridField_ActionProvider {
    /**
     * Additional metadata about the column which can be used by other components
     * @param GridField $gridField
     * @param string $columnName
     * @return array - Map of arbitrary metadata identifiers to their values.
     */
    public function getColumnMetadata($gridField, $columnName) {
        if($columnName=='Actions') {
            return array('title'=>'');
        }
    }
    
    /**
     * Names of all columns which are affected by this component.
     * @param GridField $gridField Grid Field Reference
     * @return array
     */
    public function getColumnsHandled($gridField) {
        return array('Actions');
    }
    
    /**
     * Return a list of the actions handled by this action provider.
     * @param GridField $gridField Grid Field Reference
     * @return array Array with action identifier strings.
     */
    public function getActions($gridField) {
        return array('restore-draft-item');
    }
    
    /**
     * Handle an action on the given {@link GridField}.
     * @param GridField $gridField Grid Field Reference
     * @param string $actionName Action identifier, see {@link getActions()}.
     * @param array $arguments Arguments relevant for this
     * @param array $data All form data
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        if($actionName=='restore-draft-item') {
            if(!DataObject::has_extension($gridField->getModelClass(), Versioned::class)) {
                user_error($gridField->getModelClass().' does not have the Versioned extension', E_USER_ERROR);
                
                return;
            }
            
            $controller=$gridField->getForm()->getController();
            
            if(array_key_exists('RecordID', $arguments) && is_numeric($arguments['RecordID'])) {
                Versioned::set_stage(Versioned::DRAFT);
                
                $record=Versioned::get_latest_version($gridField->getModelClass(), intval($arguments['RecordID']));
                
                //If the record does not exist on either draft or live write to the draft
                $isDeletedFromDraft=(!$record->hasMethod('isOnDraft') ? $record->isOnLiveOnly():!$record->isOnDraft());
                if($isDeletedFromDraft) {
                    $record->writeToStage('Stage');
                    
                    if($record->hasMethod('CMSEditLink')) {
                        //Redirect to the edit screen
                        return $controller->redirect($record->CMSEditLink());
                    }else {
                        return;
                    }
                }
                
                //Resource already exists so this shouldn't have been called
                return $controller->httpError(400, _t('WebbuildersGroup\\GridFieldDeletedItems\\Forms\\GridFieldDeletedRestoreButton.ITEM_ALREADY_EXISTS', 'Item already exists on the draft site'));
            }
            
            //Record ID is missing or not a number
            return $controller->httpError(400, _t('WebbuildersGroup\\GridFieldDeletedItems\\Forms\\GridFieldDeletedRestoreButton.INVALID_ID', 'Invalid Record ID'));
        }
    }
    
    /**
     * Attributes for the element containing the content returned by {@link getColumnContent()}.
     * @param GridField $gridField Grid Field Reference
     * @param DataObject $record Current data object being rendered
     * @param string $columnName Name of the current column
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName) {
        return array('class'=>'col-buttons');
    }
    
    /**
     * Modify the list of columns displayed in the table.
     * @param GridField $gridField Grid Field Reference
     * @param array $columns List of columns
     * @param array List reference of all column names.
     */
    public function augmentColumns($gridField, &$columns) {
        if(!in_array('Actions', $columns)) {
            $columns[]='Actions';
        }
    }
    
    /**
     * HTML for the column, content of the <td> element.
     * @param GridField $gridField Grid Field Reference
     * @param DataObject $record Record displayed in this row
     * @param string $columnName Name of the current column
     * @return string HTML for the column. Return NULL to skip.
     */
    public function getColumnContent($gridField, $record, $columnName) {
        $isDeletedFromDraft=(!$record->hasMethod('isOnDraft') ? $record->isOnLiveOnly():!$record->isOnDraft());
        if($gridField->State->ListDisplayMode->ShowDeletedItems=='Y' && $isDeletedFromDraft) {
            Requirements::css('webbuilders-group/silverstripe-gridfield-deleted-items: css/GridFieldDeletedRestoreButton.css');
            
            return GridField_FormAction::create($gridField, 'restore-draft-item', false, 'restore-draft-item', array('RecordID'=>$record->ID))
                                                ->addExtraClass('btn--icon-md btn--no-text grid-field__icon-action font-icon-back-in-time restore-draft-item')
                                                ->setAttribute('title', _t('WebbuildersGroup\\GridFieldDeletedItems\\Forms\\GridFieldDeletedRestoreButton.RESTORE_DRAFT', 'Restore Draft'))
                                                ->forTemplate();
        }
    }
}
?>