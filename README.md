PicoDb: A minimalist database query builder for PHP
===================================================

PicoDb is a simple PHP library to use a relational database.
**It's not an ORM**.

Features
--------

- Query builder in PHP5
- No dependency
- Easy to use, fast and very lightweight
- You can use it with a dependency injection container
- Use prepared statements

Requirements
------------

- PHP >= 5.3 or 5.4
- PDO
- A database: Sqlite, Mysql or Postgresql

Examples
--------

## Connect to your database

    use PicoDb\Database;

    $db = new Database(['driver' => 'sqlite', 'filename' => ':memory:']);

## Execute a SQL request

    $db->execute('CREATE TABLE toto (column1 TEXT)');

## Insert some data

    $db->table('toto')->save(['column1' => 'hey']);

## Fetch all data

    $records = $db->table('toto')->findAll();

    foreach ($records as $record) {

        var_dump($record->column1);
    }

## Equals condition

    $db->table('toto')
       ->equals('column1', 'hey')
       ->findAll();

or

    $db->table('toto')
       ->eq('column1', 'hey')
       ->findAll();

Yout got: 'SELECT * FROM toto WHERE column1=?'

## Update something

    $db->table('toto')->eq('id', 1)->save(['column1' => 'hey']);

You just need to add a condition to perform an update.

## Remove rows

    $db->table('toto')->lowerThan('column1', 10)->remove();