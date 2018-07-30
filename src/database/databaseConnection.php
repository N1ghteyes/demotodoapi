<?php

namespace TodoApi\database;

/**
 * Class databaseConnection
 * PHP Class to manage all database connections and queries. Designed to be secure and efficient
 * @Author Toby New (t@sc.vg)
 * @todo loads of stuff. could be a lot better, but will do for now.
 * @todo documentation
 * @todo fix insert blank arrays bug.
 * @todo add proper error checking.
 */
class databaseConnection
{
    public $statementError;
    private $database;
    private $user;
    private $host;
    private $pass;
    /** @var PDO */
    private $pdo;
    private $schema = 'mysql';
    private $options;

    private $table; //The current primary table
    private $sql = '';
    private $queryFields = [];
    private $statement;

    /**
     * databaseConnection constructor.
     * @param $database
     * @param $user
     * @param $password
     * @param string $host
     * @param array $options
     */
    public function __construct($database, $user, $password, $host = 'localhost', $options = array())
    {
        $this->setDatabase($database);
        $this->setUser($user);
        $this->setPass($password);
        $this->setHost($host);
        $this->setOptions($options);

        return $this;
    }

    /**
     * Function to set the name of the database we're connecting to
     * @param $name
     * @return $this
     */
    public function setDatabase($name)
    {
        $this->database = $name;
        return $this;
    }

    /**
     * Function to set the username to connect with
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function __get($name)
    {
        $this->table = $name;
        return $this;
    }

    /**
     * Initiate database select query
     * @param string $fields
     */
    public function select($fields = '*'){
        $this->queryFields = []; //empty the fields array.
        $fields = is_array($fields) ? implode(', ', $fields) : $fields;
        $this->sql = "SELECT ".$fields." FROM ".$this->table;
        return $this;
    }

    public function join($table, $field1, $field2, $condition = '='){
        $this->sql .= " JOIN ".$table." ON ".$field1." ".$condition." ".$field2;
        return $this;
    }

    public function update($fields, $values){
        $this->queryFields = []; //empty the fields array.
        $this->sql = "UPDATE ".$this->table;
        $this->setUpdateValues($fields, $values);
        return $this;
    }

    public function insert($fields, $values){
        $this->queryFields = []; //empty the fields array.
        $this->sql = "INSERT INTO ".$this->table;
        $this->setInsertValues($fields, $values);
        return $this;
    }

    public function delete(){
        $this->queryFields = []; //empty the fields array.
        $this->sql = "DELETE FROM ".$this->table;
        return $this;
    }

    private function setUpdateValues($fields, $values){
        $this->sql .= " SET ";
        if(is_array($fields)){
            foreach($fields as $key => $field){
                $this->sql .= $field.' = '.$this->setQueryField($field, $values[$key]). ', ';
            }
            $this->sql = rtrim($this->sql, ', ');
        } else {
            $this->sql .= $fields.'='.$this->setQueryField($fields, $values);
        }
    }

    private function setInsertValues($fields, $values){
        $this->sql .= "(";
        $this->sql .= is_array($fields) ? implode(',', $fields) : $fields;
        $this->sql .= ")";
        $fieldIds = [];
        foreach($fields as $key => $field){
            $fieldIds[] = $this->setQueryField($field, $values[$key]);
        }
        $this->sql .= " VALUES (".implode(',', $fieldIds).")";
    }

    /**
     * Function to add a WHERE clause to the query
     * @param $field
     * @param $value
     * @param string $condition
     * @return $this
     */
    public function where($field, $value, $condition = '=', $type = 'AND'){
        $fieldInc = $this->setQueryField($field, $value);
        $this->sql .= strpos($this->sql, " WHERE") === FALSE ? " WHERE " : ' '.strtoupper($type).' ';
        if(strtoupper($condition) == 'IN'){
            $this->sql .= $field .' '. $condition. ' ('. $fieldInc . ')';
        } else {
            $this->sql .= $field . ' ' . $condition . ' ' . $fieldInc;
        }
        return $this;
    }

    public function whereNestedOr($field1, $value1, $field2, $value2){
        $arg = strpos($this->sql, " WHERE") === FALSE ? " WHERE " : " AND ";
        $this->sql .= $arg."(".$field1."=".$this->setQueryField($field1, $value1)
            ." OR ".$field2."=".$this->setQueryField($field2, $value2).")";
        return $this;
    }

    public function addExtra($value){
        $this->sql .=' '.$value;
        return $this;
    }

    /**
     * Add fields to the active query, account for multiple fields with the same name.
     * @param $field
     * @param $value
     * @return string
     */
    private function setQueryField($field, $value){
        if(is_array($value)){
            $fieldInc = [];
            foreach($value as $val){
                $fieldInc[] = $fieldRaw = $this->addQueryField($field);
                $this->queryFields[$fieldRaw] = $val;
            }
            $fieldInc = implode(',', $fieldInc);
        } else {
            $fieldInc = $this->addQueryField($field);
            $this->queryFields[$fieldInc] = $value;
        }
        return $fieldInc;
    }

    private function addQueryField($field){
        $num = 1;
        $field = str_replace(['.', ' ', ')', '('], ['', '_', '', ''], $field);
        if(isset($this->queryFields[':'.$field])){
            while(TRUE){
                if(!isset($this->queryFields[':'.$field.'_'.$num])){
                    return ':'.$field.'_'.$num;
                    break;
                }
                $num++;
            }
        }
        return ':'.$field;
    }

    /**
     * Function to add Order By to a query
     * @param $field
     * @param string $sort
     * @return $this
     */
    public function orderBy($field, $sort = 'ASC'){
        $this->sql .= strpos($this->sql, " ORDER BY") === FALSE ? " ORDER BY " : ' ,';
        $this->sql .= ' '.$field.' '.$sort;
        return $this;
    }

    public function groupBy($field){
        $this->sql .= ' GROUP BY '.$field;
        return $this;
    }

    /**
     * Function to add Limit to a query
     * @param $limit
     * @param $offset
     * @return $this
     */
    public function limit($limit, $offset = 0){
        $this->sql .= ' LIMIT '.$offset.','.$limit;
        return $this;
    }

    /**
     * Function to execute the query;
     * @param array $fields
     * @param bool $empty
     * @return $this
     */
    public function execute($fields = [], $empty = FALSE){
        if(!empty($fields)){
            $this->setFieldValues($fields, $empty);
        }
        //Allow us to connect as late as possible.
        if(empty($this->pdo)){
            $this->connection();
        }
        $prepare = $this->pdo->prepare($this->sql);
        if(!$prepare){
            $error = $this->pdo->errorInfo();
        }
        $this->statement = $prepare;
        $this->statement->execute($this->queryFields);
        $this->statementError = $this->statement->errorInfo();
        return $this;
    }

    /**
     * Function to Set or update field values for the stored query
     * @param $fields
     * @param bool $empty
     * @return $this
     */
    public function setFieldValues($fields, $empty = FALSE){
        if($empty){
            $this->queryFields = [];
        }
        foreach($fields as $field => $value){
            $this->setQueryField($field, $value);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function fetchAll(){
        return $this->statement->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * @return mixed
     */
    public function fetchAssoc(){
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Initiate the connection to the database.
     * @todo move setAttribute to configurable function call on class instantiation.
     * @return $this
     */
    private function connection()
    {
        $this->pdo = new \PDO($this->schema . ':host=' . $this->host . ';dbname=' . $this->database, $this->user, $this->pass, $this->options);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $this;
    }

    /**
     * Kill the DB connection only on destruct.
     * Magic __destruct function
     */
    public function __destruct(){
        $this->pdo = null;
    }
}