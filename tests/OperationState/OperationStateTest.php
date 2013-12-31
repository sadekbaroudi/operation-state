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
    public function testSetExecute($callable, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('addExecute', 'clearExecute'))
                     ->getMock();
    
        $mock->expects($this->once())->method('clearExecute');
        $mock->expects($this->once())->method('addExecute');
    
        $ret = $mock->setExecute($callable, $arguments);
    
        $this->assertEquals(get_class($mock), get_class($ret), "Return value is not a class that matches the mock");
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
     * @covers Sadekbaroudi\OperationState\OperationState::addExecute
     * @depends testGetExecute
     * @dataProvider executeAndUndoProvider
     */
    public function testAddExecute($callable, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute'))
                     ->getMock();
        
        $ret = $mock->addExecute($callable, $arguments);
        
        $this->assertEquals(get_class($mock), get_class($ret), "Return value is not a class that matches the mock");
        
        $this->assertEquals($mock->getExecute(), array(array('callable' => $callable, 'arguments' => $arguments)));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::clearExecute
     * @depends testAddExecute
     * @depends testGetExecute
     * @dataProvider executeAndUndoProvider
     */
    public function testClearExecute($callable, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute'))
                     ->getMock();
        
        $mock->addExecute($callable, $arguments);

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
     * @depends testGoodRun
     * @dataProvider testExecuteProvider
     */
    public function testExecute($data, $expected)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setExecute'))
                     ->getMock();
        
        foreach ($data as $executeAction) {
            $mock->addExecute($executeAction[0], $executeAction[1]);
        }
        
        $return = $mock->execute();
        
        $this->assertEquals($expected, $return, "execute call did not return the expected set of data!");
        $this->assertTrue(is_array($return));
        $this->assertNotEmpty($return);
    }
    
    public function testExecuteProvider()
    {
        return array(
        	array(
        	    array(
                    array(array(new \ArrayIterator(array()), 'count'), OperationState::NO_ARGUMENT),
            	    array('md5', 'testmd5'),
        	    ),
                array(
        	        0,
                    md5('testmd5'),
                ),
            ),
            array(
                array(
                    array('count', array('onevalue')),
                    array('strtolower', 'WOAH'),
                ),
                array(
                    1,
                    'woah'
                ),
            ),
        );
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::setUndo
     * @dataProvider executeAndUndoProvider
     */
    public function testSetUndo($callable, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('addUndo', 'clearUndo'))
                     ->getMock();
    
        $mock->expects($this->once())->method('clearUndo');
        $mock->expects($this->once())->method('addUndo');
    
        $ret = $mock->setUndo($callable, $arguments);
    
        $this->assertEquals(get_class($mock), get_class($ret), "Return value for setUndo is not a class that matches the mock");
    }

    /**
     * @covers Sadekbaroudi\OperationState\OperationState::addUndo
     * @depends testGetUndo
     * @dataProvider executeAndUndoProvider
     */
    public function testAddUndo($callable, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setUndo'))
                     ->getMock();
    
        $ret = $mock->addUndo($callable, $arguments);
    
        $this->assertEquals(get_class($mock), get_class($ret), "Return value for addUndo is not a class that matches the mock");
    
        $this->assertEquals($mock->getUndo(), array(array('callable' => $callable, 'arguments' => $arguments)));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationState::clearUndo
     * @depends testAddUndo
     * @depends testGetUndo
     * @dataProvider executeAndUndoProvider
     */
    public function testClearUndo($callable, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
        ->setMethods(array('setUndo'))
        ->getMock();
    
        $mock->addUndo($callable, $arguments);
    
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
    public function testUndo($callable, $arguments)
    {
        $mock = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                     ->setMethods(array('setUndo', 'run'))
                     ->getMock();
    
        $mock->addUndo($callable, $arguments);
    
        $mock->expects($this->once())->method('run')->will($this->returnValue(10));
        
        $return = $mock->undo();
        
        $this->assertTrue(is_array($return));
        $this->assertNotEmpty($return);
    }
    
    /**
     * Note that all operations below should return non-empty results. To test bad runs, use the runBadProvider below
     */
    public function executeAndUndoProvider()
    {
        return array(
            array(array(new OperationState(), 'getKey'), array()),
            array(array(new OperationState(), 'getKey'), OperationState::NO_ARGUMENT),
            array('is_array', array()),
            array('md5', 'test'),
        );
    }
    
    public function runGoodProvider()
    {
        $updatedDataset = array();
        
        foreach($this->executeAndUndoProvider() as $dataset)
        {
            $updatedDataset[] = array(array('callable' => $dataset[0], 'arguments' => $dataset[1]));
        }
        
        return $updatedDataset;
    }
    
    public function runBadProvider()
    {
        return array(
            array(
            	array(
            	   'callable' => array(new OperationState(), 'bogusMethod'),
            	   'arguments' => array(),
                ),
            ),
            array(
                array(
                    'callable' => 'bogusMethod',
                    'arguments' => array(),
                ),
            ),
            array(
                array(
                    'callable' => array('thisStringShouldBeAnObject', 'bogusMethod'),
                    'arguments' => array(),
                ),
            ),
            array(
                array(
                    'callable' => array('thisStringShouldBeAnObject', 'bogusMethod'),
                ),
            ),
            array(
                array(
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
