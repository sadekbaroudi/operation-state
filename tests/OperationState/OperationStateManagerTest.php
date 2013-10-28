<?php

namespace Sadekbaroudi\OperationState;

use Sadekbaroudi\OperationState\OperationState;
use Sadekbaroudi\OperationState\OperationStateManager;

class OperationStateManagerTest extends \PHPUnit_Framework_TestCase {
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::add
     */
    public function testAdd()
    {
        $os = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                   ->disableOriginalConstructor()
                   ->setMethods(array('getKey'))
                   ->getMock();
        
        $testKey = 'someKey';
        
        $os->expects($this->once())->method('getKey')->will($this->returnValue($testKey));
        
        $osm = new OperationStateManager();
        $osm->add($os);
        
        $ref = new \ReflectionClass($osm);
        $refProperty = $ref->getProperty('operationQueue');
        $refProperty->setAccessible(TRUE);
        $operationQueue = $refProperty->getValue($osm);
        
        $this->assertTrue(is_array($operationQueue));
        $this->assertArrayHasKey($testKey, $operationQueue);
        $this->assertTrue(is_object($operationQueue[$testKey]));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::isInQueue
     * @depends testAdd
     */
    public function testIsInQueue()
    {
        $os = new OperationState();
        $osm = new OperationStateManager();
        $osm->add($os);
    
        $this->assertTrue($osm->isInQueue($os));
        $this->assertFalse($osm->isInQueue(new OperationState()));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::get
     * @depends testAdd
     */
    public function testGet()
    {
        $osm = new OperationStateManager();
        
        $this->assertTrue(is_array($osm->get()), "Get is not returning an array");
        
        $testKey = 'someKey';
        
        $os = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                   ->disableOriginalConstructor()
                   ->setMethods(array('getKey'))
                   ->getMock();
        
        $os->expects($this->atLeastOnce())->method('getKey')->will($this->returnValue($testKey));
        
        $osm->add($os);
        
        $this->assertTrue(is_object($osm->get($testKey)));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::get
     * @expectedException Sadekbaroudi\OperationState\OperationStateException
     */
    public function testGetException()
    {
        $osm = new OperationStateManager();
        
        $osm->get('invalidKey');
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::remove
     * @depends testAdd
     * @depends testIsInQueue
     */
    public function testRemove()
    {
        $osm = new OperationStateManager();
        
        $os = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                   ->disableOriginalConstructor()
                   ->setMethods(array('getKey'))
                   ->getMock();
        
        $os->expects($this->atLeastOnce())->method('getKey')->will($this->returnValue('someKey'));
        
        $osm->add($os);
        $osm->remove($os);
        $this->assertFalse($osm->isInQueue($os));
        
        $osm->add($os);
        $osm->remove();
        $this->assertFalse($osm->isInQueue($os));
        
        $osm->remove($os);
        $this->assertFalse($osm->isInQueue($os));
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::execute
     * @depends testIsInQueue
     * @depends testRemove
     * @depends testAdd
     */
    public function testExecute()
    {
        $osm = new OperationStateManager();
        
        $executeReturn = TRUE;
        
        $os = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                   ->disableOriginalConstructor()
                   ->setMethods(array('getKey', 'execute'))
                   ->getMock();
        
        $os->expects($this->atLeastOnce())->method('getKey')->will($this->returnValue('someKey'));
        $os->expects($this->atLeastOnce())->method('execute')->will($this->returnValue($executeReturn));
        
        $osm->add($os);
        
        $result = $osm->execute($os);
        
        $this->assertEquals($executeReturn, $result);
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::execute
     * @expectedException Sadekbaroudi\OperationState\OperationStateException
     * @depends testIsInQueue
     */
    public function testExecuteException()
    {
        $osm = new OperationStateManager();
        $osm->execute(new OperationState());
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::executeAll
     * @depends testExecute
     * @depends testAdd
     */
    public function testExecuteAll()
    {
        $osMeta = array(
            array(
               'key' => 'someKey1',
               'return' => TRUE,
            ),
            array(
                'key' => 'someKey2',
                'return' => FALSE,
            ),
        );
        
        $expectedReturn = array();
        foreach ($osMeta as $meta) {
            $expectedReturn[$meta['key']] = $meta['return'];
        }
        
        $osArray = array();
        
        foreach ($osMeta as $meta) {
            $os = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                       ->disableOriginalConstructor()
                       ->setMethods(array('getKey', 'execute'))
                       ->getMock();
            
            $os->expects($this->atLeastOnce())->method('getKey')->will($this->returnValue($meta['key']));
            $os->expects($this->atLeastOnce())->method('execute')->will($this->returnValue($meta['return']));
            $osArray[] = $os;
        }
        
        $osm = new OperationStateManager();
        
        foreach ($osArray as $os) {
            $osm->add($os);
        }
        
        $return = $osm->executeAll();
        
        $this->assertEquals($expectedReturn, $return);
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::undo
     * @depends testAdd
     */
    public function testUndo()
    {
        $undoReturn = array(TRUE);
        
        $os = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                   ->disableOriginalConstructor()
                   ->setMethods(array('undo', 'getKey'))
                   ->getMock();
        
        $os->expects($this->once())->method('undo')->will($this->returnValue($undoReturn));
        $os->expects($this->once())->method('getKey')->will($this->returnValue('someKey'));
        
        $osm = new OperationStateManager();
        $osm->add($os);
        
        $return = $osm->undo($os);
        
        $this->assertEquals($undoReturn, $return);
    }
    
    /**
     * @covers Sadekbaroudi\OperationState\OperationStateManager::undoAll
     * @depends testUndo
     * @depends testAdd
     * @depends testExecute
     * @depends testExecuteAll
     * @depends testIsInQueue
     */
    public function testUndoAll()
    {
        // Test part 1
        $osm = new OperationStateManager();
        $return = $osm->undoAll();
        $this->assertFalse($return);
        
        // Test part 2
        $osMeta = array(
            array(
                'key' => 'someKey1',
                'return' => TRUE,
            ),
            array(
                'key' => 'someKey2',
                'return' => FALSE,
            ),
        );
        
        $expectedReturn = array();
        foreach ($osMeta as $meta) {
            $expectedReturn[$meta['key']] = $meta['return'];
        }
        
        $osArray = array();
        
        foreach ($osMeta as $meta) {
            $os = $this->getMockBuilder('Sadekbaroudi\OperationState\OperationState')
                       ->disableOriginalConstructor()
                       ->setMethods(array('getKey', 'undo', 'execute'))
                       ->getMock();
        
            $os->expects($this->atLeastOnce())->method('getKey')->will($this->returnValue($meta['key']));
            $os->expects($this->atLeastOnce())->method('undo')->will($this->returnValue($meta['return']));
            $os->expects($this->atLeastOnce())->method('execute')->will($this->returnValue(TRUE));            
            $osArray[] = $os;
        }
        
        $osm = new OperationStateManager();
        
        foreach ($osArray as $os) {
            $osm->add($os);
        }
        
        $return = $osm->executeAll();
        $return = $osm->undoAll();
        
        $this->assertEquals($expectedReturn, $return);
    }
}