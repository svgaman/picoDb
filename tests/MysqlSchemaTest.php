<?php

require_once 'vendor/autoload.php';
require_once __DIR__.'/SchemaFixture.php';

class MysqlSchemaTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        $this->db = new PicoDb\Database(array('driver' => 'mysql', 'hostname' => 'localhost', 'username' => 'root', 'password' => '', 'database' => 'picodb'));
        $this->db->getConnection()->exec('DROP TABLE IF EXISTS test1');
        $this->db->getConnection()->exec('DROP TABLE IF EXISTS test2');
        $this->db->getConnection()->exec('DROP TABLE IF EXISTS schema_version');
    }

    public function testMigrations()
    {
        $this->assertTrue($this->db->schema()->check(2));
        $this->assertEquals(2, $this->db->getDriver()->getSchemaVersion());
    }

    public function testFailedMigrations()
    {
        $this->assertEquals(0, $this->db->getDriver()->getSchemaVersion());
        $this->assertFalse($this->db->schema()->check(3));
        $this->assertEquals(2, $this->db->getDriver()->getSchemaVersion());

        $logs = $this->db->getLogMessages();
        $this->assertNotEmpty($logs);
        $this->assertEquals('Running migration \Schema\version_1', $logs[0]);
        $this->assertEquals('Running migration \Schema\version_2', $logs[1]);
        $this->assertEquals('Running migration \Schema\version_3', $logs[2]);
        $this->assertStringStartsWith('SQLSTATE[42000]: Syntax error or access violation', $logs[3]);
    }
}
