<?php

namespace PicoDb\Driver;

use PDO;
use LogicException;

/**
 * Base Driver class
 *
 * @author   Frederic Guillot
 */
abstract class Base
{
    /**
     * List of required settings options
     *
     * @access protected
     * @var array
     */
    protected $requiredAtttributes = array();

    /**
     * PDO connection
     *
     * @access protected
     * @var PDO
     */
    protected $pdo = null;

    /**
     * Create a new PDO connection
     *
     * @abstract
     * @access public
     * @param  array   $settings
     */
    abstract public function createConnection(array $settings);

    /**
     * Enable foreign keys
     *
     * @abstract
     * @access public
     */
    abstract public function enableForeignKeys();

    /**
     * Disable foreign keys
     *
     * @abstract
     * @access public
     */
    abstract public function disableForeignKeys();

    /**
     * Return true if the error code is a duplicate key
     *
     * @abstract
     * @access public
     * @param  integer  $code
     * @return boolean
     */
    abstract public function isDuplicateKeyError($code);

    /**
     * Escape identifier
     *
     * @abstract
     * @access public
     * @param  string  $identifier
     * @return string
     */
    abstract public function escape($identifier);

    /**
     * Get non standard operator
     *
     * @abstract
     * @access public
     * @param  string  $operator
     * @return string
     */
    abstract public function getOperator($operator);

    /**
     * Get last inserted id
     *
     * @abstract
     * @access public
     * @return integer
     */
    abstract public function getLastId();

    /**
     * Get current schema version
     *
     * @abstract
     * @access public
     * @return integer
     */
    abstract public function getSchemaVersion();

    /**
     * Set current schema version
     *
     * @abstract
     * @access public
     * @param  integer  $version
     */
    abstract public function setSchemaVersion($version);

    /**
     * Constructor
     *
     * @abstract
     * @access public
     * @param  array   $settings
     */
    public function __construct(array $settings)
    {
        foreach ($this->requiredAtttributes as $attribute) {
            if (! isset($settings[$attribute])) {
                throw new LogicException('This configuration parameter is missing: "'.$attribute.'"');
            }
        }

        $this->createConnection($settings);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get the PDO connection
     *
     * @access public
     * @return PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }

    /**
     * Release the PDO connection
     *
     * @access public
     */
    public function closeConnection()
    {
        $this->pdo = null;
    }
}
