<?php

namespace WebbuildersGroup\GridFieldDeletedItems\Forms;




use Object;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\SS_List;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\GridField\GridField_DataManipulator;


class GridFieldDeletedManipulator implements GridField_DataManipulator {
    /**
     * Manipulate the {@link DataList} as needed by this grid modifier.
     * @param GridField $gridField Grid Field Reference
     * @param SS_List
     * @return DataList
     */
    public function getManipulatedData(GridField $gridField, SS_List $dataList) {
        if($gridField->State->ListDisplayMode->ShowDeletedItems=='Y') {
            if(!Object::has_extension($gridField->getModelClass(), Versioned::class)) {
                user_error($gridField->getModelClass().' does not have the Versioned extension', E_USER_WARNING);
            
                return;
            }
            
            $dataList=$dataList->setDataQueryParam(array(
                                                        'Versioned.mode'=>'latest_versions'
                                                    ));
        }
    
        return $dataList;
    }
}
?>