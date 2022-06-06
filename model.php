<?php
$config = require './config.php';

class RecordNotFoundErr extends Exception{
    public function __construct($exmsg, $val = 0, Exception $old = null) {
        $exmsg = 'Record Not Found';
        return parent::__construct($exmsg, $val, $old);
    }
}

class Conn{
    public function generate($db){
        try{
            return new PDO("{$GLOBALS["config"]->databases->{$db}->dbtype}:host={$GLOBALS["config"]->databases->{$db}->host};dbname={$GLOBALS["config"]->databases->{$db}->dbname}", 
                                        $GLOBALS["config"]->databases->{$db}->username,
                                        base64_decode($GLOBALS["config"]->databases->{$db}->password));
        }
        catch(PDOException $e){
            if ($GLOBALS["config"]->debug){
                die($e.PHP_EOL);
            }
            die("Db connection fail");
        }
    }

}


class DBTable{
    protected $__meta_table;
    protected $__meta_columns = array();
    protected $__meta_pk;

    public function __construct(object $conn) {
        $this->__conn = $conn;
        if (!($this->__meta_pk)){
            $stmt = $this->__conn->prepare("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            if ($result){
                $this->__meta_pk = $result->Column_name;
            }
        }
    }

    private function where_str(object $query){
        $where = [];
        foreach ($query as $key=>$value){
            array_push($where,"$key='$value'");
        }
        return " ".join(" and ",$where);
    }

    public function getTableColums(){
        return array_values($this->__meta_columns);
    }

    public function getModelColumns(){
        return array_keys($this->__meta_columns);
    }

    public function raw_query(string $query){
        $this->__get_query = $query;
        $this->__conn->prepare("SET NAMES 'utf8';CHARSET 'utf8';")->execute();
        $stmt = $this->__conn->prepare($this->__get_query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function filter(string $where = "") : array{
        $this->__get_query = $where ? $where : "1=1";
        $this->__conn->prepare("SET NAMES 'utf8';CHARSET 'utf8';")->execute();
        $query = "SELECT * from $this->__meta_table where $this->__get_query";
        $stmt = $this->__conn->prepare($query);
        $stmt->execute();
        $result =  $stmt->fetchAll(PDO::FETCH_OBJ);
        $records = [];
        foreach ($result as $row){
            $record = new DbTable($this->__conn);
            $record->__dbinstance = new DbTable($this->__conn);
            foreach ($row as $key=>$value){
                $record->{$key} = $value;
                $record->__dbinstance->{$key} = $value;
            }
            array_push($records,$record);
        }
        return $records;
    }

    public function filter_json(string $where = "") : array{
        $this->__get_query = $where ? $where : "1=1";
        $this->__conn->prepare("SET NAMES 'utf8';CHARSET 'utf8';")->execute();
        $query = "SELECT * from $this->__meta_table where $this->__get_query";
        $stmt = $this->__conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function get(string $where) : object{
        $this->__get_query = $where;
        $this->__conn->prepare("SET NAMES 'utf8';CHARSET 'utf8';")->execute();
        $query = "SELECT * from $this->__meta_table where $this->__get_query";
        $stmt = $this->__conn->prepare($query);
        $stmt->execute();
        $result =  $stmt->fetch(PDO::FETCH_OBJ);
        if ($result){
            $this->__dbinstance = new DBTable($this->__conn);

            foreach ($result as $key=>$value){
                $this->{$key} = $value;
                $this->__dbinstance->{$key} = $value;
            }
        }
        else{
            throw new RecordNotFoundErr("Table record Not Found");
        }
        return $this;
    }

    public function save() : object{
        $tableColumns = $this->getTableColums();
        $modelColumns = $this->getModelColumns();
        $data = $this->json();
        $values = [];
        foreach ($modelColumns as $key){
            array_push($values,$data->{$key});
        }
        $values = join("','",$values);
        $this->__conn->prepare("SET NAMES 'utf8';CHARSET 'utf8';")->execute();
        if ($this->__dbinstance){
            $set = [];
            foreach ($tableColumns as $key){
                $value = $this->{$key};
                array_push($set,"$key='$value'");
            }
            $set = join(",",$set);
            $query = "UPDATE $this->__meta_table SET $set where $this->__get_query";
            $this->__conn->prepare($query)->execute();
            return $this->get($this->__get_query);
        }
        else{
            $columns = join(",",$tableColumns);
            $query = "INSERT INTO $this->__meta_table ($columns) VALUES ('$values')";
            $this->__conn->prepare($query)->execute();
            if ($this->__meta_pk){
                $last = $this->__conn->lastInsertId();
                return $this->get("$this->__meta_pk='$last'");
            }
        }
    }

    public function delete() : void{
        $this->__conn->prepare("SET NAMES 'utf8';CHARSET 'utf8';")->execute();
        $query = "DELETE FROM $this->__meta_table where $this->__get_query";
        $this->__conn->prepare($query)->execute();
        unset($this->__dbinstance);
        unset($this->__get_query);
        foreach ($this->getModelColumns() as $key){
            $this->{$key} = null;
        }
    }

    public function json(){
        $data = (object)array();
        foreach ($this->getModelColumns() as $key){
            $data->{$key} = $this->{$key};
        }
        return $data;
    }

}


class User extends DBTable{
    protected $__meta_table = "users";
    protected $__meta_columns = array("id"=>"id","name"=>"name","surname"=>"surname");
    protected $__meta_pk = "id";

    protected $__dbinstance;
    protected $__get_query;
    
    public $id = null;
    public $name = null;
    public $surname = null;
    

}


?>