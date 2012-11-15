<?php

namespace PicoDb;

class Table
{
    private $table_name = '';
    private $sql_limit = '';
    private $sql_offset = '';
    private $sql_order = '';
    private $conditions = array();
    private $or_conditions = array();
    private $is_or_condition = false;
    private $columns = array();
    private $values = array();

    private $db;


    public function __construct(Database $db, $table_name)
    {
        $this->db = $db;
        $this->table_name = $table_name;

        return $this;
    }


    public function save(array $data)
    {
        if (! empty($this->conditions)) {

            // Update
        }
        else {

            return $this->insert($data);
        }
    }


    public function insert(array $data)
    {
        $columns = array();

        foreach ($data as $column => $value) {

            $columns[] = $this->db->escapeIdentifier($column);
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->db->escapeIdentifier($this->table_name),
            implode(', ', $columns),
            implode(', ', array_fill(0, count($data), '?'))
        );

        return false !== $this->db->execute($sql, array_values($data));
    }  


    public function findAll()
    {
        $sql = sprintf(
            'SELECT %s FROM %s'.$this->conditions().$this->sql_order.$this->sql_limit.$this->sql_offset,
            empty($this->columns) ? '*' : implode(', ', $columns),
            $this->db->escapeIdentifier($this->table_name)
        );

        $rq = $this->db->execute($sql, $this->values);

        if (false === $rq) {

            return false;
        }

        return $rq->fetchAll(\PDO::FETCH_CLASS);
    }


    public function findOne()
    {
        $this->limit(1);
        $result = $this->findAll();

        return isset($result[0]) ? $result[0] : null;
    }


    public function conditions()
    {
        if (! empty($this->conditions)) {

            return ' WHERE '.implode(' AND ', $this->conditions);
        }
        else {

            return '';
        }
    }


    public function addCondition($sql)
    {
        if ($this->is_or_condition) {

            $this->or_conditions[] = $sql;
        }
        else {

            $this->conditions[] = $sql;
        }
    }


    public function beginOr()
    {
        $this->is_or_condition = true;
        $this->or_conditions = array();
        
        return $this;
    }


    public function closeOr()
    {
        $this->is_or_condition = false;

        if (! empty($this->or_conditions)) {

            $this->conditions[] = '('.implode(' OR ', $this->or_conditions).')';
        }

        return $this;
    }


    public function asc($column)
    {
        $this->sql_order = ' ORDER BY '.$this->db->escapeIdentifier($column).' ASC';
        return $this;
    }


    public function desc($column)
    {
        $this->sql_order = ' ORDER BY '.$this->db->escapeIdentifier($column).' DESC';
        return $this;
    }


    public function limit($value)
    {
        $this->sql_limit = ' LIMIT '.(int) $value;
        return $this;
    }


    public function offset($value)
    {
        $this->sql_offset = ' OFFSET '.(int) $value;
        return $this;
    }


    public function __call($name, array $arguments)
    {
        if (2 !== count($arguments)) {

            throw new \LogicException('You must define a column and a value.');
        }

        $column = $arguments[0];
        $sql = '';
        
        switch ($name) {

            case 'like':
                $sql = sprintf('%s LIKE ?', $this->db->escapeIdentifier($column));
                break;

            case 'eq':
            case 'equal':
            case 'equals':
                $sql = sprintf('%s = ?', $this->db->escapeIdentifier($column));
                break;

            case 'gt':
            case 'greaterThan':
                $sql = sprintf('%s > ?', $this->db->escapeIdentifier($column));
                break;

            case 'lt':
            case 'lowerThan':
                $sql = sprintf('%s < ?', $this->db->escapeIdentifier($column));
                break;

            case 'gte':
            case 'greaterThanOrEquals':
                $sql = sprintf('%s >= ?', $this->db->escapeIdentifier($column));
                break;

            case 'lte':
            case 'lowerThanOrEquals':
                $sql = sprintf('%s <= ?', $this->db->escapeIdentifier($column));
                break;
        }

        if ('' !== $sql) {

            $this->addCondition($sql);
            $this->values[] = $arguments[1];
        }

        return $this;
    }
}