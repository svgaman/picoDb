<?php

require 'lib/PicoDb/Database.php';
require 'lib/PicoDb/Table.php';

use PicoDb\Database;

$db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));

$db->execute('CREATE TABLE toto (bla TEXT)');

$db->table('toto')
   ->save(array('bla' => 'hey'));


print_r($db->table('toto')
           ->beginOr()
           ->equals('bla', 'hey')
           ->equals('bla', 'hy')
           ->closeOr()
           ->equals('bla', 'hey')
           ->findAll()
);


print_r($db->getLogMessages());