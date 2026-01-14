<?php
namespace CloudDB\DB;

use PDO;
use Exception;

@session_start();
date_default_timezone_set('America/El_Salvador');
/**
 * Wrapper for PDO
 */
class Database {
    /**
     * hold database connection
     */
    protected $db;

    /**
     * Array of connection arguments
     * 
     * @param array $args
     */
    public function __construct($args) {
        if (!isset($args['database'])) {
            throw new Exception('&args[\'database\'] is required');
        }

        if (!isset($args['username'])) {
            throw new Exception('&args[\'username\']  is required');
        }

        $type     = isset($args['type']) ? $args['type'] : 'mysql';
        $host     = isset($args['host']) ? $args['host'] : 'localhost';
        $charset  = isset($args['charset']) ? $args['charset'] : 'utf8mb4';
        $port     = isset($args['port']) ? 'port=' . $args['port'] . ';' : '';
        $password = isset($args['password']) ? $args['password'] : '';
        $database = $args['database'];
        $username = $args['username'];

        $this->db = new PDO("$type:host=$host;$port" . "dbname=$database;charset=$charset", $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * get PDO instance
     * 
     * @return $db PDO instance
     */
    public function getPdo() {
        return $this->db;
    }

    /**
     * Run raw sql query 
     * 
     * @param  string $sql       sql query
     * @return void
     */
    public function raw($sql) {
        $this->db->query($sql);
    }

    /**
     * Run sql query
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @return object            returns a PDO object
     */
    public function run($sql, $args = []) {
        if (empty($args)) {
            return $this->db->query($sql);
        }

        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute($args);
        } catch(Exception $e) {
            /*
                C-420 = Sin definir / ErrorNoIdentificado (ENI - UFO referencia)
                C-001 = Syntax Error SQL
                C-002 = No se encontró el nombre de la columna declarado
                C-003 = El array que se está enviando como insert,update,where tiene error de sintaxis o no pegan las columnas con la tabla o el nombre de la columna se repite en update/where
                C-004 = Columna ambigua: Varios joins pero no se ha especificado en el where una columna con el alias de la tabla
                C-005 = Se están enviando parámetros vacios: tabla, columna, where, id, ... (insert, update, delete)
                c-006 = La columna de la tabla de bitácoras no existe (solo hay 4 tipos en writeBitacora)
            */
            $debug = 1;
            if($debug == 0) {
                if($e->getCode() == "42000") {
                    die("Algo salió mal - Error: C-001");
                } else if($e->getCode() == "42S22") {
                    die("Algo salió mal - Error: C-002");
                } else if($e->getCode() == "HY093") {
                    die("Algo salió mal - Error: C-003");
                } else if($e->getCode() == "23000") {
                    die("Algo salió mal - Error: C-004");
                } else {
                    die("Algo salió mal - Error: C-420");
                    // die($e->getMessage()); // Ver mensaje (mantener comentareado)
                }
            } else {
                die($e->getMessage());
            }
        }
        return $stmt;
    }

    /**
     * Get arrrays of records
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns multiple records
     */
    public function rows($sql, $args = [], $fetchMode = PDO::FETCH_OBJ) {
        return $this->run($sql, $args)->fetchAll($fetchMode);
    }

    /**
     * Get arrray of records
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns single record
     */
    public function row($sql, $args = [], $fetchMode = PDO::FETCH_OBJ) {
        return $this->run($sql, $args, $fetchMode)->fetch($fetchMode);
    }

    /**
     * Get record by id
     * 
     * @param  string $table     name of table
     * @param  integer $id       id of record
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns single record
     */
    public function getById($table, $id, $fetchMode = PDO::FETCH_OBJ) {
        return $this->run("SELECT * FROM $table WHERE id = ?", [$id])->fetch($fetchMode);
    }

    /**
     * Get number of records
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @param  object $fetchMode set return mode ie object or array
     * @return integer           returns number of records
     */
    public function count($sql, $args = []) {
        return $this->run($sql, $args)->rowCount();
    }

    /**
     * Get primary key of last inserted record
     */
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * insert record
     * 
     * @param  string $table table name
     * @param  array $data  array of columns and values
     */
    public function insert($table, $data) {
        if($table == "" || is_null($table) || $data == "" || is_null($data)) {
            die('Algo salió mal - Error: C-005');
        } else {
            //add columns into comma seperated string
            $columns = implode(',', array_keys($data));

            //get values
            $values = array_values($data);

            $placeholders = array_map(function ($val) {
                return '?';
            }, array_keys($data));

            //convert array into comma seperated string
            $placeholders = implode(',', array_values($placeholders));

            if($_SESSION["writeBitacora"] == "yes") {
                $fh = date("Y-m-d H:i:s");
                $user = $_SESSION["usuario"];
                $obsCrud = "[(" . $fh . ") INSERTÓ: C = (" . $columns . ") V = (" . implode(",", $data) . ")],";

                $this->run("INSERT INTO $table ($columns, userAdd, fhAdd, obsCrud) VALUES ($placeholders, '$user', '$fh', '$obsCrud')", $values);
            } else {
                $this->run("INSERT INTO $table ($columns) VALUES ($placeholders)", $values);
                $_SESSION["writeBitacora"] = "yes"; // reset session
            }

            return $this->lastInsertId();   
        }
    }

    /**
     * update record
     * 
     * @param  string $table table name
     * @param  array $data  array of columns and values
     * @param  array $where array of columns and values
     */
    public function update($table, $data, $where) {
        if($table == "" || is_null($table) || $data == "" || is_null($data) || $where == "" || is_null($where)) {
            die('Algo salió mal - Error: C-005');
        } else {
            //merge data and where together
            $collection = array_merge($data, $where);

            //collect the values from collection
            $values = array_values($collection);

            //setup fields
            $fieldDetails = null;
            $columnsName = null;
            foreach ($data as $key => $value) {
                $columnsName .= $key . ", ";
                $fieldDetails .= "$key = ?,";
            }
            $columnsName .= "id";
            $fieldDetails = rtrim($fieldDetails, ',');

            //setup where 
            $whereDetails = null;
            $i = 0;
            foreach ($where as $key => $value) {
                // el -duplicado es porque el update lleva la misma columna del where y al hacer
                // array_values se sustituye y solo queda 1 parametro
                $fixKey = str_replace('-duplicado', '', $key);
                $whereDetails .= $i == 0 ? "$fixKey = ?" : " AND $fixKey = ?";
                $i++;
            }

            if($_SESSION["writeBitacora"] == "yes") {
                $fh = date("Y-m-d H:i:s");
                $user = $_SESSION["usuario"];
                $obsCrud = "[(" . $fh . ") ACTUALIZÓ: C = (" . $columnsName . ") V = (" . implode(",", $values) . ")],";

                $stmt = $this->run("UPDATE $table SET $fieldDetails, userEdit='$user', fhEdit='$fh', obsCrud=CONCAT(obsCrud, '$obsCrud') WHERE $whereDetails", $values);
            } else {
                $stmt = $this->run("UPDATE $table SET $fieldDetails WHERE $whereDetails", $values);
                $_SESSION["writeBitacora"] = "yes"; // reset session
            }

            return $stmt->rowCount();
        }
    }

    /**
     * Delete records
     * 
     * @param  string $table table name
     * @param  array $where array of columns and values
     * @param  integer $limit limit number of records
     */
    public function delete($table, $where) {
        if($table == "" || is_null($table) || $where == "" || is_null($where)) {
            die('Algo salió mal - Error: C-005');
        } else {
            //collect the values from collection
            $values = array_values($where);

            //setup where 
            $whereDetails = null;
            $i = 0;
            foreach ($where as $key => $value) {
                $whereDetails .= $i == 0 ? "$key = ?" : " AND $key = ?";
                $i++;
            }

            $fh = date("Y-m-d H:i:s");
            $user = $_SESSION["usuario"];
            $obsCrud = "[(" . $fh . ") ELIMINÓ EL REGISTRO],";

            $stmt = $this->run("UPDATE $table SET flgDelete='1', userDelete='$user', fhDelete='$fh', obsCrud=CONCAT(obsCrud, '$obsCrud') WHERE $whereDetails", $values);

            return $stmt->rowCount();
        }
    }

    /**
     * Delete record by id
     * 
     * @param  string $table table name
     * @param  integer $id id of record
     */
    public function deleteById($table, $column, $id) {
        if($table == "" || is_null($table) || $column == "" || is_null($column) || $id == "" || is_null($id) || $id == 0) {
            die('Algo salió mal - Error: C-005');
        } else {
            $fh = date("Y-m-d H:i:s");
            $user = $_SESSION["usuario"];
            $obsCrud = "[(" . $fh . ") ELIMINÓ EL REGISTRO],";
            $stmt = $this->run("UPDATE $table SET flgDelete='1', userDelete='$user', fhDelete='$fh', obsCrud=CONCAT(obsCrud, '$obsCrud') WHERE $column = ?", [$id]);

            return $stmt->rowCount();
        }
    }

    /**
     * Delete record by ids
     * 
     * @param  string $table table name
     * @param  string $column name of column
     * @param  string $ids ids of records
     */
    public function deleteByIds(string $table, string $column, string $ids) {
        if($table == "" || is_null($table) || $column == "" || is_null($column) || $ids == "" || is_null($ids) || $ids == 0) {
            die('Algo salió mal - Error: C-005');
        } else {
            $fh = date("Y-m-d H:i:s");
            $user = $_SESSION["usuario"];
            $obsCrud = "[(" . $fh . ") ELIMINÓ EL REGISTRO],";
            $stmt = $this->run("UPDATE $table SET flgDelete='1', userDelete='$user', fhDelete='$fh', obsCrud=CONCAT(obsCrud, '$obsCrud') WHERE $column IN ($ids)");

            return $stmt->rowCount();
        }
    }

    /**
     * Write bitacora - usuarios
     * 
     * @param  string $column: movInsert, movUpdate, movDelete column
     * @param  string $cadena: mov. realizado
     */
    public function writeBitacora(string $column, string $cadena) {
        if($column == "movInterfaces" || $column == "movInsert" || $column == "movUpdate" || $column == "movDelete") {
            $stmt = $this->run("UPDATE bit_login_usuarios SET $column=CONCAT($column, '$cadena') WHERE loginUsuarioId = ?", [$_SESSION["loginUsuarioId"]]);
            return $stmt->rowCount();
        } else {
            die('Algo salió mal - Error: C-006');
        }
    }

    public function finalizar() {
        $this->db = null; // Establecer la instancia de PDO a null cierra la conexión
    }
}
?>