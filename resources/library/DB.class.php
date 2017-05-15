<?php

/**
 * Class DB
 */
class DB extends Sanitizer {

    private static $_instance = null;
    
    private $_pdo; // Instance for re-use
    private $_query;
    private $_error = false;
    private $_results; // Output;
    private $_count = 0;
    private $_lastquery;

    /**
     * Maakt een instance van de verbinding en slaat deze op.
     */
    public function __construct() {
        try {
            $this->_pdo = new PDO(Config::get('mysql/db_driver').':host='.Config::get('mysql/db_host').';dbname='.Config::get('mysql/dbname'), Config::get('mysql/db_user'), Config::get('mysql/db_passw'));
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Returns de instance.
     *
     * @return type
     *
     */
    public static function getInstance() {
        if(!isset(Self::$_instance)) {
            Self::$_instance = new DB();
        }
        return Self::$_instance;
    }

    /**
     * Prepared en stuurt de payload en returned, resultaten op true
     * en null op false;
     *
     * @param type $sql
     * @param array|type $params
     * @return object
     */
    public function query($sql, $params = array()) {

        $this->_error = false;
        $this->_lastquery = $sql;
        
        
        
        if($this->_query = $this->_pdo->prepare($sql)) {
            $x = 1;
            if(count($params)) {
                foreach($params as $param) {
                    $this->_query->bindValue($x, $param, PDO::PARAM_STR);
                    $x++;
                }
            }

            if($this->_query->execute()) {
                $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                $this->_count = $this->_query->rowCount();
            } else {
                $this->_error = true;
            }

        }

        return $this;
    }

    /**
     * Performed een actie op de database.
     *
     * @param type $action
     * @param type $table
     * @param type $where
     * @return boolean|\DB
     */
    public function action($action,$table,$where = array()) {
        if(count($where) === 3) {

            $ops    = array('=','<','>','<=','>=');

            $field      = $where[0];
            $operator   = $where[1];
            $value      = $where[2];

            if(in_array($operator, $ops)) {

                $sql    = "{$action} FROM {$table} WHERE {$field} {$operator} ?";

                if(!$this->query($sql, array($value))->error()) {
                    return $this;
                }
            }
        }
        return false;

    }

    /**
     * Voegt data toe aan de dbase.
     *
     * @param type $table
     * @param type $fields
     * @return boolean
     */
    public function insert($table,$fields = array()) {
        if(count($fields)) {

            $key          = array_keys($fields);
            $values     = '';
            $x              = 1;

            foreach ($fields as $field) {
                $values .= '?';
                if($x < count($fields)) {
                    $values .= ', ';
                }
                $x++;
            }

            $sql     = "INSERT INTO `".$table."` (`" . implode('`, `', $key) . "`) VALUES({$values})";

            if (!$this->query($sql, $fields)->error()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Update gegevens in de dbase.
     *
     * @param type $table
     * @param type $id
     * @param type $fields
     * @return boolean
     */
    public function update($table, $id, $fields) {
        $set    = '';
        $x      = 1;

        foreach ($fields as $name => $value) {
            $set .= "{$name} = ?";

            if($x < count($fields)) {
                $set .= ', ';
            }
            $x++;

        }

        $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";

        if(!$this->query($sql, $fields)->error()) {
            return true;
        }

        return false;
    }

    /**
     * Dit is een get statement, gewoon een simpele
     * retrieval.
     *
     * @param type $table
     * @param type $where
     * @return type
     *
     * @todo "SELECT *" aanpassen, dat die waarde ook dynamisch word.
     */
    public function get($table,$where) {
        return $this->action('SELECT *',$table,$where);
    }

    /**
     * Delete statement.
     *
     * @param type $table
     * @param type $where
     * @return type
     */
    public function delete($table,$where) {
        return $this->action('DELETE',$table,$where);
    }

    /**
     * Returned resultaten.
     *
     * @return type
     */
    public function results() {
        return $this->_results;
    }

    /**
     * Returned de eerste "row" van de resultaten.
     *
     * @return type
     */
    public function first() {
        return $this->results()[0];
    }

    /**
     * Returned laatst opgetreden error.
     *
     * @return type
     */
    public function error() {
        return $this->_error;
    }

    /**
     * Stuurt de count van de resultaten terug.
     *
     * @return type
     */
    public function count() {
        return $this->_count;
    }

    /**
     * Returned de laatst uitgevoerde query op de dbase.
     *
     * @return type
     */
    public function lastQuery() {
        return $this->_instance;
    }


/**
 * Utility function to throw an exception if an error occurs
 * while running a mysql command.
 */
    private function throwExceptionOnError($link = null) {
      	if($link == null) {
      		$link = $this->_pdo;
      	}
      	if(mysqli_error($link)) {
      		$msg = mysqli_errno($link) . ": " . mysqli_error($link);
      		throw new Exception('MySQL Error - '. $msg);
      	}
    }

}
