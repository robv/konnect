<?php
    class Db_Object
    {
        public $id;
        public $table_name;
        public $id_column_name;

        protected $columns = array();
        protected $class_name;

        protected function __construct($table_name, $id_column_name, $columns, $args = NULL)
        {
            $this->class_name = get_class($this);
            $this->table_name = $table_name;
            $this->id_column_name = $id_column_name;

            foreach ($columns as $column)
			{
                $this->columns[$column] = NULL;
			}
			
			// Passing args to select allows us to combine a select with the initialization of an object
            if (!is_null($args))
				$this->select($args);
		}

        public function __get($key)
        {
            if (array_key_exists($key, $this->columns))
                return $this->columns[$key];

            if ((substr($key, 0, 2) == '__') && array_key_exists(substr($key, 2), $this->columns))
                return htmlspecialchars($this->columns[substr($key, 2)]);

            //$trace = debug_backtrace();
            //trigger_error("Undefined property via Db_Object::__get(): $key in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_NOTICE);
            return NULL;
        }

        public function __set($key, $value)
        {
            if (array_key_exists($key, $this->columns))
			{
                $this->columns[$key] = $value;
			}
            return $value; // Seriously.
        }

		// Returns an array containing columns and values in an array as opposed to object form
        public function get_fields()
        {
            return array_keys($this->columns);
        }
		
		
		// $args should be an array formatted like $args[field_name] = field_value, matching functionality of Database::query
        public function select($args)
        {
			$db = Database::get_instance();
			$values = array();
			
			// TODO: Error check for matching array count
			foreach($args as $field => $value)
			{
				$where[] = '(`' . $db->escape($field) . '` = :' . $db->escape($field) . ')';
				$values[$db->escape($field)] = $value;
			}
			
			$where = ' WHERE ' . implode(' AND ', $where);
			
			$db->query('SELECT * FROM `' . $this->table_name . '`' . $where . ' LIMIT 1', $values);
			
			// Finally check if there were any returned results
			if ($db->has_rows())
            {
                $row = $db->get_row();
                $this->load($row);
                return true;
            }

            return false;
        }

        public function ok()
        {
            return !is_null($this->id);
        }

        public function save()
        {
            if (is_null($this->id))
			{
                $this->insert();
			}
            else
			{
                $this->update();
			}
            return $this->id;
        }

        public function insert($cmd = 'INSERT INTO')
        {
            $db = Database::get_instance();

            if (count($this->columns) == 0)
				return false;

            $data = array();
            
			foreach ($this->columns as $k => $v)
            {
    			if (!is_null($v))
				{
					$data[$k] = $db->quote($v);

				}
			}
			
            $columns = '`' . implode('`, `', array_keys($data)) . '`';
            $values = implode(',', $data);

            $db->query($cmd . ' `' . $this->table_name . '` (' . $columns . ') VALUES (' . $values . ')');
            $this->id = $db->insert_id();
            return $this->id;
        }

        public function replace()
        {
            return $this->delete() && $this->insert();
        }

        public function update()
        {
            if (is_null($this->id))
				return false;

            $db = Database::get_instance();

            if (count($this->columns) == 0)
				return false;

            $sql = 'UPDATE ' . $this->table_name . ' SET ';

            foreach ($this->columns as $k => $v)
            {
    			$sql .= "`$k`=" . $db->quote($v) . ',';
			}
			
			$sql[strlen($sql) - 1] = ' ';

            $sql .= 'WHERE `' . $this->id_column_name . '` = ' . $db->quote($this->id);
            $db->query($sql);

            return $db->affected_rows();
        }

        public function delete()
        {
            if (is_null($this->id))
				return false;
            
			$db = Database::get_instance();
            $db->query('DELETE FROM `' . $this->table_name . '` WHERE `' . $this->id_column_name . '` = :id LIMIT 1', array('id' => $this->id));
            return $db->affected_rows();
        }

        public function load($row)
        {
            foreach ($row as $k => $v)
            {
                if ($k == $this->id_column_name)
				{
                    $this->id = $v;
                }
				elseif (array_key_exists($k, $this->columns))
                {
					$this->columns[$k] = $v;
            	}
			}
        }

        // Grabs a large block of instantiated objects from the database using only one query.
        public function select_many($sql = NULL, $extra_columns = array())
        {
            $db = Database::get_instance();

            $tmp_obj = new $this->class_name;

            // Also, it needs to be a subclass of Db_Object...
            if (!is_subclass_of($tmp_obj, 'Db_Object'))
                return false;

            if (is_null($sql) || empty($sql))
                $sql = "SELECT * FROM `{$tmp_obj->table_name}`";

			// So you want to do select * but don't want to have to type it, just add %select%
			$sql = str_replace('%select%', "SELECT * FROM `{$tmp_obj->table_name}`", $sql);

            $objs = array();
            $rows = $db->get_rows($sql);
            foreach ($rows as $row)
            {
                $o = new $this->class_name;
                $o->load($row);
                $objs[$o->id] = $o;

                foreach ($extra_columns as $c)
                {
                    $o->add_column($c);
                    $o->$c = isset($row[$c]) ? $row[$c] : NULL;
                }
            }
            return $objs;
        }

        public function add_column($key, $val = NULL)
        {
            if (!in_array($key, array_keys($this->columns)))
			{
                $this->columns[$key] = $val;
			}
        }

		/*
		Example Usage: 
		// values are auto escaped and quoted, it also supports operators
		$where = array('col1' => 'value1', 'col2 >=' => 'value2')
		$orderby = array('col1' => 'DESC', 'col2' => 'ASC')
		$items_per_page and $offset are used with limit like so - LIMIT $offset, $items_per_page or if you only pass $items per page it will do LIMIT $items_per_page
		*/

		public function get($where = array(), $orderby = array(), $items_per_page = NULL, $offset = NULL, $fetch_objects = FALSE)
		{
		 	$db = Database::get_instance();

			$fields = isset($this->fields) && count($this->fields) ? implode(', ', $this->fields) : '*';

			if (is_numeric($where))
			{
				$result = $db->query("SELECT $fields FROM $this->table_name WHERE $this->id_column_name = $where ");
				return $result->result ? ($fetch_objects ? mysql_fetch_object($result->result) : mysql_fetch_assoc($result->result)) : NULL;
			}

			$sql_where = array();
			foreach ($where as $field => $value)
			{
				$operator = '=';
				// If there is a space we assume there is an operator override
				if (strpos($k, ' '))
				{
					list($field, $operator) = explode(' ', $k);
				}
				
				$value = $db->quote($value);
				
				$sql_where[] = "$field $operator $value";
			}

			$sql_where = count($sql_where) ? ('WHERE ' . implode(' AND ', $sql_where)) : '';

			$sql_orderby = array();
			foreach ($orderby as $field => $value)
			{
				$sql_orderby[] = "$field $value";
			}

			$sql_orderby = count($sql_orderby) ? ('ORDER BY ' . implode(', ', $sql_orderby)) : '';

			$sql_limit = '';
			if (is_numeric($items_per_page))
			{
				$sql_limit = 'LIMIT ';
				if (is_numeric($offset))
					$sql_limit .= "$offset, ";

				$sql_limit .= "$items_per_page";
			}

			$mysql_fetch_method = $fetch_objects ? 'mysql_fetch_object' : 'mysql_fetch_assoc';
			$rows = array();

			$result = $db->query("SELECT $fields FROM $this->table_name $sql_where $sql_orderby $sql_limit");

			if ($result->result)
			{
				while ($row = $mysql_fetch_method($result->result))
					$rows[] = $row;				
			}

			return $rows;
		}
    }