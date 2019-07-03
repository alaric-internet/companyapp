<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:33
 */

namespace Alaric\db;

interface PdoInterface
{
    /**
     * Call it when you need to restore a database connection
     * @param array $descriptor
     * @return boolean
     * @throws \Exception
     */
    public function connect($descriptor = null);

    /**
     * Returns a PDO prepared statement to be executed with 'executePrepared'
     * @param string $sqlStatement
     * @return \PDOStatement
     */
    public function prepare($sqlStatement);

    /**
     * Executes a prepared statement binding. This function uses integer indexes starting from zero
     * @param \PDOStatement $statement
     * @param array $placeholders
     * @return \PDOStatement
     */
    public function executePrepared($statement, $placeholders);

    /**
     * @param string $sqlStatement
     * @param array $placeholders
     * @return \PDOStatement|boolean
     */
    public function query($sqlStatement, $placeholders = null);

    /**
     * Sends SQL statements to the database server returning the success state.
     * @param string $sqlStatement
     * @param array $placeholders
     * @return boolean
     */
    public function execute($sqlStatement, $placeholders = null);

    /**
     * Returns the number of affected rows by the last INSERT/UPDATE/DELETE reported
     * @return int
     */
    public function affectedRows();

    /**
     * Closes active connection
     * @return boolean
     */
    public function close();

    /**
     * Escapes a value to avoid SQL injections
     * @param string $str
     * @return string
     */
    public function escapeString($str);

    /**
     * Returns insert id for the auto_increment column inserted in the last SQL statement
     * @param string $sequenceName
     * @return int|boolean
     */
    public function lastInsertId($sequenceName = null);

    /**
     * Starts a transaction in the connection
     * @param bool $nesting
     * @return boolean
     */
    public function begin($nesting = true);

    /**
     * Rollbacks the active transaction in the connection
     * @param bool $nesting
     * @return boolean
     */
    public function rollback($nesting = true);

    /**
     * Commits the active transaction in the connection
     * @param bool $nesting
     * @return boolean
     */
    public function commit($nesting = true);

    /**
     * Return the error info, if any
     * @return array
     */
    public function errorInfo();
}