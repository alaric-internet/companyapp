<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-07-02
 * Time: 19:22
 */

namespace Alaric;

use Exception;

class Models
{
    /** @var Db */
    public static $db;

    protected static $_prefix;

    protected static $_sourceName;

    protected static $_identityField;

    protected static $_fields;

    public function __construct() {
        self::$db = self::getDI()->getShared('db');

        self::$_prefix = self::$db->getPrefix();

    }

    public static function getDI(){
        return DI::getDefault();
    }

    public static function getDB(){
        if(!self::$db){
            self::$db = self::getDI()->getShared('db');
        }

        return self::$db;
    }

    public static function setSource($name){
        self::$_sourceName = $name;
    }

    public static function getSource(){
        if(!self::$_sourceName){
            $source = explode('\\', get_called_class());
            $source = preg_split("/(?=[A-Z])/", lcfirst(end($source)));

            self::$_sourceName = strtolower(self::$_prefix . implode('_', $source));
        }

        return self::$_sourceName;
    }

    public static function fetchOne($parameters = null){
        list($sqlQuery, $bindParams, $cacheOptions) = self::getPreparedQuery($parameters);

        /** @var Cache $cache */
        $cache = null;
        if($cacheOptions){
            $cache = self::getDI()->getShared('cache');

            if(is_object($cache)){
                $result = $cache->get($cacheOptions['key'], $cacheOptions['lifetime']);

                if($result){
                    return $result;
                }
            }
        }

        $result = self::getDB()->fetchOne($sqlQuery, $bindParams);

        if($cache){
            $cache->save($cacheOptions['key'], $result, $cacheOptions['lifetime']);
        }

        return $result;
    }

    public static function fetchColumn($parameters = null){
        list($sqlQuery, $bindParams, $cacheOptions) = self::getPreparedQuery($parameters);

        /** @var Cache $cache */
        $cache = null;
        if($cacheOptions){
            $cache = self::getDI()->getShared('cache');

            if(is_object($cache)){
                $result = $cache->get($cacheOptions['key'], $cacheOptions['lifetime']);

                if($result){
                    return $result;
                }
            }
        }

        $result = self::getDB()->fetchColumn($sqlQuery, $bindParams);

        if($cache){
            $cache->save($cacheOptions['key'], $result, $cacheOptions['lifetime']);
        }

        return $result;
    }

    public static function fetchAll($parameters = null){
        list($sqlQuery, $bindParams, $cacheOptions) = self::getPreparedQuery($parameters);

        /** @var Cache $cache */
        $cache = null;
        if($cacheOptions){
            $cache = self::getDI()->getShared('cache');

            if(is_object($cache)){
                $result = $cache->get($cacheOptions['key'], $cacheOptions['lifetime']);

                if($result){
                    return $result;
                }
            }
        }

        $result = self::getDB()->fetchAll($sqlQuery, $bindParams);

        if($cache){
            $cache->save($cacheOptions['key'], $result, $cacheOptions['lifetime']);
        }

        return $result;
    }

    public static function query($sqlQuery, $bindParams = null){
        return self::getDB()->query($sqlQuery, $bindParams);
    }

    public static function count($parameters = null){
        $parameters['columns'] = "COUNT(*)";
        list($sqlQuery, $bindParams, $cacheOptions) = self::getPreparedQuery($parameters);

        return self::getDB()->fetchColumn($sqlQuery, $bindParams);
    }

    public static function sum(){
        $parameters['columns'] = "SUM(*)";
        list($sqlQuery, $bindParams, $cacheOptions) = self::getPreparedQuery($parameters);

        return self::getDB()->fetchColumn($sqlQuery, $bindParams);
    }

    public static function save($data = null){
        $identityField = self::getIdentityField();
        if($data[$identityField]){
            $whereCondition = "$identityField = ?";
            $bindParams = [$identityField => $data[$identityField]];
            return self::update($data, $whereCondition, $bindParams);
        }else{
            return self::create($data);
        }
    }

    public function saveAs($data = null){
        if($data){
            $this->assign($data);
        }

        $data = $this->toArray();

        $identityField = self::getIdentityField();
        if(!empty($data[$identityField])){
            return $this->updateAs($data);
        }else{
            return $this->createAs($data);
        }
    }

    public function createAs($data = null){
        if($data){
            $this->assign($data);
        }

        $data = $this->toArray();

        $result = self::create($data);
        $identityField = self::getIdentityField();
        $params = [
            "$identityField = ?",
            "bind" => [$result]
        ];
        $data = self::fetchOne($params);
        $this->assign($data);

        return $result;
    }

    public function updateAs($data = null){
        if($data){
            $this->assign($data);
        }

        $data = $this->toArray();

        $identityField = self::getIdentityField();
        if($data[$identityField]){
            $whereCondition = "$identityField = ?";
            $bindParams = [$data[$identityField]];
            $result = self::update($data, $whereCondition, $bindParams);

            $params = [
                $whereCondition,
                "bind" => $bindParams
            ];
            $data = self::fetchOne($params);
            $this->assign($data);

            return $result;
        }else{
            return false;
        }
    }

    public function deleteAs(){
        $identityField = self::getIdentityField();
        if (!$identityField || empty($this->{$identityField})) {
            return false;
        }

        $where = "$identityField = ?";
        $bindParams = [$this->{$identityField}];

        self::delete($where, $bindParams);
    }

    public static function create($data = null){
        if(!is_array($data) || empty($data)){
            return false;
        }
        $table = self::getSource();
        $escapedTable = self::escapeIdentifier($table);
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

        $joinedValues = join(", ", $placeholder);

        $fields = array_keys($data);

        $escapedFields = [];
        foreach ($fields as $field){
            $escapedFields[] = self::escapeIdentifier($field);
        }

        $insertSql = "INSERT INTO " . $escapedTable . " (" . join(", ", $escapedFields) . ") VALUES (" . $joinedValues . ")";

        $result = self::getDB()->execute($insertSql, $insertValues);

        if($result) {
            return self::getDB()->lastInsertId();
        }

        return false;
    }

    public static function update($data = null, $whereCondition = null, $bindParams = null){
        if(!is_array($data) || empty($data)){
            return false;
        }
        $table = self::getSource();
        $escapedTable = self::escapeIdentifier($table);

        $placeholders = $updateValues = [];

        foreach ($data as $field => $value){
            $escapedField = self::escapeIdentifier($field);
            if(is_object($value)){
                $value = (string) $value;
            }

            if(is_null($value)){
                $placeholders[] = $escapedField . " = null";
            }else{
                $updateValues[] = $value;
                $placeholders[] = $escapedField . " = ?";
            }
        }

        $setClause = join(", ", $placeholders);

        if($whereCondition !== null) {
            $updateSql = "UPDATE " . $escapedTable . " SET " . $setClause . " WHERE ";

            if(is_string($whereCondition)){
                $updateSql .= $whereCondition;
            }else{
                if(!is_array($whereCondition)){
                    throw new Exception("Invalid WHERE clause conditions");
                }

                if($whereCondition["conditions"]){
                    $updateSql .= $whereCondition["conditions"];
                }

                if($whereCondition["bind"]){
                    $updateValues = array_merge($updateValues, $whereCondition["bind"]);
                }
            }
        }else{
            $updateSql = "UPDATE " . $escapedTable . " SET " . $setClause;
        }

        if(is_array($bindParams)){
            $updateValues = array_merge($updateValues, $bindParams);
        }

        $result = self::getDB()->execute($updateSql, $updateValues);

        return $result;
    }

    public static function delete($whereCondition = null, $bindParams = null){
        if(empty($whereCondition)){
            return false;
        }
        $table = self::getSource();
        $escapedTable = self::escapeIdentifier($table);

        $sql = "DELETE FROM " . $escapedTable;

        $whereCondition && $sql .= " WHERE " . $whereCondition;

        self::getDB()->execute($sql, $bindParams);

        return self::affectedRows();
    }

    /**
     * <code>
     * $params = [
     *      "from"       => "Article",
     *      "columns"    => ["id", "name"],
     *      "conditions" => [
     *          "id > :id AND name = :name",
     *          [
     *              "id"   => "1",
     *              "name" => "alaric"
     *          ]
     *      ]
     *      // or "conditions" => "id > 1 AND name = 'alaric'",
     *      "group"  => ["id", "name"] || "name",
     *      "having" => "name = 'alaric'",
     *      "order"  => ["id", "name"] || "name",
     *      "limit"  => 10 || [10, 10],
     *      "offset" => 10,
     * ];
     * </code>
     * @param array|string $params
     * @param array|string $limit
     * @return array
     */
    public static function getPreparedQuery($params, $limit = null){
        $parameters = [];
        if(is_array($params)){
            $conditions = null;
            if($params[0]){
                $conditions = $params[0];
            }elseif($params["conditions"]){
                $conditions = $params["conditions"];
            }

            if(is_array($conditions)){
                $parameters['conditions'] = $conditions[1];
                $parameters['bindParams'] = $conditions[0];
            }else{
                $parameters['conditions'] = $conditions;
            }

            if($params['bind']){
                $parameters['bindParams'] = $params['bind'];
            }

            if($params['distinct']){
                $parameters['distinct'] = $params["distinct"];
            }

            if($params['columns']){
                if(is_array($params["columns"])){
                    $parameters['columns'] = implode(", ", $params["columns"]);
                }else{
                    $parameters['columns'] = $params["columns"];
                }
            }

            if($params['form']){
                $parameters['from'] = $params["from"];
            }

            if($params['alias']){
                $parameters['alias'] = $params["alias"];
            }

            if($params['group']){
                if(is_array($params["group"])){
                    $parameters['group'] = implode(", ", $params["group"]);
                }else{
                    $parameters['group'] = $params["group"];
                }
            }

            if($params['having']){
                if(is_array($params["having"])){
                    $parameters['having'] = implode(", ", $params["having"]);
                }else{
                    $parameters['having'] = $params["having"];
                }
            }
//
//            if($params['joins']){
//                $parameters['joins'] = $params["joins"];
//            }

            if($params['order']){
                if(is_array($params["order"])){
                    $parameters['order'] = implode(", ", $params["order"]);
                }else{
                    $parameters['order'] = $params["order"];
                }
            }

            if($params['limit']){
                if(is_array($params['limit'][0])){
                    $parameters['limit'] = $params['limit'][0];
                    $parameters['offset'] = $params['limit'][1];
                }else{
                    $parameters['limit'] = $params['limit'];
                }
            }

            if($params['offset']){
                $parameters['offset'] = $params["offset"];
            }

            if($params['cache']){
                if(is_string($params['cache'])){
                    $parameters['cache'] = [
                        'key' => $params['cache'],
                        'lifetime' => 3600,
                    ];
                }else{
                    $parameters['cache'] = [
                        'key' => $params["cache"]['key'],
                        'lifetime' => $params["cache"]['lifetime'] ? $params["cache"]['lifetime'] : 3600
                    ];
                }
            }
        }else{
            if($params) {
                $parameters['conditions'] = $params;
            }
        }

        if(!$parameters['from']){
            $parameters['from'] = self::getSource();
        }

        if($limit){
            if(is_array($limit[0])){
                $parameters['limit'] = $limit[0];
                $parameters['offset'] = $limit[1];
            }else{
                $parameters['limit'] = $limit;
            }
        }

        $escapedTable = self::getDB()->escapeIdentifier($parameters['from']);

        $sqlQuery = "SELECT";
        if($parameters['distinct']){
            $sqlQuery .= " DISTINCT";
        }
        $sqlQuery .= $parameters['columns'] ? $parameters['columns'] : "*";
        $sqlQuery .= " FROM " . $escapedTable;
        $parameters['alias'] && $sqlQuery .= " as " . $parameters['alias'];
        $parameters['conditions'] && $sqlQuery .= " WHERE " . $parameters['conditions'];
        $parameters['group'] && $sqlQuery .= " GROUP BY " . $parameters['group'];
        $parameters['having'] && $sqlQuery .= " HAVING " . $parameters['having'];
        $parameters['order'] && $sqlQuery .= " ORDER BY " . $parameters['order'];
        $parameters['limit'] && $sqlQuery .= " LIMIT " . $parameters['limit'];
        $parameters['offset'] && $sqlQuery .= " LIMIT " . $parameters['limit'];

        return [$sqlQuery, $parameters['bindParams'], $parameters['cache']];
    }

    /**
     * Escapes a column/table/schema name
     * @param string|array $identifier
     * @return string
     */
    final public static function escapeIdentifier($identifier)
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

    final static public function affectedRows(){
        return self::getDB()->affectedRows();
    }

    final static public function getIdentityField()
    {
        if(!self::$_identityField) {
            $table = self::getSource();
            $res = self::getDB()->fetchAll("SHOW COLUMNS FROM $table");
            foreach ($res as $r) {
                if ($r['Key'] == 'PRI') {
                    self::$_identityField = $r['Field'];
                    break;
                }
            }
        }

        return self::$_identityField;
    }

    final static public function getFields($table = null){
        if($table){
            return self::getDB()->getFields($table);
        }
        if(!self::$_fields) {
            $table = self::getSource();
            //read fields, there is no cached
            self::$_fields = self::getDB()->getFields($table);
        }

        return self::$_fields;
    }

    public function assign($data){
        if(is_array($data)) {
            foreach ($data as $fieldName => $datum) {
                $this->{$fieldName} = $datum;
            }
        }

        return $this;
    }

    public function toArray(){
        $fields = self::getFields();

        $fieldsData = [];

        foreach ($fields as $field => $type){
            if(isset($this->{$field})){
                $fieldsData[$field] = $this->{$field};
            }
        }

        return $fieldsData;
    }
}