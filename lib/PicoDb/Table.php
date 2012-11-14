<?php

namespace PicoDb;

class Table
{
    private $table_name = '';
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
            'SELECT %s FROM %s'.$this->conditions(),
            empty($this->columns) ? '*' : implode(', ', $columns),
            $this->db->escapeIdentifier($this->table_name)
        );

        $rq = $this->db->execute($sql, $this->values);

        if (false === $rq) {

            return false;
        }

        return $rq->fetchAll(\PDO::FETCH_CLASS);
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


    public function beginOr()
    {
        $this->is_or_condition = true;
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


    public function equals($column, $value)
    {
        $sql = sprintf(
            '%s = %s',
            $this->db->escapeIdentifier($column),
            '?'
        );

        if ($this->is_or_condition) {

            $this->or_conditions[] = $sql;
        }
        else {

            $this->conditions[] = $sql;
        }
        
        $this->values[] = $value;

        return $this;
    }
}