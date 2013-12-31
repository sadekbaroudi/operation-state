<?php

namespace Sadekbaroudi\OperationState;

use Sadekbaroudi\OperationState\OperationStateException;

/**
 * This class defines an Operation State. You set actions to execute, and all the
 * appropriate undo actions for those execute actions. You can then use the OperationStateManager
 * class to keep track of what has been executed, and what needs to be undone.
 * 
 * @author sadekbaroudi
 */
class OperationState {
    
    const NO_ARGUMENT = 'SorryYouCanNeverUseThisStringAsAnArgument';
    
    /**
     * This is the generated key that persists throughout the existence of this object
     * @var string
     */
    protected $key;
    
    /**
     * @var array An array of arrays, containing the actions to be executed
     */
    protected $executeParameters = array();
    
    /**
     * @var array An array of arrays, containing the actions to undo the executed actions
     */
    protected $undoParameters = array();
    
    /**
     * Getter for the executeParameters
     * @return array
     */
    public function getExecute()
    {
        return $this->executeParameters;
    }
    
    /**
     * Getter for the undoParameters
     * @return array
     */
    public function getUndo()
    {
        return $this->undoParameters;
    }
    
    /**
     * This will clear the current set of execute actions, and add the passed action
     * 
     * @param mixed $callable php is_callable compliant call
     * @param array $arguments php is_callable compliant arguments, or OperationState::NO_ARGUMENT if none
     * @return \Sadekbaroudi\OperationState\OperationState
     */
    public function setExecute($callable, $arguments)
    {
        $this->clearExecute();
    
        $this->addExecute($callable, $arguments);
    
        return $this;
    }
    
    /**
     * This will add the passed action to the list of execute actions to be called
     * 
     * @param mixed $callable php is_callable compliant call
     * @param array $arguments php is_callable compliant arguments, or OperationState::NO_ARGUMENT if none
     * @return \Sadekbaroudi\OperationState\OperationState
     */
    public function addExecute($callable, $arguments)
    {
        $this->executeParameters[] = array(
        	'callable' => $callable,
            'arguments' => $arguments,
        );
        
        return $this;
    }
    
    /**
     * This will clear all the execute actions from the queue
     */
    protected function clearExecute()
    {
        $this->executeParameters = array();
    }
    
    /**
     * This will execute all the actions in the queue. It will not clear the queue!
     * 
     * @return array returns the results of the executed actions, with OperationState->getKey() as the array keys
     */
    public function execute()
    {
        $returnValues = array();
        
        while ($execute = array_shift($this->executeParameters)) {
            $returnValues[] = $this->run($execute);
        }
        
        return $returnValues;
    }
    
    /**
     * This will clear the current set of undo actions, and add the passed action
     *
     * @param mixed $callable php is_callable compliant call
     * @param array $arguments php is_callable compliant arguments, or OperationState::NO_ARGUMENT if none
     * @return \Sadekbaroudi\OperationState\OperationState
     */
    public function setUndo($callable, $arguments)
    {
        $this->clearUndo();
    
        $this->addUndo($callable, $arguments);
    
        return $this;
    }
    
    /**
     * This will add the passed action to the list of undo actions to be called
     *
     * @param mixed $callable php is_callable compliant call
     * @param array $arguments php is_callable compliant arguments, or OperationState::NO_ARGUMENT if none
     * @return \Sadekbaroudi\OperationState\OperationState
     */
    public function addUndo($callable, $arguments)
    {
        $this->undoParameters[] = array(
        	'callable' => $callable,
            'arguments' => $arguments,
        );
        
        return $this;
    }
    
    /**
     * This will clear all the execute actions from the queue
     */
    protected function clearUndo()
    {
        $this->undoParameters = array();
    }
    
    /**
     * This will execute all the undo actions in the queue. It will not clear the queue!
     *
     * @return array returns the results of the undo actions, with OperationState->getKey() as the array keys
     */
    public function undo()
    {
        $returnValues = array();
        while ($undo = array_shift($this->undoParameters)) {
            $returnValues[] = $this->run($undo);
        }
        
        return $returnValues;
    }
    
    /**
     * This will run the action passed, whether execute or undo.
     * 
     * @param array $call Contains 'callable' index supporting is_callable and 'arguments' (NULL or args).
     *                    You can also pass OperationState::NO_ARGUMENT as an argument to call with no argument
     * @throws \RuntimeException
     * @return mixed returns the result of the method call
     */
    protected function run($call)
    {
        if (!isset($call['callable'])) {
            throw new OperationStateException("\$call['callable'] was not set");
        }
        
        if (!isset($call['arguments'])) {
            try {
                is_null($call['arguments']);
            } catch (\Exception $e) {
                throw new OperationStateException("\$call['arguments'] was not set");
            }
        }
        
        if (is_callable($call['callable'])) {
            if ($call['arguments'] == self::NO_ARGUMENT) {
                return call_user_func($call['callable']);
            } else {
                return call_user_func($call['callable'], $call['arguments']);
            }
        } else {
            throw new OperationStateException("\$call and \$arguments passed did not pass is_callable() check");
        }
    }
    
    /**
     * Get the a unique key for this object. It should return the same value regardless of when this function is called!
     * 
     * @return string
     */
    public function getKey()
    {
        if (!isset($this->key)) {
            $this->key = md5(microtime().rand());
        }
        return $this->key;
    }
}
