<?php
class GridFieldDeletedToggle implements GridField_ActionProvider, GridField_HTMLProvider {
    protected $targetFragment;
    
    /**
     * Constructor
     * @param string $targetFragment Target fragment to add the control to
     */
    public function __construct($targetFragment='before') {
        $this->targetFragment=$targetFragment;
    }
    
    /**
     * Returns a map where the keys are fragment names and the values are pieces of HTML to add to these fragments.
     * @param GridField $gridField Grid Field Reference
     * @return array
     */
    public function getHTMLFragments($gridField) {
        if(!Object::has_extension($gridField->getModelClass(), 'Versioned')) {
            user_error($gridField->getModelClass().' does not have the Versioned extension', E_USER_WARNING);
            
            return;
        }
        
        $button=GridField_FormAction::create($gridField, 'grid-field-toggle-deleted', _t('GridFieldDeletedToggle.INCLUDE_DELETED', 'Include Deleted'), 'gf-toggle-deleted', null)->addExtraClass('gf-toggle-deleted');
        
        
        if($gridField->State->ListDisplayMode->ShowDeletedItems=='Y') {
            $button->setAttribute('data-icon', 'ui-check-box');
            $button->setTitle(_t('GridFieldDeletedToggle.INCLUDING_DELETED', 'Including Deleted'));
        }else {
            $button->setAttribute('data-icon', 'ui-check-box-uncheck');
        }
        
        
        Requirements::css(GRIDFIELD_DELETED_DIR.'/css/GridFieldDeletedToggle.css');
        
        return array(
                    $this->targetFragment=>$button->forTemplate()
                );
    }
    
    /**
     * Return a list of the actions handled by this action provider.
     * @param GridField $gridField Grid Field Reference
     * @return array Array with action identifier strings.
     */
    public function getActions($gridField) {
        return array('gf-toggle-deleted');
    }
    
    /**
     * Handle an action on the given {@link GridField}.
     * @param GridField $gridField Grid Field Reference
     * @param string Action identifier, see {@link getActions()}.
     * @param array Arguments relevant for this
     * @param array All form data
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        if($actionName=='gf-toggle-deleted') {
            if($gridField->State->ListDisplayMode->ShowDeletedItems=='Y') {
                $gridField->State->ListDisplayMode->ShowDeletedItems='N';
            }else {
                $gridField->State->ListDisplayMode->ShowDeletedItems='Y';
            }
        }
    }
}
?>