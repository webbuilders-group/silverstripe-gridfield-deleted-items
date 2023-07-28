<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Tests\GridFieldDeletedTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

class TestObject extends DataObject implements TestOnly
{
    private static $db = [
        'Title' => 'Varchar(255)',
    ];

    private static $extensions = [
        Versioned::class,
    ];

    private static $table_name = 'GridFieldDeletedTest_TestObject';
}
