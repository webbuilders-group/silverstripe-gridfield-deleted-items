<?php
class GridFieldDeletedTest extends FunctionalTest {
    protected static $fixture_file='GridFieldDeletedTest.yml';

	protected $extraDataObjects=array('GridFieldDeletedTest_TestObject');
    
    protected $list;
    protected $gridField;
    protected $form;
    
    public function setUp() {
        parent::setUp();
        
        $this->list=GridFieldDeletedTest_TestObject::get();
        $config=GridFieldConfig_RecordEditor::create(10)
                                                        ->removeComponentsByType('GridFieldDataColumns')
                                                        ->removeComponentsByType('GridFieldEditButton')
                                                        ->addComponent(new GridFieldDeletedManipulator(), 'GridFieldToolbarHeader')
                                                        ->addComponent(new GridFieldDeletedColumns())
                                                        ->addComponent(new GridFieldDeletedEditButton())
                                                        ->addComponent(new GridFieldDeletedRestoreButton())
                                                        ->addComponent(new GridFieldDeletedToggle('buttons-before-left'));
        
        $this->gridField=new GridField('testfield', 'testfield', $this->list, $config);
        $this->form=new Form(new Controller(), 'mockform', new FieldList(array($this->gridField)), new FieldList());
    }
    
    /**
     * Tests toggling the list on and off
     */
    public function testToggleShowDeleted() {
        $deletedIDs=array();
        $list=GridFieldDeletedTest_TestObject::get();
        foreach($list as $item) {
            if($item->ID%2==0) {
                $deletedIDs[]=$item->ID;
                $item->delete();
            }
        }
        
        
        //In the default state the list should not contain the deleted items
        $this->assertEquals(0, $this->gridField->getManipulatedList()->filter('ID', $deletedIDs)->count(), 'Deleted items are visible in the list and they should not be in the default state');
        
        
        //Toggle the show deleted on
        $stateID='testGridStateActionField';
        Session::set($stateID, array('grid'=>'', 'actionName'=>'gf-toggle-deleted', 'args'=>array('ListDisplayMode'=>array('ShowDeletedItems'=>'Y'))));
        $request=new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $this->form->getSecurityToken()->getName()=>$this->form->getSecurityToken()->getValue()));
        $this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
        
        
        //Check to see if the deleted items are now visible
        $this->assertGreaterThan(0, $this->gridField->getManipulatedList()->filter('ID', $deletedIDs)->count(), 'Deleted items are not visible in the list and they should be when the toggle is on');
        
        
        //Toggle the show deleted back off
        $stateID='testGridStateActionField';
        Session::set($stateID, array('grid'=>'', 'actionName'=>'gf-toggle-deleted', 'args'=>array('ListDisplayMode'=>array('ShowDeletedItems'=>'N'))));
        $request=new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $this->form->getSecurityToken()->getName()=>$this->form->getSecurityToken()->getValue()));
        $this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
        
        
        //Check to see if the deleted items are now visible
        $this->assertEquals(0, $this->gridField->getManipulatedList()->filter('ID', $deletedIDs)->count(), 'Deleted items are visible in the list and they should be when the toggle is off');
    }
    
    /**
     * Tests restoring a deleted item
     */
    public function testRestoreDeleted() {
        //Load the item to delete and capture it's id then delete it
        $deletedItem=$this->objFromFixture('GridFieldDeletedTest_TestObject', 'testobj2');
        $deletedItemID=$deletedItem->ID;
        $deletedItem->delete();
        
        
        //Make sure the item was deleted
        $this->assertNull(Versioned::get_one_by_stage('GridFieldDeletedTest_TestObject', 'Stage', '"ID"='.$deletedItemID), 'Item was not deleted prior to restoring');
        
        
        //Attempt to restore the item
        $stateID='testGridStateActionField';
        Session::set($stateID, array('grid'=>'', 'actionName'=>'restore-draft-item', 'args'=>array('RecordID'=>$deletedItemID)));
        $request=new SS_HTTPRequest('POST', 'url', array(), array('action_gridFieldAlterAction?StateID='.$stateID=>true, $this->form->getSecurityToken()->getName()=>$this->form->getSecurityToken()->getValue()));
        $this->gridField->gridFieldAlterAction(array('StateID'=>$stateID), $this->form, $request);
        
        
        //Check to see if the item exists again
        $item=Versioned::get_one_by_stage('GridFieldDeletedTest_TestObject', 'Stage', '"Title"=\'Test Object 2\'');
        $this->assertInstanceOf('GridFieldDeletedTest_TestObject', $item, 'Could not find the item after restoring');
        $this->assertTrue($item->exists(), 'Could not find the item after restoring');
    }
    
    /**
     * Tests if the edit button is removed for deleted items or not
     */
    public function testDeletedNoEdit() {
        //Load the item to delete and capture it's id then delete it
        $deletedItem=$this->objFromFixture('GridFieldDeletedTest_TestObject', 'testobj2');
        $deletedItem->delete();
        
        
        //Enable the deleted items
        $this->gridField->getState()->ListDisplayMode->ShowDeletedItems='Y';
        
        
        //Get the attributes for the deleted item's title column
        $attributes=$this->gridField->getConfig()->getComponentByType('GridFieldDeletedColumns')->getColumnAttributes($this->gridField, $deletedItem, 'Title');
        
        
        //Verify we have an array and the class attribute exists
        $this->assertInternalType('array', $attributes);
        $this->assertArrayHasKey('class', $attributes);
        
        
        //Verify that the deleted-record class is applied
        $classes=explode(' ', $attributes['class']);
        $this->assertContains('deleted-record', $classes, 'Item was deleted but could not find the deleted-record class');
        
        
        //Get the attributes for a non-deleted item
        $attributes=$this->gridField->getConfig()->getComponentByType('GridFieldDeletedColumns')->getColumnAttributes($this->gridField, $this->objFromFixture('GridFieldDeletedTest_TestObject', 'testobj1'), 'Title');
        
        
        //Verify we have an array and the class attribute exists
        $this->assertInternalType('array', $attributes);
        $this->assertArrayHasKey('class', $attributes);
        
        
        //Verify that the deleted-record class is not applied
        $classes=explode(' ', $attributes['class']);
        $this->assertNotContains('deleted-record', $classes, 'Item was not deleted but the deleted-record class was found');
    }
}

class GridFieldDeletedTest_TestObject extends DataObject implements TestOnly {
    private static $db=array(
                            'Title'=>'Varchar(255)'
                        );
    
    private static $extensions=array(
                                    'Versioned'
                                );
    
    
    /**
     * Compares current draft with live version, and returns TRUE if no draft version of this page exists, but the page is still published (after triggering "Delete from draft site" in the CMS).
     * @return bool
     */
    public function getIsDeletedFromStage() {
        if(!$this->ID) {
            return true;
        }
        
        if(!$this->exists()) {
            return false;
        }
        
        $stageVersion=Versioned::get_versionnumber_by_stage($this->class, 'Stage', $this->ID);
        
        // Return true for both completely deleted pages and for pages just deleted from stage.
        return !($stageVersion);
    }
    
    /**
     * Return true if this page exists on the live site
     */
    public function getExistsOnLive() {
        return (bool)Versioned::get_versionnumber_by_stage($this->class, 'Live', $this->ID);
    }
}
?>