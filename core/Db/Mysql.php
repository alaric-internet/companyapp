<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:26
 */

namespace Alaric\Db;

use \Exception;
use PDO;

class Mysql implements PdoInterface
{
    /**
     * @var \PDO
     */
    public $_pdo = null;

    /**
     * @var int
     */
    public $_affectedRows = 0;

    /**
     * Mysql constructor.
     * @param array $descriptor
     * @throws \Exception
     */
    public function __construct($descriptor)
    {
        $this->connect($descriptor);
    }

    /**
     * Call it when you need to restore a database connection
     * @param array $descriptor
     * @return boolean
     * @throws \Exception
     */
    public function connect($descriptor = null)
    {
        if (!$descriptor['port']) {
            $descriptor['port'] = 3306;//默认端口
        }
        if (!$descriptor['charset']) {
            $descriptor['charset'] = 'utf8';
        }

        $descriptor['dsn'] = 'mysql:host=' . $descriptor['host'] . ';port=' . $descriptor['port'] . ';dbname=' . $descriptor['dbname'];

        try {
            $this->_pdo = new PDO($descriptor['dsn'], $descriptor['username'], $descriptor['password'], array(
                PDO::ATTR_PERSISTENT         => true,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $descriptor['charset']
            ));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        //重置sql_mode,防止datetime,group by 出错
        $this->_pdo->query("set sql_mode=''");

        return true;
    }

    /**
     * @param string $sqlStatement
     * @param array  $bindParams
     * @return \PDOStatement|boolean
     */
    public function query($sqlStatement, $bindParams = null)
    {
        if(!is_array($bindParams)){
            $bindParams = [];
        }
        $statement = $this->_pdo->prepare($sqlStatement);

        if(is_object($statement)){
            $statement = $this->executePrepared($statement, $bindParams);
        }else{
            throw new Exception("Cannot prepare statement");
        }

        return $statement;
    }

    /**
     * Sends SQL statements to the database server returning the success state.
     * @param string $sqlStatement
     * @param array  $bindParams
     * @return boolean
     */
    public function execute($sqlStatement, $bindParams = null)
    {
        $affectedRows = 0;

        if(is_array($bindParams)){
            $statement = $this->_pdo->prepare(($sqlStatement));
            if(is_object($statement)){
                $newStatement = $this->executePrepared($statement, $bindParams);
                $affectedRows = $newStatement->rowCount();
            }
        }else{
            $affectedRows = $this->_pdo->exec($sqlStatement);
        }

        if(is_int($affectedRows)){
            $this->_affectedRows = $affectedRows;
        }

        return true;
    }

    /**
     * Returns the number of affected rows by the last INSERT/UPDATE/DELETE reported
     * @return int
     */
    public function affectedRows()
    {
        return $this->_affectedRows;
    }

    /**
     * Closes active connection
     * @return boolean
     */
    public function close()
    {
        $this->_pdo = null;

        return true;
    }

    /**
     * Escapes a value to avoid SQL injections
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        return $this->_pdo->quote($str);
    }

    /**
     * Returns insert id for the auto_increment column inserted in the last SQL statement
     * @param string $sequenceName
     * @return int|boolean
     */
    public function lastInsertId($sequenceName = null)
    {
        if(!is_object($this->_pdo)){
            return false;
        }

        return $this->_pdo->lastInsertId($sequenceName);
    }

    /**
     * Starts a transaction in the connection
     * @param bool $nesting
     * @return boolean
     */
    public function begin($nesting = true)
    {
        // TODO: Implement begin() method.
    }

    /**
     * Rollbacks the active transaction in the connection
     * @param bool $nesting
     * @return boolean
     */
    public function rollback($nesting = true)
    {
        // TODO: Implement rollback() method.
    }

    /**
     * Commits the active transaction in the connection
     * @param bool $nesting
     * @return boolean
     */
    public function commit($nesting = true)
    {
        // TODO: Implement commit() method.
    }

    /**
     * Return the error info, if any
     * @return array|boolean
     */
    public function errorInfo()
    {
        if(!is_object($this->_pdo)){
            return false;
        }
        return $this->_pdo->errorInfo();
    }

    /**
     * Returns a PDO prepared statement to be executed with 'executePrepared'
     * @param string $sqlStatement
     * @return \PDOStatement
     */
    public function prepare($sqlStatement)
    {
        return $this->_pdo->prepare($sqlStatement);
    }

    /**
     * Executes a prepared statement binding. This function uses integer indexes starting from zero
     * @param \PDOStatement $statement
     * @param array         $placeholders
     * @return \PDOStatement
     */
    public function executePrepared($statement, $placeholders)
    {
        foreach ($placeholders as $wildcard => $value){
            if(is_int($wildcard)){
                $parameter = $wildcard + 1;
            }elseif(is_string($wildcard)){
                $parameter = $wildcard;
            }else{
                throw new Exception("Invalid bind parameter (1)");
            }

            if(!is_array($value)){
                $statement->bindValue($parameter, $value);
            }else{
                foreach ($value as $position => $itemValue){
                    $statement->bindValue($parameter . $position, $itemValue);
                }
            }
        }

        $statement->execute();
		return $statement;
    }
}