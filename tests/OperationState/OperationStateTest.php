<?php

namespace Sadekbaroudi\OperationState;

use Sadekbaroudi\OperationState\OperationState;
use Sadekbaroudi\OperationState\OperationStateManager;

class OperationStateTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::getExecute
     */
    public function testGetExecute()
    {
        $os = new OperationState();
        
        $this->assertTrue(is_array($os->getExecute()));
    }

    /**
     * @covers Sadekbaroudi\OperationState\OperationState::getUndo
     */
    public function testGetUndo()
    {
        $os = new OperationState();
        
        $this->assertTrue(is_array($os->getUndo()));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::setExecute
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
     * @covers Sadekbaroudi\OperationState\OperationState::getExecute
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
     * @covers Sadekbaroudi\OperationState\OperationState::clearExecute
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
     * @covers Sadekbaroudi\OperationState\OperationState::execute
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
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::setUndo
     * @dataProvider executeAndUndoProvider
     */
    public function testSetUndo($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('addUndo', 'clearUndo'))
                     ->getMock();
    
        $mock->expects($this->once())->method('clearUndo');
        $mock->expects($this->once())->method('addUndo');
    
        $ret = $mock->setUndo($object, $method, $arguments);
    
        $this->assertEquals(get_class($mock), get_class($ret), "Return value for setUndo is not a class that matches the mock");
    }

    /**
     * @covers Sadekbaroudi\OperationState\OperationState::addUndo
     * @depends testGetUndo
     * @dataProvider executeAndUndoProvider
     */
    public function testAddUndo($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setUndo'))
                     ->getMock();
    
        $ret = $mock->addUndo($object, $method, $arguments);
    
        $this->assertEquals(get_class($mock), get_class($ret), "Return value for addUndo is not a class that matches the mock");
    
        $this->assertEquals($mock->getUndo(), array(array('object' => $object, 'method' => $method, 'arguments' => $arguments)));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::clearUndo
     * @depends testAddUndo
     * @depends testGetUndo
     * @dataProvider executeAndUndoProvider
     */
    public function testClearUndo($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
        ->setMethods(array('setUndo'))
        ->getMock();
    
        $mock->addUndo($object, $method, $arguments);
    
        $results = $mock->getUndo();
        $this->assertNotEmpty($results);
    
        $os = new \ReflectionClass('Sadekbaroudi\OperationState\OperationState');
        $refMethod = $os->getMethod('clearUndo');
        $refMethod->setAccessible(TRUE);
        $refMethod->invoke($mock);
    
        $results = $mock->getUndo();
    
        $this->assertEmpty($results);
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::undo
     * @depends testAddUndo
     * @dataProvider executeAndUndoProvider
     */
    public function testUndo($object, $method, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
        ->setMethods(array('setUndo', 'run'))
        ->getMock();
    
        $mock->addUndo($object, $method, $arguments);
    
        $mock->expects($this->once())->method('run')->will($this->returnValue(10));
        
        $return = $mock->undo($object, $method, $arguments);
        
        $this->assertTrue(is_array($return));
        $this->assertNotEmpty($return);
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::run
     * @dataProvider runGoodProvider
     * @param array $params
     */
    public function testGoodRun($params)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute'))
                     ->getMock();
        
        $os = new \ReflectionClass('Sadekbaroudi\OperationState\OperationState');
        $refMethod = $os->getMethod('run');
        $refMethod->setAccessible(TRUE);
        $results = $refMethod->invokeArgs($mock, array($params));
        
        $this->assertNotEmpty($results);
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::run
     * @dataProvider runBadProvider
     * @expectedException Sadekbaroudi\OperationState\OperationStateException
     * @param array $params
     */
    public function testBadRun($params)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute'))
                     ->getMock();
        
        $os = new \ReflectionClass('Sadekbaroudi\OperationState\OperationState');
        $refMethod = $os->getMethod('run');
        $refMethod->setAccessible(TRUE);
        $results = $refMethod->invokeArgs($mock, array($params));
    
        $this->assertNotEmpty($results);
    }
    
    /**
     * Note that all operations below should return non-empty results. To test bad runs, use the runBadProvider below
     */
    public function executeAndUndoProvider()
    {
        return array(
        	array(new OperationState(), 'getKey', array()),
            array(NULL, 'is_array', array(array())),
            array(NULL, 'md5', array('test'))
        );
    }
    
    public function runGoodProvider()
    {
        $updatedDataset = array();
        
        foreach($this->executeAndUndoProvider() as $dataset)
        {
            $updatedDataset[] = array(array('object' => $dataset[0], 'method' => $dataset[1], 'arguments' => $dataset[2]));
        }
        
        return $updatedDataset;
    }
    
    public function runBadProvider()
    {
        return array(
            array(
            	array(
            	   'object' => new OperationState(),
            	   'method' => 'bogusMethod',
            	   'arguments' => array(),
                ),
            ),
            array(
                array(
                    'object' => NULL,
                    'method' => 'bogusMethod',
                    'arguments' => array(),
                ),
            ),
            array(
                array(
                    'object' => 'thisStringShouldBeAnObject',
                    'method' => 'bogusMethod',
                    'arguments' => array(),
                ),
            ),
        );
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::getKey
     */
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
