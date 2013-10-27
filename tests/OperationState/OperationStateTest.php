<?php

namespace Sadekbaroudi\OperationState;

use Sadekbaroudi\OperationState\OperationState;
use Sadekbaroudi\OperationState\OperationStateManager;

class OperationStateTest extends \PHPUnit_Framework_TestCase {
    
    public function testKeyPersistence()
    {
        $obj = new OperationState();
        
        $key1 = $obj->getKey();
        $key2 = $obj->getKey();
        
        $this->assertEquals($key1, $key2);
        $this->assertTrue(is_string($key1), '$key1 is not a string');
        $this->assertTrue(is_string($key2), '$key2 is not a string');
    }
}
