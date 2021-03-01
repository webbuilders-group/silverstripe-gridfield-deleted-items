<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Tests\GridFieldDeletedTest;

use SilverStripe\Versioned\Versioned;
use WebbuildersGroup\GridFieldDeletedItems\Tests\GridFieldDeletedTest_TestObject;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class TestObject extends DataObject implements TestOnly
{
    private static $db = [
                            'Title' => 'Varchar(255)',
                        ];
    
    private static $extensions = [
                                    Versioned::class
                                ];
    
    private static $table_name = 'GridFieldDeletedTest_TestObject';
}
