<?php

namespace PicoDb;

use PDO;

/**
 * Hashtable (key/value)
 *
 * @author   Frederic Guillot
 * @author   Mathias Kresin
 */
class Hashtable extends Table
{
    /**
     * Column for the key
     *
     * @access private
     * @var    string
     */
    private $column_key = 'key';

    /**
     * Column for the value
     *
     * @access private
     * @var    string
     */
    private $column_value = 'value';

    /**
     * Set the key column
     *
     * @access public
     * @param  string  $column
     * @return Table
     */
    public function columnKey($column)
    {
        $this->column_key = $column;
        return $this;
    }

    /**
     * Set the value column
     *
     * @access public
     * @param  string  $column
     * @return Table
     */
    public function columnValue($column)
    {
        $this->column_value = $column;
        return $this;
    }

    /**
     * Insert or update
     *
     * @access public
     * @param  array    $data
     * @return boolean
     */
    public function put(array $data)
    {
        switch ($this->db->getConnection()->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
                return $this->handleMysqlUpsert($data);
            case 'sqlite':
                return $this->handleSqliteUpsert($data);
            default:
                return $this->handleGenericUpsert($data);
        }
    }

    /**
     * Hashmap result [ [column1 => column2], [], ...]
     *
     * @access public
     * @return array
     */
    public function get()
    {
        $hashmap = array();

        // setup where condition
        if (func_num_args() > 0) {
            $this->in($this->column_key, func_get_args());
        }

        // setup to select columns in case that there are more than two
        $this->columns($this->column_key, $this->column_value);

        $rq = $this->db->execute($this->buildSelectQuery(), $this->condition->getValues());
        $rows = $rq->fetchAll(PDO::FETCH_NUM);

        foreach ($rows as $row) {
            $hashmap[$row[0]] = $row[1];
        }

        return $hashmap;
    }

    /**
     * Shortcut method to get a hashmap result
     *
     * @access public
     * @param  string  $key    Key column
     * @param  string  $value  Value column
     * @return array
     */
    public function getAll($key, $value)
    {
        $this->column_key = $key;
        $this->column_value = $value;
        return $this->get();
    }

    /**
     * Handle UPSERT for Mysql
     *
     * @access private
     * @param  array    $data
     * @return boolean
     */
    private function handleMysqlUpsert(array $data)
    {
        $values = array();

        $sql = sprintf(
            'REPLACE INTO %s (%s) VALUES %s',
            $this->db->escapeIdentifier($this->name),
            "$this->column_key, $this->column_value",
            implode(', ', array_fill(0, count($data), '(?, ?)'))
        );

        foreach ($data as $key => $value) {
            $values[] = $key;
            $values[] = $value;
        }

        return $this->db->execute($sql, $values);
    }

    /**
     * Handle UPSERT for Sqlite
     *
     * Note: requires sqlite library > 3.7.11 (bundled with PHP 5.5.11+)
     *
     * @access private
     * @param  array    $data
     * @return boolean
     */
    private function handleSqliteUpsert(array $data)
    {
        $this->db->startTransaction();

        foreach ($data as $key => $value) {

            $sql = sprintf(
                'INSERT OR REPLACE INTO %s (%s) VALUES (?, ?)',
                $this->db->escapeIdentifier($this->name),
                $this->db->escapeIdentifier($this->column_key).', '.$this->db->escapeIdentifier($this->column_value)
            );

            $this->db->execute($sql, array($key, $value));
        }

        $this->db->closeTransaction();

        return true;
    }

    /**
     * Handle UPSERT for everything else
     *
     * @access private
     * @param  array    $data
     * @return boolean
     */
    private function handleGenericUpsert(array $data)
    {
        $this->db->startTransaction();

        foreach($data as $key => $value) {
            $this->eq($this->column_key, $key);

            if ($this->count() === 1) {
                $this->update(array($this->column_key => $key, $this->column_value => $value));
            }
            else {
                $this->insert(array($this->column_key => $key, $this->column_value => $value));
            }
        }

        $this->db->closeTransaction();

        return true;
    }
}
