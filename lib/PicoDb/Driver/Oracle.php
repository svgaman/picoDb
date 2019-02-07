<?php

namespace PicoDb\Driver;

use PDO;
use PDOException;

/**
 * Oracle Driver
 *
 * WARNING : PicoDb generated queries are not fully compatible with Oracle Database (limit offset ??)
 *
 * @package PicoDb\Driver
 * @author Svgaman
 */
class Oracle extends Base
{
    /**
     * List of required settings options
     *
     * @access protected
     * @var array
     */
    protected $requiredAttributes = array(
        'hostname',
        'username',
        'password',
        'database',
    );

    /**
     * Create a new PDO connection
     *
     * @access public
     * @param  array   $settings
     */
    public function createConnection(array $settings)
    {
        $this->pdo = new PDO(
            $this->buildDsn($settings),
            $settings['username'],
            $settings['password'],
            $this->buildOptions($settings)
        );

        $this->alterSession($settings);
    }

    /**
     * Build connection DSN
     *
     * @access protected
     * @param  array $settings
     * @return string
     */
    protected function buildDsn(array $settings)
    {
        $charset = empty($settings['charset']) ? 'AL32UTF8' : $settings['charset'];
        $dsn = 'oci:dbname=' . $settings['database'] . ';charset='.$charset;

        return $dsn;
    }

    /**
     * Build connection options
     *
     * @access protected
     * @param  array $settings
     * @return array
     */
    protected function buildOptions(array $settings)
    {
        if (! empty($settings['persistent'])) {
            $options[PDO::ATTR_PERSISTENT] = $settings['persistent'];
        } else {
            $options[PDO::ATTR_PERSISTENT] = TRUE;
        }

        if (! empty($settings['errmode'])) {
            $options[PDO::ATTR_ERRMODE] = $settings['errmode'];
        } else {
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        if (! empty($settings['emulate_prepare'])) {
            $options[PDO::ATTR_EMULATE_PREPARES] = $settings['emulate_prepare'];
        } else {
            $options[PDO::ATTR_EMULATE_PREPARES] = TRUE;
        }

        if (! empty($settings['case'])) {
            $options[PDO::ATTR_CASE] = $settings['case'];
        } else {
            $options[PDO::ATTR_CASE] = PDO::CASE_LOWER;
        }

        return $options;
    }

    /**
     * Execute session queries
     *
     * @param array $settings
     */
    private function alterSession(array $settings)
    {
        if (!empty($settings['nlsdateformat'])) {
            $this->pdo->exec('ALTER SESSION SET NLS_DATE_FORMAT = "' . $settings['nlsdateformat'] . '"');
        }

        if (!empty($settings['nlscomp'])) {
            $this->pdo->exec('ALTER SESSION SET NLS_COMP = ' . $settings['nlscomp']);
        }

        if (!empty($settings['nlssort'])) {
            $this->pdo->exec('ALTER SESSION SET NLS_SORT = ' . $settings['nlssort']);
        }
    }

    /**
     * Enable foreign keys
     *
     * @access public
     */
    public function enableForeignKeys()
    {
    }

    /**
     * Disable foreign keys
     *
     * @access public
     */
    public function disableForeignKeys()
    {
    }

    /**
     * Return true if the error code is a duplicate key
     *
     * @access public
     * @param  integer  $code
     * @return boolean
     */
    public function isDuplicateKeyError($code)
    {
        return $code == 23505 || $code == 23503;
    }

    /**
     * Escape identifier
     *
     * @access public
     * @param  string  $identifier
     * @return string
     */
    public function escape($identifier)
    {
        return '"'.$identifier.'"';
    }

    /**
     * Get non standard operator
     *
     * @access public
     * @param  string  $operator
     * @return string
     */
    public function getOperator($operator)
    {
        if ($operator === 'LIKE') {
            return 'LIKE';
        }

        return '';
    }

    /**
     * Get last inserted id
     *
     * @access public
     * @return integer
     */
    public function getLastId()
    {
        try {
            $rq = $this->pdo->prepare('SELECT LASTVAL()');
            $rq->execute();

            return $rq->fetchColumn();
        }
        catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get current schema version
     *
     * @access public
     * @return integer
     */
    public function getSchemaVersion()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS ".$this->schemaTable." (version INTEGER DEFAULT 0)");

        $rq = $this->pdo->prepare('SELECT "version" FROM "'.$this->schemaTable.'"');
        $rq->execute();
        $result = $rq->fetchColumn();

        if ($result !== false) {
            return (int) $result;
        }
        else {
            $this->pdo->exec('INSERT INTO '.$this->schemaTable.' VALUES(0)');
        }

        return 0;
    }

    /**
     * Set current schema version
     *
     * @access public
     * @param  integer  $version
     */
    public function setSchemaVersion($version)
    {
        $rq = $this->pdo->prepare('UPDATE '.$this->schemaTable.' SET version=?');
        $rq->execute(array($version));
    }
}