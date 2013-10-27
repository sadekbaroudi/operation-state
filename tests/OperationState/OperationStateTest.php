<?php

namespace Sadekbaroudi\OperationState;

use Sadekbaroudi\OperationState\OperationState;
use Sadekbaroudi\OperationState\OperationStateManager;

class OperationStateTest extends \PHPUnit_Framework_TestCase {
    
    public function testGetExecute()
    {
        $os = new OperationState();
        
        $this->assertTrue(is_array($os->getExecute()));
    }

    public function testGetUndo()
    {
        $os = new OperationState();
        
        $this->assertTrue(is_array($os->getUndo()));
    }
    
    /**
     * @dataProvider executeAndUndoProvider
     */
    public function testSetExecute($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('addExecute', 'clearExecute'))
                     ->getMock();
    
        $mock->expects($this->once())->method('clearExecute');
        $mock->expects($this->once())->method('addExecute');
    
        $ret = $mock->setExecute($object, $method, $arguments);
    
        $this->assertEquals(get_class($mock), get_class($ret), "Return value is not a class that matches the mock");
    }
    
    /**
     * @depends testGetExecute
     * @dataProvider executeAndUndoProvider
     */
    public function testAddExecute($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute'))
                     ->getMock();
        
        $ret = $mock->addExecute($object, $method, $arguments);
        
        $this->assertEquals(get_class($mock), get_class($ret), "Return value is not a class that matches the mock");
        
        $this->assertEquals($mock->getExecute(), array(array('object' => $object, 'method' => $method, 'arguments' => $arguments)));
    }
    
    /**
     * @depends testAddExecute
     * @depends testGetExecute
     * @dataProvider executeAndUndoProvider
     */
    public function testClearExecute($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute'))
                     ->getMock();
        
        $mock->addExecute($object, $method, $arguments);

        $results = $mock->getExecute();
        $this->assertNotEmpty($results);
        
        $os = new \ReflectionClass('Sadekbaroudi\OperationState\OperationState');
        $refMethod = $os->getMethod('clearExecute');
        $refMethod->setAccessible(TRUE);
        $refMethod->invoke($mock);
        
        $results = $mock->getExecute();
        
        $this->assertEmpty($results);
    }

    /**
     * @depends testAddExecute
     * @dataProvider executeAndUndoProvider
     */
    public function testExecute($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute', 'run'))
                     ->getMock();
        
        $mock->addExecute($object, $method, $arguments);
        
        $mock->expects($this->once())->method('run')->will($this->returnValue(10));
        
        $return = $mock->execute($object, $method, $arguments);
        
        $this->assertTrue(is_array($return));
        $this->assertNotEmpty($return);
    }
    
    //TODO: Continue unit tests here (I'm at OperationState->setUndo())
    
    public function executeAndUndoProvider()
    {
        return array(
        	array(new OperationState(), 'getKey', array()),
            array(NULL, 'is_array', array(array())),
            array(NULL, 'md5', array('test'))
        );
    }
    
    public function testKeyPersistence()
    {
        $obj = new OperationState();
        $key1 = $obj->getKey();
        $key2 = $obj->getKey();
    
        $this->assertEquals($key1, $key2, '$key1 should equal $key2, since they come from the same object');
        $this->assertTrue(is_string($key1), '$key1 is not a string');
        $this->assertTrue(is_string($key2), '$key2 is not a string');
    
        $obj2 = new OperationState();
        $key3 = $obj2->getKey();
    
        $this->assertNotEquals($key1, $key3, '$key1 should not equal $key3, since they come from different objects');
    }
}
