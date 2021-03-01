<?php
namespace WebbuildersGroup\GridFieldDeletedItems\Tests\GridFieldDeletedTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Control\Controller;

class DummyController extends Controller implements TestOnly
{
    private static $url_segment = 'grid-field-deleted-items';
}
