<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:26
 */

namespace Alaric;

use Alaric\Db\Mysql;
use Alaric\Db\Factory as DbFactory;
use Exception;

class Db implements DbInterface
{
    /**
     * @var Mysql
     */
    public $adapter;

    protected $_prefix;
    /**
     * Db constructor.
     * @param $config
     * @throws \Exception
     */
    public function __construct($config) {
        $this->_prefix = $config['prefix'];

        $this->adapter = DbFactory::load($config);
    }

    /**
     * Returns the first row in a SQL query result
     * @param string $sqlQuery
     * @param int    $bindParams
     * @return array
     */
    public function fetchOne($sqlQuery, $bindParams = null)
    {
        $result = $this->adapter->query($sqlQuery, $bindParams);
        if(is_object($result)){
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            return $result->fetch();
        }

        return [];
    }

    /**
     * return the first column in a SQL query result
     * @param string $sqlQuery
     * @param array  $bindParams
     * @param int|string    $column
     * @return string
     */
    public function fetchColumn($sqlQuery, $bindParams = null, $column = 0){
        $row = $this->fetchOne($sqlQuery, $bindParams);

        if($row && $row[$column]){
            return $row[$column];
        }
        return false;
    }

    /**
     * Dumps the complete result of a query into an array
     * @param string $sqlQuery
     * @param array    $bindParams
     * @return array
     */
    public function fetchAll($sqlQuery, $bindParams = null)
    {
        $results = [];
        $result = $this->adapter->query($sqlQuery, $bindParams);
        if(is_object($result)){
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $results = $result->fetchAll();
        }

        return $results;
    }

    /**
     * Inserts data into a table
     * @param string $table
     * @param array  $data
     * @return boolean
     * @throws \Exception
     */
    public function insert($table, $data)
    {
        if(!is_array($data) || empty($data)){
            return false;
        }

        $placeholder = $insertValues = [];

        foreach ($data as $field => $value){
            if(is_object($value)){
                $value = (string) $value;
            }

            if(is_null($value)){
                $placeholder[] = "null";
            }else{
                $placeholder[] = "?";
                $insertValues[] = $value;
            }
        }

        $escapedTable = $this->escapeIdentifier($table);

        $joinedValues = join(", ", $placeholder);

        $fields = array_keys($data);

        $escapedFields = [];
        foreach ($fields as $field){
            $escapedFields[] = $this->escapeIdentifier($field);
        }

        $insertSql = "INSERT INTO " . $escapedTable . " (" . join(", ", $escapedFields) . ") VALUES (" . $joinedValues . ")";

        $this->adapter->execute($insertSql, $insertValues);

        return $this->lastInsertId();
    }

    /**
     * Gets a list of columns
     * @param string $table
     * @return array
     */
    public function getFields($table)
    {
        $fields = [];

        $escapedTable = $this->escapeIdentifier($table);

        $sql = "SHOW COLUMNS FROM " . $escapedTable;

        $result = $this->adapter->query($sql);
        if(is_object($result)){
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $results = $result->fetchAll();

            foreach ($results as $value){
                $fields[$value['Field']] = $value['Type'];
            }
        }

        return $fields;
    }

    /**
     * check if a field exist in a table
     * @param string $table
     * @param string $field
     * @return bool
     */
    public function fieldExists($table, $field)
    {
        $fields = $this->getFields($table);

        return array_key_exists($field, $fields);
    }

    /**
     * Appends a LIMIT clause to sqlQuery argument
     * @param string $sqlQuery
     * @param int    $number
     * @return string
     */
    public function limit($sqlQuery, $number)
    {
        if(is_array($number)){
            $sqlQuery .= " LIMIT " . $number[0];
            if(!empty($number[1])){
                $sqlQuery .= " OFFSET " . $number[1];
            }

            return $sqlQuery;
        }

        return $sqlQuery . " LIMIT " . $number;
    }

    /**
     * Generates SQL checking for the existence of a schema.table
     * @param string $tableName
     * @param string $schemaName
     * @return boolean
     */
    public function tableExists($tableName, $schemaName = null)
    {
        $tables = $this->listTables();

        return in_array($tableName, $tables) ? true : false;
    }

    /**
     * List all tables on a database
     * @return array
     */
    public function listTables()
    {
        $tables = [];
        $sql = "SHOW TABLES";

        $result = $this->adapter->query($sql);
        if(is_object($result)){
            $result->setFetchMode(\PDO::FETCH_NUM);
            $results = $result->fetchAll();
            foreach ($results as $table){
                $tables[] = $table[0];
            }
        }

        return $tables;
    }

    /**
     * Returns the number of affected rows by the last INSERT/UPDATE/DELETE reported
     * @return int
     */
    public function affectedRows()
    {
        return $this->adapter->affectedRows();
    }

    /**
     * Escapes a column/table/schema name
     * @param string|array $identifier
     * @return string
     */
    public function escapeIdentifier($identifier)
    {
        if(is_array($identifier)){
            $str = "";
            foreach ($identifier as $item){
                $str .= "`" . trim($item, "`") . "`";
            }
        }else{
            $str = "`" . trim($identifier, "`") . "`";
        }

        return $str;
    }

    public function escapeString($str)
    {
        return $this->adapter->escapeString($str);
    }

    /**
     * Returns insert id for the auto_increment column inserted in the last SQL statement
     * @param string $sequenceName
     * @return int|boolean
     */
    public function lastInsertId($sequenceName = null)
    {
        return $this->adapter->lastInsertId($sequenceName);
    }

    /**
     * Return the error info, if any
     * @return array
     */
    public function errorInfo()
    {
        return $this->adapter->errorInfo();
    }

    /**
     * @param string $sqlStatement
     * @param array  $placeholders
     * @return array|boolean
     */
    public function query($sqlStatement, $placeholders = null)
    {
        return $this->adapter->query($sqlStatement, $placeholders);
    }

    /**
     * Sends SQL statements to the database server returning the success state.
     * @param string $sqlStatement
     * @param array  $placeholders
     * @return boolean
     */
    public function execute($sqlStatement, $placeholders = null)
    {
        return $this->adapter->execute($sqlStatement, $placeholders);
    }

    public function getPrefix(){
        return $this->_prefix;
    }


}