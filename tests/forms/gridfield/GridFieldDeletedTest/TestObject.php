<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Tests\GridFieldDeletedTest;

use SilverStripe\Versioned\Versioned;
use WebbuildersGroup\GridFieldDeletedItems\Tests\GridFieldDeletedTest_TestObject;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class TestObject extends DataObject implements TestOnly {
    private static $db=array(
                            'Title'=>'Varchar(255)'
                        );
    
    private static $extensions=array(
                                    Versioned::class
                                );
    
    private static $table_name='GridFieldDeletedTest_TestObject';
    
    
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
        
        $stageVersion=Versioned::get_versionnumber_by_stage(self::class, 'Stage', $this->ID);
        
        // Return true for both completely deleted pages and for pages just deleted from stage.
        return !($stageVersion);
    }
    
    /**
     * Return true if this page exists on the live site
     */
    public function getExistsOnLive() {
        return (bool)Versioned::get_versionnumber_by_stage(self::class, 'Live', $this->ID);
    }
}
?>