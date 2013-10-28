operation-state [![Build Status](https://travis-ci.org/sadekbaroudi/operation-state.png?branch=master)](https://travis-ci.org/sadekbaroudi/operation-state) [![Coverage Status](https://coveralls.io/repos/sadekbaroudi/operation-state/badge.png)](https://coveralls.io/r/sadekbaroudi/operation-state) [![Dependency Status](https://www.versioneye.com/user/projects/526df623632bac11d5000063/badge.png)](https://www.versioneye.com/user/projects/526df623632bac11d5000063)
===============

Simple PHP classes to handle Operation States for executable actions, with the
ability to undo. In other words, helper for transactions and rollbacks within PHP.

Its purpose is to provide a reliable method for executing actions, and being able to
undo those actions when applicable. The functionality will allow you create multiple
groups of executable actions and their respective undo actions.

Usage
=====

```php
use Sadekbaroudi\OperationState\OperationStateManager;
use Sadekbaroudi\OperationState\OperationState;

$yourClass = new YourClassName();

// Instantiate the manager
$osm = new OperationStateManager();

$os = new OperationState();
$os->setExecute($yourClass, 'yourMethod', array('param1', $param2, array('foo' => 'bar')));
$os->setUndo($yourClass, 'undoMethod', array('param1'));
$osm->add($os);

try {
    $osm->execute($os);
} catch ( OperationStateException $e ) {
    $osm->undo($os);
    throw $e;
}
```

Installation
============

Operation State can be installed with [Composer](http://getcomposer.org) by adding
the library as a dependency to your composer.json file.

```json
{
    "require": {
        "sadekbaroudi/operation-state": "*@dev"
    }
}
```

Please refer to the [Composer's documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction)
for installation and usage instructions.
