<?php

class Database
{
    // Singleton object. Leave $me alone.
    private static $me;

    public $db;
    public $host;
    public $name;
    public $username;
    public $password;
    public $die;
    public $queries;
    public $result;

    public $redirect = FALSE;

    // Singleton constructor
    private function __construct($config = NULL, $connect = FALSE)
    {
		
		if(is_null($config))
			$config = Config::$config['core']['db'];

		$this->host       = $config['host'];
		$this->name       = $config['name'];
		$this->username   = $config['username'];
		$this->password   = $config['password'];
		$this->die 		  = $config['die'];

        $this->db = FALSE;
        $this->queries = array();

        if($connect === TRUE)
            $this->connect();
    }

    // Get Singleton object
    public static function get_db($config = NULL, $connect = TRUE)
    {
        if(is_null(self::$me))
            self::$me = new Database($config, $connect);
        return self::$me;
    }

    // Do we have a valid database connection?
    public function is_connected()
    {
        return is_resource($this->db) && get_resource_type($this->db) == 'mysql link';
    }

    public function connect()
    {
        $this->db = mysql_connect($this->host, $this->username, $this->password) or $this->notify();
        if($this->db === FALSE) return FALSE;
        mysql_select_db($this->name, $this->db) or $this->notify();
        return $this->is_connected();
    }

    public function query($sql, $args_to_prepare = NULL, $exception_on_missing_args = TRUE)
    {
        if(!$this->is_connected()) $this->connect();

        // Allow for prepared arguments. Example:
        // query("SELECT * FROM table WHERE id = :id", array('id' => $some_val));
        if(is_array($args_to_prepare))
        {
            foreach($args_to_prepare as $name => $val)
            {
                $val = $this->quote($val);
                $sql = str_replace(":$name", $val, $sql, $count);
                if($exception_on_missing_args && (0 == $count))
                    throw new Exception(":$name was not found in prepared SQL query.");
            }
        }

        $this->queries[] = $sql;
        $this->result = mysql_query($sql, $this->db) or $this->notify();

        return $this; // for the sake of chaining!
    }

    // Returns the number of rows.
    // You can pass in nothing, a string, or a db result
    public function num_rows($arg = NULL)
    {
        $result = $this->resulter($arg);
        return ($result !== FALSE) ? mysql_num_rows($result) : FALSE;
    }

    // Returns TRUE / FALSE if the result has one or more rows
    public function has_rows($arg = NULL)
    {
        $result = $this->resulter($arg);
        return is_resource($result) && (mysql_num_rows($result) > 0);
    }

    // Returns the number of rows affected by the previous operation
    public function affected_rows()
    {
        if(!$this->is_connected()) return FALSE;
        return mysql_affected_rows($this->db);
    }

    // Returns the auto increment ID generated by the previous insert statement
    public function insert_id()
    {
        if(!$this->is_connected()) return FALSE;
        $id = mysql_insert_id($this->db);
        if($id === 0 || $id === FALSE)
            return FALSE;
        else
            return $id;
    }

    // Returns a single value.
    // You can pass in nothing, a string, or a db result
    public function get_value($arg = NULL)
    {
        $result = $this->resulter($arg);
        return $this->has_rows($result) ? mysql_result($result, 0, 0) : FALSE;
    }

    // Returns an array of the first value in each row.
    // You can pass in nothing, a string, or a db result
    public function get_values($arg = NULL)
    {
        $result = $this->resulter($arg);
        if(!$this->has_rows($result)) return array();

        $values = array();
        mysql_data_seek($result, 0);
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
            $values[] = array_pop($row);
        return $values;
    }

    // Returns the first row.
    // You can pass in nothing, a string, or a db result
    public function get_row($arg = NULL)
    {
        $result = $this->resulter($arg);
        return $this->has_rows() ? mysql_fetch_array($result, MYSQL_ASSOC) : FALSE;
    }

    // Returns an array of all the rows.
    // You can pass in nothing, a string, or a db result
    public function get_rows($arg = NULL)
    {
        $result = $this->resulter($arg);
        if(!$this->has_rows($result)) return array();

        $rows = array();
        mysql_data_seek($result, 0);
        while($row = mysql_fetch_array($result, MYSQL_ASSOC))
            $rows[] = $row;
        return $rows;
    }

    // Escapes a value and wraps it in single quotes.
    public function quote($var)
    {
        if(!$this->is_connected()) $this->connect();
        return "'" . $this->escape($var) . "'";
    }

    // Escapes a value.
    public function escape($var)
    {
        if(!$this->is_connected()) $this->connect();
        return mysql_real_escape_string($var, $this->db);
    }

    public function num_queries()
    {
        return count($this->queries);
    }

    public function lastQuery()
    {
        if($this->num_queries() > 0)
            return $this->queries[$this->num_queries() - 1];
        else
            return FALSE;
    }

    private function notify()
    {
        $err_msg = mysql_error($this->db);
        error_log($err_msg);

        if($this->die === TRUE)
        {
            echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Database Error:</strong><br/>$err_msg</p>";
            echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Last Query:</strong><br/>" . $this->lastQuery() . "</p>";
            echo "<pre>";
            debug_print_backtrace();
            echo "</pre>";
            exit;
        }

        if(is_string($this->redirect))
        {
            header("Location: {$this->redirect}");
            exit;
        }
    }

    // Takes nothing, a MySQL result, or a query string and returns
    // the correspsonding MySQL result resource or FALSE if none available.
    private function resulter($arg = NULL)
    {
        if(is_null($arg) && is_resource($this->result))
            return $this->result;
        elseif(is_resource($arg))
            return $arg;
        elseif(is_string($arg))
        {
            $this->query($arg);
            if(is_resource($this->result))
                return $this->result;
            else
                return FALSE;
        }
        else
            return FALSE;
    }
}