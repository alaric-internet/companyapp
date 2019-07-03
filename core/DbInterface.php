<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-28
 * Time: 10:39
 */

namespace Alaric;

interface DbInterface
{
    /**
     * Returns the first row in a SQL query result
     * @param string $sqlQuery
     * @param array $placeholders
     * @return array
     */
    public function fetchOne($sqlQuery, $placeholders = null);

    /**
     * Dumps the complete result of a query into an array
     * @param string $sqlQuery
     * @param array $placeholders
     * @return array
     */
    public function fetchAll($sqlQuery, $placeholders = null);

    /**
     * Gets a list of columns
     * @param string $table
     * @return string
     */
    public function getFields($table);

    /**
     * Appends a LIMIT clause to sqlQuery argument
     * @param string $sqlQuery
     * @param int $number
     * @return string
     */
    public function limit($sqlQuery, $number);

    /**
     * Generates SQL checking for the existence of a schema.table
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function tableExists($tableName, $schemaName = null);

    /**
     * List all tables on a database
     * @return array
     */
    public function listTables();

    /**
     * @param string $sqlStatement
     * @param array $placeholders
     * @return array|boolean
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
     * Escapes a column/table/schema name
     * @param string $identifier
     * @return string
     */
    public function escapeIdentifier($identifier);

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
     * Return the error info, if any
     * @return array
     */
    public function errorInfo();
}