<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Tests;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedColumns;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedEditButton;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedDeleteAction;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedManipulator;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedRestoreButton;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedToggle;

class GridFieldDeletedTest extends FunctionalTest
{
    protected static $fixture_file = 'GridFieldDeletedTest.yml';
    protected static $extra_dataobjects = [GridFieldDeletedTest\TestObject::class];
    
    protected $list;
    protected $gridField;
    protected $form;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->list = GridFieldDeletedTest\TestObject::get();
        $config = GridFieldConfig_RecordEditor::create(10)
                                                        ->removeComponentsByType(GridFieldDataColumns::class)
                                                        ->removeComponentsByType(GridFieldEditButton::class)
                                                        ->addComponent(new GridFieldDeletedManipulator(), GridFieldToolbarHeader::class)
                                                        ->addComponent(new GridFieldDeletedColumns())
                                                        ->addComponent(new GridFieldDeletedEditButton())
                                                        ->addComponent(new GridFieldDeletedRestoreButton())
                                                        ->addComponent(new GridFieldDeletedToggle('buttons-before-left'));
        
        $this->gridField = new GridField('testfield', 'testfield', $this->list, $config);
        $this->form = new Form(new GridFieldDeletedTest\DummyController(), 'mockform', new FieldList([$this->gridField]), new FieldList());
    }
    
    /**
     * Tests toggling the list on and off
     */
    public function testToggleShowDeleted()
    {
        $deletedIDs = [];
        $list = GridFieldDeletedTest\TestObject::get();
        foreach ($list as $item) {
            if ($item->ID % 2 == 0) {
                $deletedIDs[] = $item->ID;
                $item->delete();
            }
        }
        
        
        //In the default state the list should not contain the deleted items
        $this->assertEquals(0, $this->gridField->getManipulatedList()->filter('ID', $deletedIDs)->count(), 'Deleted items are visible in the list and they should not be in the default state');
        
        
        //Toggle the show deleted on
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Injector::inst()->create(Session::class, []);
        $request->setSession($session);
        $session->init($request);
        $request->getSession()->set($stateID, ['grid' => '', 'actionName' => 'gf-toggle-deleted', 'args' => ['ListDisplayMode' => ['ShowDeletedItems' => 'Y']]]);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);
        
        
        //Check to see if the deleted items are now visible
        $this->assertGreaterThan(0, $this->gridField->getManipulatedList()->filter('ID', $deletedIDs)->count(), 'Deleted items are not visible in the list and they should be when the toggle is on');
        
        
        //Toggle the show deleted back off
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Injector::inst()->create(Session::class, []);
        $request->setSession($session);
        $session->init($request);
        $request->getSession()->set($stateID, ['grid' => '', 'actionName' => 'gf-toggle-deleted', 'args' => ['ListDisplayMode' => ['ShowDeletedItems' => 'N']]]);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);
        
        
        //Check to see if the deleted items are now visible
        $this->assertEquals(0, $this->gridField->getManipulatedList()->filter('ID', $deletedIDs)->count(), 'Deleted items are visible in the list and they should be when the toggle is off');
    }
    
    /**
     * Tests restoring a deleted item
     */
    public function testRestoreDeleted()
    {
        //Load the item to delete and capture it's id then delete it
        $deletedItem = $this->objFromFixture(GridFieldDeletedTest\TestObject::class, 'testobj2');
        $deletedItemID = $deletedItem->ID;
        $deletedItem->delete();
        
        
        //Make sure the item was deleted
        $this->assertNull(Versioned::get_one_by_stage(GridFieldDeletedTest\TestObject::class, 'Stage', '"ID"=' . $deletedItemID), 'Item was not deleted prior to restoring');
        
        
        //Attempt to restore the item
        $stateID = 'testGridStateActionField';
        $request = new HTTPRequest('POST', 'url', [], ['action_gridFieldAlterAction?StateID=' . $stateID => true, $this->form->getSecurityToken()->getName() => $this->form->getSecurityToken()->getValue()]);
        $session = Injector::inst()->create(Session::class, []);
        $request->setSession($session);
        $session->init($request);
        $request->getSession()->set($stateID, ['grid' => '', 'actionName' => 'restore-draft-item', 'args' => ['RecordID' => $deletedItemID]]);
        $this->gridField->gridFieldAlterAction(['StateID' => $stateID], $this->form, $request);
        
        
        //Check to see if the item exists again
        $item = Versioned::get_one_by_stage(GridFieldDeletedTest\TestObject::class, 'Stage', '"Title"=\'Test Object 2\'');
        $this->assertInstanceOf(GridFieldDeletedTest\TestObject::class, $item, 'Could not find the item after restoring');
        $this->assertTrue($item->exists(), 'Could not find the item after restoring');
    }
    
    /**
     * Tests if the edit button is removed for deleted items or not
     */
    public function testDeletedNoEdit()
    {
        //Load the item to delete and capture it's id then delete it
        $deletedItem = $this->objFromFixture(GridFieldDeletedTest\TestObject::class, 'testobj2');
        $deletedItem->delete();
        
        
        //Enable the deleted items
        $this->gridField->getState()->ListDisplayMode->ShowDeletedItems = 'Y';
        
        
        //Get the attributes for the deleted item's title column
        $attributes = $this->gridField->getConfig()->getComponentByType(GridFieldDeletedColumns::class)->getColumnAttributes($this->gridField, $deletedItem, 'Title');
        
        
        //Verify we have an array and the class attribute exists
        $this->assertInternalType('array', $attributes);
        $this->assertArrayHasKey('class', $attributes);
        
        
        //Verify that the deleted-record class is applied
        $classes = explode(' ', $attributes['class']);
        $this->assertContains('deleted-record', $classes, 'Item was deleted but could not find the deleted-record class');
        
        
        //Get the attributes for a non-deleted item
        $attributes = $this->gridField->getConfig()->getComponentByType(GridFieldDeletedColumns::class)->getColumnAttributes($this->gridField, $this->objFromFixture(GridFieldDeletedTest\TestObject::class, 'testobj1'), 'Title');
        
        
        //Verify we have an array and the class attribute exists
        $this->assertInternalType('array', $attributes);
        $this->assertArrayHasKey('class', $attributes);
        
        
        //Verify that the deleted-record class is not applied
        $classes = explode(' ', $attributes['class']);
        $this->assertNotContains('deleted-record', $classes, 'Item was not deleted but the deleted-record class was found');
    }
}
