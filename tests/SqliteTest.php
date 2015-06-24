<?php

require 'vendor/autoload.php';

use PicoDb\Database;
use PicoDb\Table;

class SqliteTest extends PHPUnit_Framework_TestCase
{
    public function testEscapeIdentifer()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('"a"', $db->escapeIdentifier('a'));
        $this->assertEquals('a.b', $db->escapeIdentifier('a.b'));
        $this->assertEquals('"c"."a"', $db->escapeIdentifier('a', 'c'));
        $this->assertEquals('a.b', $db->escapeIdentifier('a.b', 'c'));
        $this->assertEquals('SELECT COUNT(*) FROM test', $db->escapeIdentifier('SELECT COUNT(*) FROM test'));
        $this->assertEquals('SELECT COUNT(*) FROM test', $db->escapeIdentifier('SELECT COUNT(*) FROM test', 'b'));
    }

    public function testEscapeIdentiferList()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals(array('"c"."a"', '"c"."b"'), $db->escapeIdentifierList(array('a', 'b'), 'c'));
        $this->assertEquals(array('"a"', 'd.b'), $db->escapeIdentifierList(array('a', 'd.b')));
    }

    public function testSelect()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT 1 FROM "test"', $db->table('test')->select(1)->buildSelectQuery());
    }

    public function testColumns()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT "test"."a", "test"."b" FROM "test"', $db->table('test')->columns('a', 'b')->buildSelectQuery());
    }

    public function testDistinct()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT DISTINCT "test"."a", "test"."b" FROM "test"', $db->table('test')->distinct('a', 'b')->buildSelectQuery());
    }

    public function testGroupBy()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT * FROM "test"   GROUP BY "test"."a"', $db->table('test')->groupBy('a')->buildSelectQuery());
    }

    public function testOrderBy()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));

        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" ASC', $db->table('test')->asc('a')->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" ASC', $db->table('test')->orderBy('a', Table::SORT_ASC)->buildSelectQuery());

        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" DESC', $db->table('test')->desc('a', Table::SORT_DESC)->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" DESC', $db->table('test')->orderBy('a', Table::SORT_DESC)->buildSelectQuery());

        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" ASC, "test"."b" ASC', $db->table('test')->asc('a')->asc('b')->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" DESC, "test"."b" DESC', $db->table('test')->desc('a')->desc('b')->buildSelectQuery());

        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" ASC, "test"."b" ASC', $db->table('test')->orderBy('a')->orderBy('b')->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" DESC, "test"."b" DESC', $db->table('test')->orderBy('a', Table::SORT_DESC)->orderBy('b', Table::SORT_DESC)->buildSelectQuery());

        $this->assertEquals('SELECT * FROM "test"     ORDER BY "test"."a" DESC, "test"."b" ASC', $db->table('test')->desc('a')->asc('b')->buildSelectQuery());
    }

    public function testLimit()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT * FROM "test"      LIMIT 10', $db->table('test')->limit(10)->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"', $db->table('test')->limit(null)->buildSelectQuery());
    }

    public function testOffset()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT * FROM "test"       OFFSET 0', $db->table('test')->offset(0)->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"       OFFSET 10', $db->table('test')->offset(10)->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"', $db->table('test')->limit(null)->buildSelectQuery());
    }

    public function testLimitOffset()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT * FROM "test"      LIMIT 2  OFFSET 0', $db->table('test')->offset(0)->limit(2)->buildSelectQuery());
        $this->assertEquals('SELECT * FROM "test"      LIMIT 5  OFFSET 10', $db->table('test')->offset(10)->limit(5)->buildSelectQuery());
    }

    public function testSubquery()
    {
        $db = new Database(array('driver' => 'sqlite', 'filename' => ':memory:'));
        $this->assertEquals('SELECT (SELECT 1 FROM "foobar" WHERE 1=1) AS "b" FROM "test"', $db->table('test')->subquery('SELECT 1 FROM "foobar" WHERE 1=1', 'b')->buildSelectQuery());
    }
}
